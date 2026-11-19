<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class DescriptivosController extends Controller
{
    public function index()
    {
        return view('personal.descriptivos');
    }

    public function miDescriptivo()
    {
        return view('personal.miDescriptivo');
    }

    public function obtenerDescriptivos(Request $request)
    {
        $p_id_area = $request->p_id_area;
        $p_estado = $request->p_estado;
        $p_solo_ultima = $request->p_solo_ultima;

        $descriptivos = DB::select('CALL SP_DESCRIPTIVOS_LISTAR(?, ?, ?, ?)', [
            auth()->id(),
            $request->p_id_area,
            $request->p_estado,
            $request->p_solo_ultima,
        ]);
        return response()->json(["data" => $descriptivos]);
    }

    public function verDetalleDescriptivo(Request $request)
    {
        $p_id_descriptivo = $request->p_id_descriptivo;

        $descriptivo = DB::select('CALL SP_VER_DESCRIPTIVO(?, ?)', [$request->p_id_descriptivo, auth()->id()]);
        $competencias = DB::select("call SP_VER_DESCRIPTIVO_COMPETENCIAS(?)", [$p_id_descriptivo]);
        return response()->json(["data" => $descriptivo[0], "competencias" => $competencias,]);
    }

    public function altaDescriptivo(Request $request)
    {
        $request->validate([
            'codigoPuesto'              => 'required|string|max:255',
            'nombrePuesto'              => 'required|string|max:512',
            'idArea'                    => 'required|integer',
            'idServicio'                => 'required|integer',
            'reportaA'                  => 'nullable|string|max:512',
            'supervisorDirecto'         => 'nullable|string|max:512',
            'supervisaA'                => 'nullable|string|max:512',
            'propositoPuesto'           => 'nullable|string',
            'tareas'                    => 'nullable|string',
            'relacionesInternas'        => 'nullable|string',
            'relacionesExternas'        => 'nullable|string',
            'formacion'                 => 'nullable|string',
            'experiencia'               => 'nullable|string',
            'conocimientos'             => 'nullable|string',
            'capacitacionContinua'      => 'nullable|string',
            'normasCalidad'             => 'nullable|string',
            'compromisoAmbiental'       => 'nullable|string',
            'seguridadSaludOcupacional' => 'nullable|string',
            'eticaConfidencialidad'     => 'nullable|string',
            'contribucion'              => 'nullable|string',
            'competenciaTipo.*'         => 'nullable|in:ORGANIZACIONAL,FUNCIONAL',
            'competenciaNombre.*'       => 'nullable|string|max:512',
            'competenciaDescripcion.*'  => 'nullable|string',
        ]);

        $audit = [auth()->id(), $request->ip(), substr((string) $request->userAgent(), 0, 512)];

        try {
            $idDescriptivo = DB::transaction(function () use ($request, $audit) {

                // 1) Cabecera
                DB::statement('CALL SP_ALTA_DESCRIPTIVO(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, @id_descriptivo)', [
                    $request->codigoPuesto,
                    $request->nombrePuesto,
                    $request->idArea,
                    $request->idServicio,
                    $request->reportaA,
                    $request->supervisorDirecto,
                    $request->supervisaA,
                    $request->propositoPuesto,
                    $request->tareas,
                    $request->relacionesInternas,
                    $request->relacionesExternas,
                    $request->formacion,
                    $request->experiencia,
                    $request->conocimientos,
                    $request->capacitacionContinua,
                    $request->normasCalidad,
                    $request->compromisoAmbiental,
                    $request->seguridadSaludOcupacional,
                    $request->eticaConfidencialidad,
                    $request->contribucion,
                    ...$audit,
                ]);
                $id = DB::selectOne('SELECT @id_descriptivo AS id')->id;

                // 2) Competencias (en el orden del formulario: genéricas y después específicas)
                $orden = 1;
                foreach ((array) $request->competenciaNombre as $i => $nombre) {
                    if (trim((string) $nombre) === '') continue;   // fila vacía: se ignora

                    DB::statement('CALL SP_ALTA_COMPETENCIA_DESCRIPTIVO(?,?,?,?,?)', [
                        $id,
                        $request->competenciaTipo[$i] ?? null,
                        $nombre,
                        $request->competenciaDescripcion[$i] ?? null,
                        $orden++,
                    ]);
                }

                return $id;
            });
        } catch (QueryException $e) {
            // SIGNAL 45000 = validación de negocio del SP
            if (($e->errorInfo[0] ?? null) === '45000') {
                return response()->json(['message' => $e->errorInfo[2]], 422);
            }
            Log::error('Alta de descriptivo', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo guardar el descriptivo.'], 500);
        }

        return response()->json([
            'message' => 'Descriptivo creado como borrador.',
            'data'    => ['id_descriptivo' => $idDescriptivo],
        ]);
    }

    public function cambiarEstadoDescriptivo(Request $request)
    {
        $request->validate([
            'idDescriptivo' => 'required|integer',
            'accion'        => 'required|in:validar,devolver,publicar',
            'motivo'        => 'nullable|string|max:500',
        ]);

        $user    = auth()->user();
        $esAdmin = $user->rol === 'Administrador/a';

        $desc = DB::select('CALL SP_VER_DESCRIPTIVO(?)', [$request->idDescriptivo]);
        if (empty($desc)) {
            return response()->json(['message' => 'El descriptivo no existe.'], 404);
        }

        // permisos: valida el coordinador de su área (o admin); devuelve y publica solo admin
        $esCoordDelArea = in_array($user->rol, ['Coordinador/a', 'Coordinador/a L2'])
            && (int) $user->area_id === (int) $desc[0]->id_area;

        $puede = $request->accion === 'validar' ? ($esAdmin || $esCoordDelArea) : $esAdmin;
        if (!$puede) {
            return response()->json(['message' => 'No tenés permiso para esta acción.'], 403);
        }

        $audit = [$user->id, $request->ip(), substr((string) $request->userAgent(), 0, 512)];

        try {
            DB::transaction(function () use ($request, $audit) {
                switch ($request->accion) {
                    case 'validar':
                        DB::statement('CALL SP_VALIDAR_DESCRIPTIVO(?,?,?,?)', [$request->idDescriptivo, ...$audit]);
                        break;
                    case 'devolver':
                        DB::statement('CALL SP_DEVOLVER_DESCRIPTIVO(?,?,?,?,?)', [$request->idDescriptivo, $request->motivo, ...$audit]);
                        break;
                    case 'publicar':
                        DB::statement('CALL SP_PUBLICAR_DESCRIPTIVO(?,?,?,?)', [$request->idDescriptivo, ...$audit]);
                        break;
                }
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '45000') {
                return response()->json(['message' => $e->errorInfo[2]], 422);
            }
            Log::error('Cambio de estado de descriptivo', ['id' => $request->idDescriptivo, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo cambiar el estado del descriptivo.'], 500);
        }

        $textos = ['validar' => 'Descriptivo validado.', 'devolver' => 'Descriptivo devuelto a borrador.', 'publicar' => 'Descriptivo publicado.'];
        return response()->json(['message' => $textos[$request->accion]]);
    }

    public function verMiDescriptivo()
    {
        $mi = DB::select('CALL SP_MI_DESCRIPTIVO(?)', [auth()->id()]);
        $mi = $mi[0] ?? null;

        if (!$mi || !$mi->id_descriptivo) {
            return response()->json([
                'situacion' => $mi->situacion ?? 'SIN_VINCULO',
                'puesto'    => $mi->puesto ?? null,
            ]);
        }

        $descriptivo  = DB::select('CALL SP_VER_DESCRIPTIVO(?, ?)', [$mi->id_descriptivo, auth()->id()]);
        $competencias = DB::select('CALL SP_VER_DESCRIPTIVO_COMPETENCIAS(?)', [$mi->id_descriptivo]);

        return response()->json([
            'situacion'    => $mi->situacion,
            'signed_at'    => $mi->signed_at,
            'data'         => $descriptivo[0] ?? null,
            'competencias' => $competencias,
        ]);
    }

    public function aceptarMiDescriptivo(Request $request)
    {
        $request->validate(['idDescriptivo' => 'required|integer']);

        try {
            DB::transaction(function () use ($request) {
                DB::statement('CALL SP_ACEPTAR_DESCRIPTIVO(?, ?, ?, ?)', [
                    auth()->id(),
                    $request->idDescriptivo,
                    $request->ip(),
                    substr((string) $request->userAgent(), 0, 512),
                ]);
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '45000') {
                return response()->json(['message' => $e->errorInfo[2]], 422);
            }
            Log::error('Aceptar descriptivo', ['user' => auth()->id(), 'error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo registrar la aceptación.'], 500);
        }

        return response()->json(['message' => 'Aceptaste tu descriptivo de puesto.']);
    }
}
