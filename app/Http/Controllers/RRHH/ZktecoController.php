<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Mithun\PhpZkteco\Libs\ZKTeco;
use Illuminate\Http\Request;
use App\Services\ZktecoAttendanceService;
use App\Services\ZktecoSyncService;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ZktecoController extends Controller
{
    public function index()
    {
        return view('zkteco.index');
    }

    public function marcaciones(Request $request)
    {
        try {

            $request->validate([
                'tipo_entidad' => 'nullable|string',
                'desde' => 'nullable|date_format:Y-m-d',
                'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde'
            ]);

            $reporte = $this->obtenerReporteTarja($request);

            return response()->json([
                'success' => true,
                'cantidad' => $reporte->count(),
                'data' => $reporte
            ]);
        } catch (\Throwable $e) {

            Log::error('Error consultando reporte de tarja', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error consultando marcaciones.',
                'data' => []
            ], 500);
        }
    }

    public function probarConexion(Request $request)
    {
        $dispositivo = DB::table('zkteco_dispositivos')
            ->where('id', $request->dispositivo_id)
            ->where('activo', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo ZKTeco no encontrado o inactivo.'
            ], 404);
        }

        $zk = new ZKTeco(
            host: $dispositivo->ip,
            port: (int) $dispositivo->puerto,
            shouldPing: false,
            timeout: (int) config('zkteco.timeout', 60),
            password: (int) config('zkteco.password', 0),
            protocol: config('zkteco.protocol', 'tcp')
        );

        $conectado = false;

        try {

            $inicio = microtime(true);

            $conectado = $zk->connect();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            if (!$conectado) {

                return response()->json([
                    'success' => false,
                    'dispositivo' => $dispositivo->nombre,
                    'message' => 'El reloj no respondió a la conexión TCP.',
                    'tiempo' => $tiempo
                ], 500);
            }

            return response()->json([
                'success' => true,
                'dispositivo' => $dispositivo->nombre,
                'tipo_entidad' => $dispositivo->tipo_entidad,
                'message' => 'Conexión TCP con ZKTeco realizada correctamente.',
                'tiempo' => $tiempo
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'dispositivo' => $dispositivo->nombre,
                'message' => 'Error al conectar con el reloj ZKTeco.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {

                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function probarLectura(Request $request)
    {
        $request->validate([
            'dispositivo_id' => 'required|integer'
        ]);

        $dispositivo = DB::table('zkteco_dispositivos')
            ->where('id', $request->dispositivo_id)
            ->where('activo', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo ZKTeco no encontrado o inactivo.'
            ], 404);
        }

        $zk = new ZKTeco(
            host: $dispositivo->ip,
            port: (int) $dispositivo->puerto,
            shouldPing: false,
            timeout: (int) config('zkteco.timeout', 60),
            password: (int) config('zkteco.password', 0),
            protocol: config('zkteco.protocol', 'tcp')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'dispositivo' => $dispositivo->nombre,
                    'message' => 'No se pudo conectar con el reloj.'
                ], 500);
            }

            $hora = $zk->getTime();

            return response()->json([
                'success' => true,
                'dispositivo' => $dispositivo->nombre,
                'tipo_entidad' => $dispositivo->tipo_entidad,
                'message' => 'Lectura realizada correctamente.',
                'hora_reloj' => $hora
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'dispositivo' => $dispositivo->nombre,
                'message' => 'Error al leer información del reloj.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {
                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function diagnostico(Request $request)
    {
        $request->validate([
            'dispositivo_id' => 'required|integer'
        ]);

        $dispositivo = DB::table('zkteco_dispositivos')
            ->where('id', $request->dispositivo_id)
            ->where('activo', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo ZKTeco no encontrado o inactivo.'
            ], 404);
        }

        $zk = new ZKTeco(
            host: $dispositivo->ip,
            port: (int) $dispositivo->puerto,
            shouldPing: false,
            timeout: (int) config('zkteco.timeout', 60),
            password: (int) config('zkteco.password', 0),
            protocol: config('zkteco.protocol', 'tcp')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'dispositivo' => $dispositivo->nombre,
                    'message' => 'No se pudo conectar con el reloj.'
                ], 500);
            }

            $memoria = $zk->getMemoryInfo();

            return response()->json([
                'success' => true,
                'dispositivo' => $dispositivo->nombre,
                'tipo_entidad' => $dispositivo->tipo_entidad,
                'hora' => $zk->getTime(),
                'modelo' => $zk->deviceName(),
                'serial' => $zk->serialNumber(),
                'version' => $zk->version(),
                'plataforma' => $zk->platform(),
                'memoria' => $memoria,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'dispositivo' => $dispositivo->nombre,
                'message' => 'Error al consultar diagnóstico.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {
                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function probarUsuarios()
    {
        $zk = new ZKTeco(
            host: config('zkteco.ip'),
            port: config('zkteco.port'),
            shouldPing: false,
            timeout: config('zkteco.timeout'),
            password: config('zkteco.password'),
            protocol: config('zkteco.protocol')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar con el reloj ZKTeco.',
                    'data' => []
                ], 500);
            }

            $inicio = microtime(true);

            // SOLO LECTURA
            $usuarios = $zk->getUsers();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            /*
        |--------------------------------------------------------------------------
        | Normalizar respuesta
        |--------------------------------------------------------------------------
        |
        | No devolvemos password.
        |
        */

            $usuariosLimpios = [];

            foreach ($usuarios as $usuario) {

                $usuariosLimpios[] = [
                    'uid' => $usuario['uid'] ?? null,
                    'user_id' => $usuario['user_id'] ?? null,
                    'name' => $usuario['name'] ?? null,
                    'role' => $usuario['role'] ?? null,
                    'card_no' => $usuario['card_no'] ?? null,
                    'device_ip' => $usuario['device_ip'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'cantidad' => count($usuariosLimpios),
                'tiempo' => $tiempo,
                'data' => $usuariosLimpios
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error consultando usuarios del reloj.',
                'detalle' => $e->getMessage(),
                'data' => []
            ], 500);
        } finally {

            if ($conectado) {

                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function sincronizar(
        ZktecoSyncService $syncService
    ) {
        try {

            $dispositivos = DB::table('zkteco_dispositivos')
                ->where('activo', 1)
                ->get();

            $resultado = [];

            foreach ($dispositivos as $dispositivo) {

                $resultado[] = [
                    'dispositivo' => $dispositivo->nombre,
                    'resultado' => $syncService
                        ->sincronizar($dispositivo)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando relojes ZKTeco.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerReporteTarja(Request $request)
    {
        $maxHoras = 24;
        $minutosDuplicado = 5;

        $query = DB::table('zkteco_marcaciones as zm')
            ->join(
                'zkteco_dispositivos as zd',
                'zd.id',
                '=',
                'zm.dispositivo_id'
            )
            ->leftJoin(
                'medicos as m',
                'm.id',
                '=',
                'zm.user_id'
            )
            ->select([
                'zm.id',
                'zm.uid',
                'zm.user_id',
                'zm.record_time',
                'zm.dispositivo_id',
                'zd.nombre as dispositivo',
                'zd.tipo_entidad',
                'm.nombre as medico'
            ]);

        // FILTRO TIPO ENTIDAD
        if ($request->filled('tipo_entidad')) {
            $query->where(
                'zd.tipo_entidad',
                $request->tipo_entidad
            );
        }

        /*
     * Recuperamos marcaciones adicionales para poder
     * identificar jornadas que cruzan la medianoche.
     *
     * IMPORTANTE:
     * El rango ampliado permite recuperar marcaciones
     * cercanas a los límites del reporte, pero no
     * garantiza resolver marcaciones faltantes.
     */

        if ($request->filled('desde')) {
            $query->where(
                'zm.record_time',
                '>=',
                Carbon::parse($request->desde)
                    ->subHours($maxHoras)
                    ->format('Y-m-d H:i:s')
            );
        }

        if ($request->filled('hasta')) {
            $query->where(
                'zm.record_time',
                '<',
                Carbon::parse($request->hasta)
                    ->addDay()
                    ->addHours($maxHoras)
                    ->format('Y-m-d H:i:s')
            );
        }

        $marcaciones = $query
            ->orderBy('zm.user_id')
            ->orderBy('zm.record_time')
            ->orderBy('zm.id')
            ->get();

        $reporte = collect();

        // Agrupar por médico
        $grupos = $marcaciones->groupBy(
            fn($item) => (string) $item->user_id
        );

        foreach ($grupos as $grupo) {

            $marcacionesValidas = collect();
            $ultimaMarcacion = null;

            // Eliminar duplicados cercanos
            foreach ($grupo as $marcacion) {

                $fechaActual = Carbon::parse(
                    $marcacion->record_time
                );

                if ($ultimaMarcacion !== null) {

                    $diferencia = Carbon::parse(
                        $ultimaMarcacion->record_time
                    )->diffInSeconds($fechaActual);

                    if ($diferencia < $minutosDuplicado * 60) {
                        continue;
                    }
                }

                $marcacionesValidas->push($marcacion);
                $ultimaMarcacion = $marcacion;
            }

            $pendiente = null;

            foreach ($marcacionesValidas as $marcacion) {

                if ($pendiente === null) {
                    $pendiente = $marcacion;
                    continue;
                }

                $entrada = Carbon::parse(
                    $pendiente->record_time
                );

                $salida = Carbon::parse(
                    $marcacion->record_time
                );

                $minutos = (int) $entrada->diffInMinutes(
                    $salida
                );

                $estado = 'COMPLETA';
                $tiempoTrabajado = null;
                $fechaSalida = null;

                if ($minutos <= $maxHoras * 60) {

                    $tiempoTrabajado = sprintf(
                        '%02d:%02d',
                        intdiv($minutos, 60),
                        $minutos % 60
                    );

                    $fechaSalida = $salida->format(
                        'Y-m-d H:i:s'
                    );

                    $pendiente = null;
                } else {

                    $estado = 'REVISAR';

                    /*
                 * No podemos determinar automáticamente
                 * la correspondencia entre estas dos
                 * marcaciones.
                 */
                }

                $reporte->push([
                    'id_medico' => $marcacion->user_id,
                    'medico' => $marcacion->medico
                        ?? 'Sin identificar',

                    'entrada' => $entrada->format(
                        'Y-m-d H:i:s'
                    ),

                    'salida' => $fechaSalida,

                    'minutos_trabajados' =>
                    $estado === 'COMPLETA'
                        ? $minutos
                        : null,

                    'tiempo_trabajado' => $tiempoTrabajado,

                    'estado' => $estado,

                    'dispositivo' => $pendiente->dispositivo
                        ?? $marcacion->dispositivo
                ]);

                if ($estado === 'REVISAR') {
                    $pendiente = $marcacion;
                }
            }

            // Marcación sin pareja
            if ($pendiente !== null) {

                $reporte->push([
                    'id_medico' => $pendiente->user_id,
                    'medico' => $pendiente->medico
                        ?? 'Sin identificar',

                    'entrada' => $pendiente->record_time,
                    'salida' => null,
                    'minutos_trabajados' => null,
                    'tiempo_trabajado' => null,
                    'estado' => 'INCOMPLETA',
                    'dispositivo' => $pendiente->dispositivo
                ]);
            }
        }

        /*
     * Filtramos por fecha de entrada una vez
     * reconstruidas las jornadas.
     */

        if ($request->filled('desde')) {

            $desde = Carbon::parse(
                $request->desde
            )->startOfDay();

            $reporte = $reporte->filter(
                fn($item) =>
                Carbon::parse($item['entrada'])
                    ->greaterThanOrEqualTo($desde)
            );
        }

        if ($request->filled('hasta')) {

            $hasta = Carbon::parse(
                $request->hasta
            )->endOfDay();

            $reporte = $reporte->filter(
                fn($item) =>
                Carbon::parse($item['entrada'])
                    ->lessThanOrEqualTo($hasta)
            );
        }

        return $reporte
            ->sortByDesc('entrada')
            ->values();
    }

    public function exportarTarja(Request $request)
    {
        try {

            $request->validate([
                'tipo_entidad' => 'nullable|string',
                'desde' => 'nullable|date_format:Y-m-d',
                'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde'
            ]);

            // Obtener los mismos datos que utiliza el DataTable
            $datos = $this->obtenerReporteTarja($request);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle('Tarja Médicos');

            // TÍTULO
            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue('A1', 'REPORTE DE TARJA MÉDICA - HP3C');

            $sheet->getStyle('A1:F1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '17365D']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            $sheet->getRowDimension(1)->setRowHeight(35);

            // PERÍODO CONSULTADO
            $sheet->mergeCells('A2:F2');

            $desde = $request->desde
                ? date('d/m/Y', strtotime($request->desde))
                : 'Sin especificar';

            $hasta = $request->hasta
                ? date('d/m/Y', strtotime($request->hasta))
                : 'Sin especificar';

            $sheet->setCellValue(
                'A2',
                "Período: {$desde} al {$hasta}"
            );

            $sheet->getStyle('A2:F2')->getFont()->setBold(true);

            // ENCABEZADOS
            $encabezados = [
                'Médico',
                'Entrada',
                'Salida',
                'Tiempo trabajado',
                'Estado',
                'Dispositivo'
            ];

            $sheet->fromArray($encabezados, null, 'A4');

            $sheet->getStyle('A4:F4')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]);

            $fila = 5;

            foreach ($datos as $item) {

                $entrada = !empty($item['entrada'])
                    ? date('d/m/Y H:i', strtotime($item['entrada']))
                    : '';

                $salida = !empty($item['salida'])
                    ? date('d/m/Y H:i', strtotime($item['salida']))
                    : '';

                $sheet->setCellValue("A{$fila}", $item['medico']);
                $sheet->setCellValue("B{$fila}", $entrada);
                $sheet->setCellValue("C{$fila}", $salida);

                $sheet->setCellValueExplicit(
                    "D{$fila}",
                    $item['tiempo_trabajado'] ?? '',
                    DataType::TYPE_STRING
                );

                $sheet->setCellValue("E{$fila}", $item['estado']);
                $sheet->setCellValue("F{$fila}", $item['dispositivo']);

                // Colores según estado
                $color = match ($item['estado']) {
                    'COMPLETA' => 'E2F0D9',
                    'INCOMPLETA' => 'FFF2CC',
                    'REVISAR' => 'FCE4D6',
                    default => 'FFFFFF'
                };

                $sheet->getStyle("E{$fila}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($color);

                $fila++;
            }

            $ultimaFila = max(4, $fila - 1);

            // BORDES
            $sheet->getStyle("A4:F{$ultimaFila}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // ANCHO DE COLUMNAS
            $sheet->getColumnDimension('A')->setWidth(35);
            $sheet->getColumnDimension('B')->setWidth(22);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(22);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(30);

            // FILTROS AUTOMÁTICOS
            $sheet->setAutoFilter("A4:F{$ultimaFila}");

            // CONGELAR ENCABEZADOS
            $sheet->freezePane('A5');

            // ALINEACIÓN
            $sheet->getStyle("B5:E{$ultimaFila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // GENERAR ARCHIVO
            $nombreArchivo = 'Reporte_Tarja_Medicos_'
                . now()->format('Ymd_His')
                . '.xlsx';

            $writer = new Xlsx($spreadsheet);

            $rutaTemporal = tempnam(sys_get_temp_dir(), 'tarja_');

            $writer->save($rutaTemporal);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return response()->download(
                $rutaTemporal,
                $nombreArchivo,
                [
                    'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ]
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {

            Log::error('Error exportando tarja médica', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo exportar el reporte.'
            ], 500);
        }
    }
}
