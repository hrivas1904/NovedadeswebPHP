<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\ClasificadorOperacion;

class InterbankingController extends Controller
{

    private const COD_BANCO = ['011' => 'NACION', '017' => 'NACION', '285' => 'MACRO'];

    private const CTA_LARGA = [
        '30704530072342' => 'NACION',
        '4760009860'     => 'FRANCES (986)',
        '4760010017'     => 'FRANCES (1001)',
        '312709414221236' => 'MACRO',
    ];

    private const COD_OP = [
        'N24' => 'GTOS, COMIS, IMP.',
        'R86' => 'GTOS, COMIS, IMP.',
        'Q24' => 'IMPUESTOS Y TASAS',
        '771' => 'IMPUESTOS Y TASAS',
        'M23' => 'TARJETA CORPORATIVA',
        '303' => 'OBRAS SOCIALES',
        '772' => 'INGRESOS PARTICULARES',
        '462' => 'INGRESOS PARTICULARES',
        '952' => 'TARJETAS D/C',
        '917' => 'SUELDOS',
        '471' => 'PLANES DE PAGO',
        '130' => 'TRANSF. ENTRE CUENTAS',
    ];

    public function interbankingView()
    {
        $conceptos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))->pluck('nombre');

        return view('administracion.interbanking.interbanking', compact('conceptos'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'contenido' => 'nullable|string',
            'archivo'   => 'nullable|file|mimes:csv,txt',
        ]);

        $contenido = $request->hasFile('archivo')
            ? $request->file('archivo')->get() // es texto plano (CSV), no Excel -- se lee directo
            : (string) $request->input('contenido', '');

        if (trim($contenido) === '') {
            return response()->json(['rows' => [], 'mensaje' => 'No se recibió contenido ni archivo.']);
        }

        $rows = $this->parseIB($contenido);
        $validas = collect($rows)->where('valido', true)->count();

        $mensaje = count($rows) === 0
            ? 'No se detectaron filas de movimiento (líneas que empiecen con "M,").'
            : $validas . ' de ' . count($rows) . ' movimientos listos para importar.';

        return response()->json(['rows' => $rows, 'mensaje' => $mensaje]);
    }

    public function confirmar(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $cuentasValidas = collect(DB::select('SELECT nombre FROM ff_cuentas WHERE activo=1'))->pluck('nombre')->toArray();

        $insertados = 0;
        $omitidos = 0;

        foreach ($request->input('rows') as $r) {
            if (!in_array($r['banco'] ?? '', $cuentasValidas) || (float) ($r['importe'] ?? 0) == 0) {
                $omitidos++;
                continue;
            }

            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                $r['banco'],
                $r['concepto'],
                $r['subconcepto'] ?? null,
                $r['detalle'],
                $r['importe'],
                'EJECUTADO',
                $r['operacion'],
                $r['seccion'],
                'IMPORTACION_INTERBANKING',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json(['insertados' => $insertados, 'omitidos' => $omitidos]);
    }

    private function parseIB(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $cuentasValidas = collect(DB::select('SELECT nombre FROM ff_cuentas WHERE activo=1'))->pluck('nombre')->toArray();
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (!str_starts_with($line, 'M,')) continue;

            $cols = array_map(function ($c) {
                return trim(preg_replace('/^"|"$/', '', trim($c)));
            }, explode(',', $line));

            if (count($cols) < 12) continue;

            $impRaw = preg_replace('/\D/', '', $cols[6] ?? '');
            $imp = $impRaw !== '' ? (int) $impRaw : 0;
            $importe = (($cols[5] ?? '') === 'C') ? $imp / 100 : - ($imp / 100);
            if ($importe == 0) continue;

            $ctaLarga = trim($cols[14] ?? '');
            $fr = $cols[3] ?? '';
            $fecha = strlen($fr) === 8
                ? substr($fr, 0, 4) . '-' . substr($fr, 4, 2) . '-' . substr($fr, 6, 2)
                : $fr;

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) continue;

            $codOp = trim($cols[8] ?? '');
            $banco = self::CTA_LARGA[$ctaLarga] ?? (self::COD_BANCO[$cols[1] ?? ''] ?? ($cols[1] ?? ''));
            $concepto = self::COD_OP[$codOp] ?? 'GTOS, COMIS, IMP.';
            $seccion = $importe >= 0 ? '2 INGRESOS' : '3 EGRESOS';
            $detalle = trim($cols[11] ?? '');

            // Unico ajuste real respecto al original: la operacion se normaliza
            // al ENUM de la BD (el original guardaba el codigo crudo tipo "N24",
            // que ya quedo preservado en subconcepto, asi que no se pierde nada).
            $operacion = ClasificadorOperacion::resolver($banco, $concepto, $seccion, $importe);

            $errores = [];
            if (!in_array($banco, $cuentasValidas)) {
                $errores[] = "Cuenta \"$banco\" no reconocida (código banco/CBU sin mapear)";
            }

            $rows[] = [
                'fecha' => $fecha,
                'banco' => $banco,
                'seccion' => $seccion,
                'concepto' => $concepto,
                'subconcepto' => $codOp,
                'detalle' => $detalle,
                'importe' => $importe,
                'operacion' => $operacion,
                'valido' => empty($errores),
                'errores' => $errores,
            ];
        }

        return $rows;
    }
}
