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
}
