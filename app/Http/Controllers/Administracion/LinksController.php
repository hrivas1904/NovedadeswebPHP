<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PedidoCompra;

class LinksController extends Controller
{
    public function index()
    {
        return view('administracion.links.index');
    }

    public function obtenerLinksImportacion(Request $request)
    {
        $estado = $request->p_estado;
        try {
            $links = DB::select("call SP_OBTENER_LINKS_IMPORTACION(?)", [$estado]);
            return response()->json(["data" => $links]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        };
    }

    public function crearNuevoLinkImportacion(Request $request)
    {
        $p_nombre = $request->nombreArtefacto;
        $p_descripcion = $request->descripcionArtefacto;
        $p_url = $request->linkArtefacto;
        $p_icono = $request->iconoArtefacto;

        try {

            DB::statement("call SP_CREAR_NUEVO_LINK_IMPORTACION(?,?,?,?)", [$p_nombre, $p_descripcion, $p_url, $p_icono]);
            return response()->json([
                'success' => true,
                'mensaje' => 'El link de importación fue creado correctamente.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        };
    }

    public function eliminarLinkImportacion(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        try {

            DB::statement(
                "CALL SP_ELIMINAR_LINK_VISTA(?)",
                [$data['id']]
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'El link de importación fue eliminado correctamente.'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Error al eliminar link de importación', [
                'id_link' => $data['id'],
                'usuario_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Ocurrió un error al eliminar el link de importación.'
            ], 500);
        }
    }
}
