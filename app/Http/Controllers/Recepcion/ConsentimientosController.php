<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ConsentimientosController extends Controller
{
    public function index()
    {
        return view('recepcion.consentimientos');
    }

    private function parsearListadoInternado(string $texto): array
    {
        $out = [];
        $cur = [];
        $ultimo = null;

        foreach (preg_split('/\r?\n/', $texto) as $raw) {
            $l = trim($raw);

            if (preg_match('/^(.*?)\s*Pac:\s*(.*)$/u', $l, $m)) {
                $cur = ['paciente' => trim($m[1] !== '' ? $m[1] : $m[2]), 'os' => ''];
                continue;
            }

            if (
                !empty($cur['paciente']) && $cur['os'] === '' && empty($cur['atencion'])
                && preg_match('/^(.+?\(\d+\))/u', $l, $m)
            ) {
                $cur['os'] = trim($m[1]);
                continue;
            }

            if (!empty($cur['paciente']) && preg_match('/N[º°o]\s*:\s*(\d{6,8})\b/u', $l, $m)) {
                preg_match_all('/\d{2}\/\d{2}\/\d{4}/', $l, $f);
                preg_match_all('/\b\d{2}:\d{2}\b/', $l, $h);
                $cur['atencion']     = $m[1];
                $cur['fecha']        = $f[0][0] ?? null;
                $cur['hora']         = $h[0][0] ?? null;
                $cur['egreso_fecha'] = $f[0][1] ?? null;
                $cur['egreso_hora']  = $h[0][1] ?? null;
                $cur['egreso_real']  = stripos($l, 'Fec. Egreso') !== false;
                continue;
            }

            if (!empty($cur['atencion']) && preg_match('/^Solicitante:\s*(?:\d+\s*-\s*)?(.*)$/u', $l, $m)) {
                $cur['medico'] = trim($m[1]);
                $cur['tipo_alta'] = null;
                $out[] = $cur;
                $ultimo = array_key_last($out);
                $cur = [];
                continue;
            }
            if ($ultimo !== null && preg_match('/Tipo Alta:\s*(.*)$/u', $l, $m)) {
                $out[$ultimo]['tipo_alta'] = trim($m[1]) ?: null;
                $ultimo = null;
            }
        }
        return $out;
    }

    public function importarAtenciones(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:pdf|max:10240']);

        $texto = (new Parser())->parseFile($request->file('archivo')->getRealPath())->getText();
        $atenciones = $this->parsearListadoInternado($texto);

        $enPdf = preg_match_all('/N[º°o]\s*:\s*\d{6,8}\b/u', $texto);
        if (!$atenciones) {
            return response()->json(['ok' => false, 'mensaje' => 'No se encontraron atenciones. Verificá que sea el listado de internado de Geclisa.'], 422);
        }

        $r = DB::select('CALL SP_CONS_IMPORTAR(?, ?, ?, ?)', [
            json_encode($atenciones, JSON_UNESCAPED_UNICODE),
            $request->file('archivo')->getClientOriginalName(),
            $enPdf,
            auth()->id(),
        ])[0];

        return response()->json([
            'ok'         => true,
            'en_pdf'     => $enPdf,
            'leidas'     => $r->leidas,
            'insertadas' => $r->insertadas,
            'omitidas'   => $r->omitidas,
        ]);
    }

    public function listarAtenciones(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        return response()->json([
            'data' => DB::select('CALL SP_CONS_LISTAR(?, ?)', [$request->desde, $request->hasta]),
        ]);
    }

    public function marcarAtencion(Request $request)
    {
        $v = $request->validate([
            'atencion' => 'required|string|max:10',
            'campo'    => 'required|in:firmado_pac,firmado_med,subido',
            'valor'    => 'required|boolean',
        ]);

        $r = DB::select('CALL SP_CONS_MARCAR(?, ?, ?, ?)', [
            $v['atencion'],
            $v['campo'],
            $v['valor'] ? 1 : 0,
            auth()->id(),
        ])[0];

        return response()->json(['ok' => true, 'data' => $r]);
    }
}
