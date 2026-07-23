<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class PersonalController extends Controller
{
    public function nominaPersonal()
    {
        return view('personal.nominaPersonal');
    }

    public function controlAsistencia()
    {
        return view('personal.controlAsistencia');
    }

    public function cronogramaPersonal()
    {
        return view('personal.cronogramaPersonal');
    }

    public function registroAsistencia()
    {
        return view('personal.registroAsistencia');
    }

    public function Configuraciones()
    {
        return view('personal.configuraciones');
    }

    public function nominaPersonalBaja()
    {
        return view('personal.personalBaja');
    }

    public function solicitudes()
    {
        return view('novedades.solicitudes');
    }

    public function miLegajo()
    {
        return view('personal.miLegajo');
    }

    public function administrarUsuarios()
    {
        return view('personal.administrarUsuarios');
    }

    public function administrarObraSociales()
    {
        return view('personal.obrasSociales');
    }

    public function controlTarjas()
    {
        return view('personal.tarjas');
    }

    public function listarAreas()
    {
        try {
            $areas = DB::select('CALL SP_LISTA_AREAS()');

            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener áreas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarCategorias()
    {
        try {
            $categorias = DB::select('CALL SP_LISTA_CATEG_EMPLEADOS()');

            return response()->json($categorias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener categorías',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarTipoConvenio()
    {
        try {
            $convenios = DB::select('CALL SP_TIPOS_CONVENIO()');

            return response()->json($convenios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener convenios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarRegimenes()
    {
        try {
            $regimen = DB::select('CALL SP_LISTA_REGIMEN()');

            return response()->json($regimen);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener regimenes',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarEstados()
    {
        try {
            $estados = DB::select('CALL SP_TIPO_ESTADOS()');

            return response()->json($estados);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estados',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarRolesXCategoria($id)
    {
        try {
            $roles = DB::select(
                'CALL SP_LISTA_ROLESXCATEG_EMP(?)',
                [$id]
            );

            return response()->json($roles);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener roles',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarObraSocial()
    {
        $obras = DB::select('CALL SP_LISTA_OBRA_SOCIAL()');

        $data = collect($obras)->map(function ($o) {
            return [
                'id' => $o->id,
                'text' => $o->nombre,
                'codigo' => $o->codigo
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // Iniciamos la transacción para asegurar que si fallan los hijos, 
        // no se cree el empleado a medias.
        DB::beginTransaction();

        try {
            // Normalización de opcionales
            $fechaFinPrueba = $request->fecha_fin_prueba ?: null;
            $idOS           = $request->obra_social_id ?: null;

            DB::statement("
            CALL SP_INSERTAR_EMPLEADO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?,
                @legajo, @mensaje
            )
        ", [
                $request->cuil,
                $request->dni,
                $request->nombre_completo,
                $request->domicilio,
                $request->localidad,
                $request->estado_civil,
                $request->genero,
                $request->fecha_nacimiento,
                $request->fecha_ingreso,
                $fechaFinPrueba,
                $request->posee_convenio,
                $request->area_id,
                $request->servicio_id,
                $request->categoria_id,
                $request->rol_interno_id,
                $request->es_coordinador,
                $request->regimen_horas,
                $request->horas_diarias,
                $idOS,
                $request->posee_titulo,
                $request->titulo,
                $request->matricula_profesional,
                $request->email,
                $request->telefono,
                $request->es_afiliado,
                $request->estado,
                $request->tipo_contrato,
                $request->persona_emerg1,
                $request->contacto_emerg1,
                $request->parentesco_emerg1,
                $request->persona_emerg2,
                $request->contacto_emerg2,
                $request->parentesco_emerg2,
                $request->padreColaborador,
                $request->madreColaborador
            ]);

            // ... después del CALL ...
            $res = DB::select("SELECT @legajo AS legajo, @mensaje AS mensaje");
            $nuevoLegajo = $res[0]->legajo;
            $mensajeSP = $res[0]->mensaje;

            if ($mensajeSP === 'Colaborador registrado correctamente') {
                // AQUÍ es donde insertas los hijos usando $nuevoLegajo
                if ($request->has('hijos')) {
                    foreach ($request->hijos as $hijo) {
                        DB::statement("CALL SP_INSERTAR_FAMILIAR(?, ?, ?)", [
                            $nuevoLegajo,
                            $hijo['nombre'],
                            'Hijo/a'
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'mensaje' => "Empleado registrado con Legajo: " . $nuevoLegajo
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => $mensajeSP // Aquí saldría "El colaborador ya existe" o similar
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar el colaborador',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function normalizarFiltro($valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_array($valor)) {
            return implode(',', $valor);
        }

        return $valor;
    }

    public function listar(Request $request)
    {
        try {
            $areaId   = $this->normalizarFiltro($request->area_id);
            $categId  = $this->normalizarFiltro($request->categ_id);
            $convenio = $this->normalizarFiltro($request->convenio);
            $estado = $this->normalizarFiltro($request->estado);

            $regimen  = $request->p_regimen !== null && $request->p_regimen !== ''
                ? (int)$request->p_regimen
                : null;

            $uti = $request->p_uti !== null && $request->p_uti !== ''
                ? (int)$request->p_uti
                : null;

            $noche = $request->p_noche !== null && $request->p_noche !== ''
                ? (int)$request->p_noche
                : null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS(?, ?, ?, ?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen, $uti, $noche, $estado]
            );

            return response()->json([
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function listarColaboradoresDatatable(Request $request)
    {
        try {
            $areaId   = $this->normalizarFiltro($request->area_id);
            $categId  = $this->normalizarFiltro($request->categ_id);
            $convenio = $this->normalizarFiltro($request->convenio);
            $estado = $this->normalizarFiltro($request->p_estado);

            $regimen  = $request->p_regimen !== null && $request->p_regimen !== ''
                ? (int)$request->p_regimen
                : null;

            $uti = $request->p_uti !== null && $request->p_uti !== ''
                ? (int)$request->p_uti
                : null;

            $noche = $request->p_noche !== null && $request->p_noche !== ''
                ? (int)$request->p_noche
                : null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS_DATATABLE(?, ?, ?, ?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen, $uti, $noche, $estado]
            );

            return response()->json([
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarListaColabDatatable(Request $request)
    {
        $data = DB::select('CALL SP_LISTA_EMPLEADOS(?, ?, ?, ?, ?, ?, ?)', [
            $request->area_id,
            $request->categ_id,
            $request->p_convenio,
            $request->p_regimen,
            $request->p_uti,
            $request->p_noche,
            $request->p_estado,
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 🔹 Encabezados
        $headers = [
            'LEGAJO',
            'COLABORADOR',
            'ESTADO',
            'DNI',
            'CUIL',
            'FECHA NACIMIENTO',
            'EDAD',
            'CORREO',
            'TELEFONO',
            'DOMICILIO',
            'LOCALIDAD',
            'ESTADO CIVIL',
            'GENERO',
            'OBRA SOCIAL',
            'CÓDIGO OS',
            'TITULO',
            'DESCRIPCIÓN TITULO',
            'MP',
            'TIPO DE CONTRATO',
            'FECHA INGRESO',
            'FECHA FIN PRUEBA',
            'FECHA EGRESO',
            'ANTIGÜEDAD',
            'AREA',
            'SERVICIO',
            'CONVENIO',
            'CATEGORIA',
            'REGIMEN',
            'HORAS',
            'COORDINADOR',
            'SINDICATO',
            'NOCHE',
            'UTI',
            'BANCO',
            'CUENTA',
            'CBU',
            'MADRE',
            'PADRE',
            'PERSONA EMERG I',
            'CONTACTO EMERG I',
            'PARENTESCO EMERG I',
            'PERSONA EMERG II',
            'CONTACTO EMERG II',
            'PARENTESCO EMERG II',
        ];

        $sheet->fromArray($headers, NULL, 'A1');

        // 🔹 Datos
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue("A$row", str_pad($item->LEGAJO, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue("B$row", $item->COLABORADOR);
            $sheet->setCellValue("C$row", $item->ESTADO);
            $sheet->setCellValue("D$row", $item->DNI);
            $sheet->setCellValue("E$row", $item->CUIL);
            $sheet->setCellValue("F$row", $item->FECHA_NAC ? date('d/m/Y', strtotime($item->FECHA_NAC)) : '');
            $sheet->setCellValue("G$row", $item->EDAD);
            $sheet->setCellValue("H$row", $item->CORREO);
            $sheet->setCellValue("I$row", $item->TELEFONO);
            $sheet->setCellValue("J$row", $item->DOMICILIO);
            $sheet->setCellValue("K$row", $item->LOCALIDAD);
            $sheet->setCellValue("L$row", $item->ESTADO_CIVIL);
            $sheet->setCellValue("M$row", $item->GENERO);
            $sheet->setCellValue("N$row", $item->OBRA_SOCIAL);
            $sheet->setCellValue("O$row", $item->CODIGO);
            $sheet->setCellValue("P$row", $item->TITULO);
            $sheet->setCellValue("Q$row", $item->DESCRIP_TITULO);
            $sheet->setCellValue("R$row", $item->MAT_PROF);
            $sheet->setCellValue("S$row", $item->TIPO_CONTRATO);
            $sheet->setCellValue("T$row", $item->FECHA_INGRESO ? date('d/m/Y', strtotime($item->FECHA_INGRESO)) : '');
            $sheet->setCellValue("U$row", $item->FECHA_FIN_PRUEBA ? date('d/m/Y', strtotime($item->FECHA_FIN_PRUEBA)) : '');
            $sheet->setCellValue("V$row", $item->FECHA_EGRESO ? date('d/m/Y', strtotime($item->FECHA_EGRESO)) : '');
            $sheet->setCellValue("W$row", $item->ANTIGUEDAD);
            $sheet->setCellValue("X$row", $item->AREA);
            $sheet->setCellValue("Y$row", $item->SERVICIO);
            $sheet->setCellValue("Z$row", $item->CONVENIO);
            $sheet->setCellValue("AA$row", $item->CATEGORIA);
            $sheet->setCellValue("AB$row", $item->REGIMEN);
            $sheet->setCellValue("AC$row", $item->HORAS_DIARIAS);
            $sheet->setCellValue("AD$row", $item->COORDINADOR);
            $sheet->setCellValue("AE$row", $item->AFILIADO);
            $sheet->setCellValue("AF$row", $item->NOCHE);
            $sheet->setCellValue("AG$row", $item->UTI);
            $sheet->setCellValue("AH$row", $item->banco);
            $sheet->setCellValueExplicit("AI$row", $item->numero_cuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("AJ$row", $item->cbu, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("AK$row", $item->MADRE);
            $sheet->setCellValue("AL$row", $item->PADRE);
            $sheet->setCellValue("AM$row", $item->PERSONA_EMERG1);
            $sheet->setCellValue("AN$row", $item->CONTACTO_EMERG1);
            $sheet->setCellValue("AO$row", $item->PARENTESCO_EMERG1);
            $sheet->setCellValue("AP$row", $item->PERSONA_EMERG2);
            $sheet->setCellValue("AQ$row", $item->CONTACTO_EMERG2);
            $sheet->setCellValue("AR$row", $item->PARENTESCO_EMERG2);
            $row++;
        }

        for ($col = 'A'; $col !== 'AS'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'NOMINA_COLABORADORES' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }

    public function exportarListaColabBajaDatatable(Request $request)
    {
        $data = DB::select('CALL SP_LISTA_EMPLEADOS_BAJA(?, ?, ?, ?)', [
            $request->area_id,
            $request->categ_id,
            $request->p_convenio,
            $request->p_regimen
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'LEGAJO',
            'COLABORADOR',
            'DNI',
            'FECHA INGRESO',
            'AREA',
            'CATEGORIA',
            'REGIMEN',
            'HORAS DIARIAS',
            'CONVENIO',
            'ESTADO',
            'FECHA EGRESO',   // Extraído del SP
            'MOTIVO BAJA'     // Extraído del SP
        ];

        $sheet->fromArray($headers, NULL, 'A1');

        // 🔹 Datos
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue("A$row", $item->LEGAJO);
            $sheet->setCellValue("B$row", $item->COLABORADOR);
            $sheet->setCellValue("C$row", $item->DNI);
            $sheet->setCellValue("D$row", $item->FECHA_INGRESO);
            $sheet->setCellValue("E$row", $item->AREA);
            $sheet->setCellValue("F$row", $item->CATEGORIA);
            $sheet->setCellValue("G$row", $item->REGIMEN);
            $sheet->setCellValue("H$row", $item->HORAS_DIARIAS);
            $sheet->setCellValue("I$row", $item->CONVENIO);
            $sheet->setCellValue("J$row", $item->ESTADO);
            $sheet->setCellValue("K$row", $item->FECHA_EGRESO);
            $sheet->setCellValue("L$row", $item->MOTIVO_BAJA);

            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'colaboradores_baja_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }

    public function listarCuentasBancarias(Request $request)
    {
        try {
            $legajo = $request->legajo;

            $cuentas = DB::select('CALL SP_LISTAR_CUENTAS_BANCARIAS(?)', [$legajo]);

            return response()->json([
                'success' => true,
                'data' => $cuentas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actualizarDatosCuentasBancarias(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|integer',
                'cbu' => 'nullable|digits:22',
                'numero_cuenta' => 'nullable|string|max:300'
            ]);

            $id = $request->id;
            $cbu = $request->cbu;
            $numeroCuenta = $request->numero_cuenta;

            $resultado = DB::select(
                'CALL SP_EDITAR_DATOS_CUENTAS_BANCARIAS(?, ?, ?)',
                [$id, $numeroCuenta, $cbu]
            );

            if (!empty($resultado)) {
                return response()->json([
                    'success' => $resultado[0]->success,
                    'mensaje' => $resultado[0]->mensaje
                ]);
            }

            return response()->json([
                'success' => 0,
                'mensaje' => 'No se obtuvo respuesta del servidor'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage() // opcional (solo debug)
            ], 500);
        }
    }

    public function eliminarCuentaBancaria(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer'
            ]);

            $id = $request->id;

            $resultado = DB::select(
                'CALL SP_ELIMINAR_CUENTA_BANCARIA(?)',
                [$id]
            );

            if (!empty($resultado)) {
                return response()->json([
                    'success' => $resultado[0]->success,
                    'mensaje' => $resultado[0]->mensaje
                ]);
            }

            return response()->json([
                'success' => 0,
                'mensaje' => 'No se obtuvo respuesta del servidor'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage() // opcional debug
            ], 500);
        }
    }

    public function crearCuentaBancaria(Request $request)
    {
        try {
            $request->validate([
                'legajo' => 'required|integer',
                'numero_cuenta' => 'required|string|max:300',
                'cbu' => 'required|digits:22',
                'banco' => 'required|string|max:100'
            ]);

            $res = DB::select(
                'CALL SP_CREAR_CUENTA_BANCARIA(?, ?, ?, ?)',
                [
                    $request->legajo,
                    $request->numero_cuenta,
                    $request->cbu,
                    $request->banco
                ]
            );

            if (!empty($res)) {
                return response()->json([
                    'success' => $res[0]->success,
                    'mensaje' => $res[0]->mensaje
                ]);
            }

            return response()->json([
                'success' => 0,
                'mensaje' => 'No se obtuvo respuesta del servidor'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage() // opcional debug
            ], 500);
        }
    }

    public function priorizarCuentaBancaria(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|integer',
                'legajo' => 'required|integer'
            ]);

            $res = DB::select(
                'CALL SP_PRIORIZAR_CUENTA_BANCARIA(?, ?)',
                [
                    $request->id,
                    $request->legajo
                ]
            );

            if (!empty($res)) {
                return response()->json([
                    'success' => $res[0]->success,
                    'mensaje' => $res[0]->mensaje
                ]);
            }

            return response()->json([
                'success' => 0,
                'mensaje' => 'No se obtuvo respuesta del servidor'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => 0,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage() // opcional debug
            ], 500);
        }
    }

    public function listarCargaMasiva(Request $request)
    {
        try {
            $areaId    = $request->area_id ?: null;
            $categId   = $request->categ_id ?: null;
            $convenio  = $request->p_convenio ?: null;
            $regimen   = $request->p_regimen ?: null;
            $uti   = $request->p_uti ?: null;
            $noche   = $request->p_noche ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS_CARGA_MASIVA(?, ?, ?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen, $uti, $noche]
            );

            return response()->json([
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function listarPersonalBaja(Request $request)
    {
        try {
            $areaId    = $request->area_id ?: null;
            $categId   = $request->categ_id ?: null;
            $convenio  = $request->convenio ?: null;
            $regimen   = $request->p_regimen ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS_BAJA(?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen]
            );

            return response()->json([
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function verLegajo($legajo)
    {
        try {
            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
            DB::statement("SET @dummy = 1");

            if (empty($empleadoData)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el legajo'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $empleadoData[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarFamiliares(Request $request)
    {
        try {
            $legajo = $request->legajo;
            $familiares = DB::select("CALL SP_LISTAR_FAMILIARES(?)", [$legajo]);

            return response()->json([
                'success' => true,
                'data' => $familiares
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener los hijos del colaborador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editarFamiliares(Request $request)
    {
        try {
            $idHijo = $request->idHijo;
            $legajo = $request->legajo;
            $nombre = $request->nombre;
            $dni = $request->dni;
            $fechaNacimiento = $request->fechaNacimiento;

            DB::statement("CALL SP_EDITAR_HIJOS(?,?,?,?,?)", [$idHijo, $legajo, $nombre, $dni, $fechaNacimiento]);

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function quitarFamiliares(Request $request)
    {
        try {
            $idHijo = $request->idHijo;
            $legajo = $request->legajo;

            DB::statement("CALL SP_QUITAR_HIJOS(?,?)", [$idHijo, $legajo]);

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function agregarFamiliares(Request $request)
    {
        try {
            $legajo = $request->legajo;
            $nombre = $request->nombre;
            $parentesco = 'HIJO/A';
            $dni = $request->dni;
            $fechaNacimiento = $request->fechaNacimiento;

            DB::statement("CALL SP_INSERTAR_FAMILIAR(?,?,?,?,?)", [$legajo, $nombre, $parentesco, $dni, $fechaNacimiento]);

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bajaEmpleado(Request $request, $legajo)
    {
        try {

            $fechaBaja = $request->fechaBaja;
            $motivo = $request->motivo;
            $observaciones = $request->observaciones;
            $registrante = auth()->user()->name;

            DB::statement(
                "CALL SP_DAR_BAJA_EMPLEADO(?, ?, ?, ?, ?, @mensaje)",
                [
                    $legajo,
                    $fechaBaja,
                    $motivo,
                    $observaciones,
                    $registrante
                ]
            );

            $res = DB::select("SELECT @mensaje AS mensaje");
            $mensaje = $res[0]->mensaje ?? 'Error desconocido';

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al dar de baja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function infoMinimaEmpleado($legajo)
    {
        $data = DB::select("CALL SP_INFO_MINIMA_EMPLEADO(?)", [$legajo]);

        return response()->json($data[0] ?? null);
    }

    public function historialNovedades($legajo)
    {
        try {
            $ListaNovedades = DB::select(
                "CALL SP_LISTA_NOVEDADESXCOLABORADOR(?,?,?,?,?)",
                [
                    $legajo,
                    null, // liquidada
                    null, // desde
                    null, // hasta
                    null  // idNovedad
                ]
            );

            return response()->json([
                'data' => $ListaNovedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar historial',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarServiciosxArea($id)
    {
        try {
            $servicios = DB::select(
                'CALL SP_LISTA_SERVICIOS(?)',
                [$id]
            );

            return response()->json($servicios);
        } catch (\Throwable $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar servicios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function show($legajo)
    {
        try {
            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
            $familiares = DB::select("CALL SP_LISTAR_FAMILIARES(?)", [$legajo]);

            if (!empty($empleadoData)) {
                return response()->json([
                    'success' => true,
                    'data' => $empleadoData[0],
                    'familiares' => $familiares // Enviamos el array de hijos aquí
                ]);
            }

            return response()->json(['success' => false, 'mensaje' => 'No se encontró el legajo']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $legajo)
    {
        DB::table('empleados')
            ->where('LEGAJO', $legajo)
            ->update(
                $request->except(['_token', '_method', 'legajo'])
            );

        return response()->json(['success' => true]);
    }

    public function registrarSolicitud(Request $request)
    {
        try {

            DB::statement(
                "CALL SP_REGISTRAR_SOLICITUD(?, ?, ?, ?, ?, @p_mensaje)",
                [
                    $request->tipoSolicitud,
                    $request->legajo,
                    $request->registrante,
                    $request->observ,
                    $request->monto
                ]
            );

            $mensaje = DB::selectOne("SELECT @p_mensaje as mensaje");

            $ok = $mensaje->mensaje === 'Solicitud registrada correctamente';

            return response()->json([
                'ok' => $ok,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al registrar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarSolicitudes(Request $request)
    {
        try {
            $fechaDesde = $request->fechaDesde !== '' ? $request->fechaDesde : null;
            $fechaHasta = $request->fechaHasta !== '' ? $request->fechaHasta : null;
            $areaId = $request->area_id !== '' ? $request->area_id : null;
            $estado = $request->estado !== '' ? $request->estado : null;
            $depositado = $request->depositado !== null && $request->depositado !== ''
                ? (int) $request->depositado
                : null;

            $legajo = Auth::user()->legajo ?? null;
            $rol = Auth::user()->rol ?? null;

            $solicitudes = DB::select(
                "CALL SP_LISTA_SOLICITUDES(?, ?, ?, ?, ?, ?, ?)",
                [$fechaDesde, $fechaHasta, $estado, $areaId, $legajo, $rol, $depositado]
            );

            return response()->json([
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function aprobarSolicitud(Request $request)
    {
        try {

            DB::beginTransaction();

            foreach ($request->ids as $idSolicitud) {

                DB::statement(
                    "CALL SP_APROBAR_SOLICITUD(?, ?, @p_msj)",
                    [
                        $idSolicitud,
                        auth()->id()
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Solicitudes aprobadas correctamente'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al aprobar solicitudes',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function rechazarSolicitud(Request $request)
    {
        try {

            DB::beginTransaction();

            foreach ($request->ids as $idSolicitud) {

                DB::statement(
                    "CALL SP_RECHAZAR_SOLICITUD(?, @p_msj)",
                    [
                        $idSolicitud
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Solicitudes rechazadas correctamente'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al rechazar solicitudes',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function anularSolicitud(Request $request)
    {
        try {
            $request->validate([
                'idSolicitud' => 'required|integer'
            ]);

            $idSolicitud = $request->idSolicitud;

            DB::statement("CALL SP_ANULAR_SOLICITUD(?, @p_msj)", [$idSolicitud]);

            $mensaje = DB::selectOne("SELECT @p_msj as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al anular solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function depositarAdelantos(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || count($ids) == 0) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se seleccionaron solicitudes'
            ]);
        }

        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                DB::statement('CALL SP_DEPOSITAR_SOLICITUDES_ADELANTOS(?)', [$id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Depósitos realizados correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al depositar',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function verMiLegajo()
    {
        try {
            $legajo = Auth::user()->legajo;

            if (!$legajo) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El usuario no tiene un legajo asignado'
                ], 400);
            }

            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
            DB::statement("SET @dummy = 1");


            if (empty($empleadoData)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el legajo'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $empleadoData[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarMiLegajo(Request $request)
    {
        try {

            $legajo = Auth::user()->legajo;
            $reqs = $request->req_alimenticios ?? [];
            $observ = $request->req_alimenticio_otro ?? '';

            DB::statement("CALL SP_ACTUALIZAR_MI_LEGAJO(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                $legajo,
                $request->correo,
                $request->telefono,
                $request->domicilio,
                $request->localidad,
                $request->estadoCivil,
                $request->genero,
                $request->personaEmerg1,
                $request->telefEmerg1,
                $request->parentesco1,
                $request->personaEmerg2,
                $request->telefEmerg2,
                $request->parentesco2,
                $request->padre,
                $request->madre
            ]);

            DB::statement("CALL SP_LIMPIAR_REQ_ALIMENTICIOS(?)", [
                $legajo
            ]);

            foreach ($reqs as $r) {
                $obs = ($r == 12) ? $observ : '';
                DB::statement(
                    "CALL SP_REGISTRAR_REQUERIMIENTO_ALIMENTARIO(?,?,?)",
                    [$r, $legajo, $obs]
                );
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Legajo actualizado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarLegajoColaborador(Request $request, $legajo)
    {

        $reqs = $request->req_alimenticios ?? [];
        $observ = $request->req_alimenticio_otro ?? '';

        try {

            DB::statement("CALL SP_ACTUALIZAR_LEGAJO_COLABORADOR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                $legajo,
                $request->dni,
                $request->cuil,
                $request->colaborador,
                $request->fechaIngreso,
                $request->correo,
                $request->telefono,
                $request->domicilio,
                $request->localidad,
                $request->estado_civil,
                $request->genero,
                $request->obra_social_id,
                $request->descrip_titulo,
                $request->mat_prof,
                $request->personaEmerg1,
                $request->telefEmerg1,
                $request->parentesco1,
                $request->personaEmerg2,
                $request->telefEmerg2,
                $request->parentesco2,
                $request->padre,
                $request->madre,
                $request->tipo_contrato,
                $request->area_id,
                $request->servicio_id,
                $request->posee_convenio,
                $request->categoria_id,
                $request->rol_interno_id,
                $request->regimen_horas,
                $request->horas_diarias,
                $request->es_coordinador,
                $request->es_afiliado,
                $request->uti,
                $request->noche,
                $request->fechaNacimiento
            ]);

            DB::statement("CALL SP_LIMPIAR_REQ_ALIMENTICIOS(?)", [
                $legajo
            ]);

            foreach ($reqs as $r) {
                $obs = ($r == 12) ? $observ : '';
                DB::statement(
                    "CALL SP_REGISTRAR_REQUERIMIENTO_ALIMENTARIO(?,?,?)",
                    [$r, $legajo, $obs]
                );
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Legajo actualizado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarUsuarios(Request $request)
    {
        $area = $request->input('area');     // puede venir null
        $estado = $request->input('estado'); // puede venir null

        $usuarios = DB::select('CALL SP_LISTA_USUARIOS(?, ?)', [
            $area,
            $estado
        ]);

        return response()->json([
            'data' => $usuarios
        ]);
    }

    public function crearUsuario(Request $request)
    {
        try {

            $name = $request->name;
            $legajo = $request->legajo;
            $username = $request->username ?: $legajo;
            $password = bcrypt($request->password);
            $rol = $request->rol;
            $area = $request->area;

            DB::statement('CALL SP_REGISTRAR_USUARIO(?, ?, ?, ?, ?, ?, @p_mensaje)', [
                $name,
                $legajo,
                $username,
                $password,
                $rol,
                $area
            ]);

            $resultado = DB::select('SELECT @p_mensaje as mensaje');
            $mensaje = $resultado[0]->mensaje ?? 'ERROR';

            if ($mensaje !== 'OK') {
                return response()->json([
                    'ok' => false,
                    'mensaje' => $mensaje
                ], 400);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario creado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtener($legajo)
    {
        $usuario = DB::table('users')
            ->where('legajo', $legajo)
            ->first();

        return response()->json($usuario);
    }

    public function actualizar(Request $request)
    {
        try {

            $data = [
                'name' => $request->name,
                'username' => $request->username ?: $request->legajo,
                'rol' => $request->rol,
                'area_id' => $request->area
            ];

            // solo actualiza password si viene
            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            }

            DB::table('users')
                ->where('legajo', $request->legajo)
                ->update($data);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario actualizado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al actualizar'
            ], 500);
        }
    }

    public function baja(Request $request)
    {
        try {

            $legajo = $request->legajo;

            DB::statement('CALL SP_BAJA_USUARIO(?, @p_mensaje)', [$legajo]);

            $resultado = DB::select('SELECT @p_mensaje as mensaje');
            $mensaje = $resultado[0]->mensaje ?? 'ERROR';

            if ($mensaje !== 'OK') {
                return response()->json([
                    'ok' => false,
                    'mensaje' => $mensaje
                ], 400);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario dado de baja correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al dar de baja'
            ], 500);
        }
    }

    public function listaRequerimientos()
    {
        $data = DB::table('requerimientos_alimenticios')
            ->orderBy('id')
            ->get();

        return response()->json($data);
    }

    public function obtenerReqAlimenticios($legajo)
    {
        $data = DB::select(
            "CALL SP_LISTAR_REQ_ALIMENTICIOS_COLAB(?)",
            [$legajo]
        );

        return response()->json($data);
    }

    public function registraNuevaOs(Request $request)
    {
        try {
            $nombre = $request->input('nombreOs');
            $codigo = $request->input('codigoOs');

            DB::statement('CALL SP_CREAR_OBRA_SOCIAL(?, ?)', [
                $nombre,
                $codigo
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Obra social registrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar la obra social',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verObraSocial($id)
    {
        $obraSocial = DB::select(
            'CALL SP_VER_OBRA_SOCIAL(?)',
            [$id]
        );

        if (empty($obraSocial)) {
            return response()->json(['error' => 'Obra social no encontrada'], 404);
        }

        return response()->json($obraSocial[0]);
    }

    public function editarObraSocial(Request $request)
    {
        try {

            $request->validate([
                'idOs' => 'required|integer',
                'nombreOsEdit' => 'required|string|max:500',
                'codigoOsEdit' => 'required|string|max:255',
            ]);

            DB::statement('CALL SP_MODIFICAR_OBRA_SOCIAL(?, ?, ?)', [
                $request->idOs,
                $request->nombreOsEdit,
                $request->codigoOsEdit
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Obra social actualizada correctamente'
            ]);
        } catch (\Exception $e) {

            \Log::error('Error al editar obra social', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar la obra social',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function enviarIngresoAsistencia(Request $request)
    {
        try {
            $fechaHora = now();

            $request->validate([
                'turno' => 'required|string|max:20',
                'servicio' => 'required|string|max:100',
                'latitud' => 'required',
                'longitud' => 'required',
            ]);

            DB::statement("CALL SP_INGRESO_ASISTENCIA(?,?,?,?,?,?,?, @p_msj)", [
                Auth::user()->id,
                $request->turno,
                $request->servicio,
                $request->latitud,
                $request->longitud,
                $request->ip(),
                $fechaHora->format('Y-m-d H:i:s'),
            ]);

            $mensaje = DB::selectOne(
                "SELECT @p_msj as mensaje"
            );

            $success = $mensaje->mensaje === 'Ingreso marcado correctamente.';

            return response()->json([
                'success' => $success,
                'message' => $mensaje->mensaje,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el ingreso.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enviarEgresoAsistencia(Request $request)
    {
        try {
            $fechaHora = now();

            $request->validate([
                'turno' => 'required|string|max:20',
                'servicio' => 'required|string|max:100',
                'latitud' => 'required',
                'longitud' => 'required'
            ]);

            DB::statement("CALL SP_EGRESO_ASISTENCIA(?,?,?,?,?,?,?,@p_msj)", [
                Auth::user()->id,
                $request->turno,
                $request->servicio,
                $request->latitud,
                $request->longitud,
                $request->ip(),
                $fechaHora->format('Y-m-d H:i:s'),
            ]);

            $mensaje = DB::selectOne(
                "SELECT @p_msj as mensaje"
            );

            $success = $mensaje->mensaje === 'Egreso marcado correctamente.';

            return response()->json([
                'success' => $success,
                'message' => $mensaje->mensaje,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el egreso.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
