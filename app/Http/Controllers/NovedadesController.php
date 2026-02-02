<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class NovedadesController extends Controller
{
    public function registroNovedades()
    {
        return view('novedades.registroNovedades');
    }

    public function controlNovedades()
    {
        return view('novedades.controlNovedades');
    }

    public function configNovedades()
    {
        return view('novedades.configNovedades');
    }

    public function listarCategorias()
    {
        try {
            $rol = Auth::user()->rol;

            $categorias = DB::select(
                'CALL SP_LISTA_CATEG_NOVEDADES_POR_ROL(?)',
                [$rol]
            );

            return response()->json($categorias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar categorías',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarNovedadesSelector()
    {
        try {

            $novedades = DB::select('CALL SP_BUSCAR_NOVEDADES()');

            return response()->json($novedades);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error SP',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function cargarTablaNovedades()
    {
        try {
            $data = DB::select('CALL SP_TABLA_NOVEDADES()');
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registrarNovedad(Request $request)
    {
        try {

            DB::statement(
                "CALL SP_REGISTRAR_NOVEDAD(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @mensaje
            )",
                [
                    $request->legajo,
                    $request->idNovedad,
                    now(), // fecha registro
                    $request->fechaDesde,
                    $request->fechaHasta,
                    $request->fechaAplicacion,
                    $request->valor1 ?? null,
                    $request->valor2 ?? null,
                    $request->centroCosto ?? null,
                    $request->cantidadFinal,
                    auth()->user()->name,
                    $request->descripcion ?? null,
                    $request->tipoVacaciones ?? null,
                    $request->annio ?? null,
                ]
            );

            $resultado = DB::select("SELECT @mensaje AS mensaje");

            return response()->json([
                'success' => true,
                'mensaje' => $resultado[0]->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar la novedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarNovedadesPorArea(Request $request)
    {
        try {
            $areaId = ($request->area_id && $request->area_id !== "") ? (int)$request->area_id : null;
            $idNovedad = ($request->idNovedad && $request->idNovedad !== "") ? (int)$request->idNovedad : null;

            $ListaNovedades = DB::select("CALL SP_LISTA_NOVEDADES_REGISTRADAS(?,?)", [$areaId, $idNovedad]);

            return response()->json([
                'data' => $ListaNovedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function verNovedad($codigo)
    {
        $novedad = DB::select(
            'CALL SP_VER_NOVEDAD(?)',
            [$codigo]
        );

        if (empty($novedad)) {
            return response()->json(['error' => 'Novedad no encontrada'], 404);
        }

        return response()->json($novedad[0]);
    }

    public function altaNuevaNovedad(Request $request)
    {
        $request->validate([
            'codigo'     => 'required|string|max:50',
            'nombre'     => 'required|string|max:250',
            'tipo_valor' => 'nullable|string|max:50'
        ]);

        DB::statement(
            'CALL SP_NUEVA_NOVEDAD(?, ?, ?, ?, @mensaje)',
            [
                $request->codigo,
                $request->nombre,
                $request->tipo_valor
            ]
        );

        $mensaje = DB::selectOne('SELECT @mensaje AS mensaje')->mensaje;

        return response()->json([
            'mensaje' => $mensaje
        ]);
    }

    public function subirComprobante(Request $request)
    {
        $request->validate([
            'id_nxe' => 'required|integer|exists:novedadxempleados,id_nxe',
            'comprobantes' => 'required',
            'comprobantes.*' => 'file|max:5120'
        ]);

        DB::beginTransaction();

        try {

            $idNxe   = $request->id_nxe;
            $usuario = auth()->id();

            foreach ($request->file('comprobantes') as $archivo) {

                $nombreOriginal = $archivo->getClientOriginalName();
                $mime           = $archivo->getMimeType();
                $extension      = $archivo->getClientOriginalExtension();

                $nombreArchivo = uniqid('comp_') . '.' . $extension;
                $ruta = "comprobantes/novedades/{$idNxe}";

                $archivo->storeAs($ruta, $nombreArchivo);

                $idComprobante = DB::table('comprobantes')->insertGetId([
                    'nombre_original' => $nombreOriginal,
                    'nombre_archivo'  => $nombreArchivo,
                    'ruta'            => $ruta,
                    'tipo_mime'       => $mime,
                    'usuario'         => $usuario,
                    'fecha_subida'    => now()
                ]);

                DB::table('comprobantesxnovedad')->insert([
                    'id_comprobante' => $idComprobante,
                    'id_novedad'     => $idNxe
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Comprobante/s subido/s correctamente'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al subir el comprobante',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function listarComprobantes($idNovedad)
    {
        $comprobantes = DB::table('comprobantes as c')
            ->join('comprobantesxnovedad as cxn', 'cxn.id_comprobante', '=', 'c.id')
            ->where('cxn.id_novedad', $idNovedad) // 👈 ESTE ID
            ->select('c.id')
            ->orderBy('c.fecha_subida')
            ->get();

        return response()->json($comprobantes);
    }

    public function verComprobante($id)
    {
        $comp = DB::table('comprobantes')->where('id', $id)->first();

        if (!$comp) {
            abort(404);
        }

        $path = $comp->ruta . '/' . $comp->nombre_archivo;

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('private')->response(
            $path,
            null,
            ['Content-Type' => $comp->tipo_mime]
        );
    }

    public function listar()
    {
        try {

            $novedades = DB::select('CALL SP_LISTA_NOVEDADES()');

            return response()->json($novedades);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar novedades',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
