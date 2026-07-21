<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Http\Request;

class AdministracionController extends Controller
{
    public function pedidosComprasView()
    {
        return view('administracion.pedidosCompras');
    }

    public function panelAdminView()
    {
        return view('administracion.panelAdmin');
    }

    public function productosProveedoresView()
    {
        return view('administracion.productosProveedores');
    }

    public function listarCentrosCosto()
    {
        $centros = DB::select("CALL SP_LISTAR_CENTRO_COSTOS()");

        return response()->json($centros);
    }

    public function listarProveedores()
    {
        $proveedores = DB::select("CALL SP_LISTAR_PROVEEDORES()");

        return response()->json($proveedores);
    }

    public function listarProductos()
    {
        $productos = DB::select("CALL SP_LISTAR_PRODUCTOS()");

        return response()->json($productos);
    }

    public function guardarPedido(Request $request)
    {
        $request->validate([
            'fecha'                      => ['required', 'date'],
            'prioridad'                  => ['required'],
            'centro_costo_id'            => ['nullable', 'integer', 'exists:centros_costo,id'],
            'proveedor_id'               => ['nullable', 'integer', 'exists:proveedores,id'],
            'detalle'                    => ['required', 'array', 'min:1'],
            'detalle.*.producto_id'      => ['required', 'integer', 'exists:productos,id'],
            'detalle.*.cantidad'         => ['required', 'numeric', 'min:0.01'],
            'detalle.*.precio'           => ['nullable', 'numeric', 'min:0'],
            'adjuntos'                   => ['nullable', 'array'],
            'adjuntos.*'                 => ['file', 'max:3072', 'mimes:pdf,jpg,jpeg,png,webp,xlsx,xls,doc,docx'],
        ]);

        DB::beginTransaction();

        try {

            $pedido = DB::select(
                "CALL SP_GUARDAR_PEDIDO_COMPRA(?,?,?,?,?,?)",
                [
                    $request->fecha,
                    strtoupper($request->prioridad),
                    Auth::id(),
                    $request->centro_costo_id ?: null,
                    $request->proveedor_id ?: null,
                    $request->descripcion
                ]
            );

            $pedidoId = $pedido[0]->pedido_id;

            foreach ($request->detalle as $item) {

                DB::statement(
                    "CALL SP_GUARDAR_DETALLE_PEDIDO_COMPRA(?,?,?,?,?)",
                    [
                        $pedidoId,
                        $item['producto_id'],
                        $item['cantidad'],
                        $item['precio'] !== '' ? $item['precio'] : null,
                        $item['descripcion_item']
                    ]
                );
            }

            if ($request->hasFile('adjuntos')) {
                foreach ($request->file('adjuntos') as $archivo) {
                    $path = $archivo->store('pedidos_compras/' . $pedidoId, 'public');

                    DB::statement(
                        "CALL SP_GUARDAR_ADJUNTO_PEDIDO_COMPRA(?,?,?,?)",
                        [$pedidoId, $path, $archivo->getClientOriginalName(), 'PRESUPUESTO']
                    );
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function listarPedidosCompras(Request $request)
    {
        $data = DB::select(
            "CALL SP_LISTAR_PEDIDOS_COMPRAS(?,?,?,?,?,?)",
            [
                Auth::id(),
                $request->prioridades ?: null,
                $request->estados ?: null,
                $request->autorizaciones ?: null,
                $request->desde ?: null,
                $request->hasta ?: null,
            ]
        );

        return response()->json([
            'data' => $data
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $cabecera = [

            'NUMERO',
            'FECHA',
            'PROVEEDOR',
            'COMPROBANTE',
            'CONDICIONPAGO',
            'SUCURSAL',
            'DESCRIPCION',
            'PRODUCTO',
            'DESCRIPCIONITEM',
            'FECHAPROXPASO',
            'CANTIDAD',
            'PRECIO',
            'WORKFLOW',
            'PROVINCIA_ORIGEN_TRANSACCION',
            'PROVINCIA_DESTINO_TRANSACCION',
            'PROVINCIA_ORIGEN_PRODUCTO',
            'PROVINCIA_DESTINO_PRODUCTO',
            'DIMENSION',
            'DIMENSIONVALOR',
            'DIMENSION2',
            'DIMENSIONVALOR2',
            'EQUIPO_SOLICITANTE'

        ];

        $sheet->fromArray($cabecera);

        $fila = 2;

        foreach ($request->ids as $pedidoId) {

            $detalle = DB::select(
                "CALL SP_EXPORTAR_PEDIDOS_COMPRAS(?)",
                [$pedidoId]
            );

            foreach ($detalle as $item) {

                $sheet->setCellValue('A' . $fila, $item->NUMERO);
                $sheet->setCellValue('B' . $fila, $item->FECHA);
                $sheet->setCellValue('C' . $fila, $item->PROVEEDOR);
                $sheet->setCellValue('D' . $fila, $item->COMPROBANTE);
                $sheet->setCellValue('E' . $fila, $item->CONDICIONPAGO);
                $sheet->setCellValue('F' . $fila, $item->SUCURSAL);
                $sheet->setCellValue('G' . $fila, $item->DESCRIPCION);
                $sheet->setCellValue('H' . $fila, $item->PRODUCTO);
                $sheet->setCellValue('I' . $fila, $item->DESCRIPCIONITEM);
                $sheet->setCellValue('J' . $fila, $item->FECHAPROXPASO);
                $sheet->setCellValue('K' . $fila, $item->CANTIDAD);
                $sheet->setCellValue('L' . $fila, $item->PRECIO);
                $sheet->setCellValue('M' . $fila, $item->WORKFLOW);
                $sheet->setCellValue('N' . $fila, $item->PROVINCIA_ORIGEN_TRANSACCION);
                $sheet->setCellValue('O' . $fila, $item->PROVINCIA_DESTINO_TRANSACCION);
                $sheet->setCellValue('P' . $fila, $item->PROVINCIA_ORIGEN_PRODUCTO);
                $sheet->setCellValue('Q' . $fila, $item->PROVINCIA_DESTINO_PRODUCTO);
                $sheet->setCellValue('R' . $fila, $item->DIMENSION);
                $sheet->setCellValue('S' . $fila, $item->DIMENSIONVALOR);
                $sheet->setCellValue('T' . $fila, $item->DIMENSION2);
                $sheet->setCellValue('U' . $fila, $item->DIMENSIONVALOR2);
                $sheet->setCellValue('V' . $fila, $item->EQUIPO_SOLICITANTE);

                $fila++;
            }

            DB::statement("CALL SP_FINALIZAR_PEDIDO(?)", [$pedidoId]);
            
        }

        foreach (range('A', 'V') as $col) {

            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $nombre = 'PedidosCompras_' . date('YmdHis') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {

            $writer->save('php://output');
        }, $nombre);
    }

    public function aprobarPedido(Request $request)
    {
        DB::statement(
            "CALL SP_APROBAR_PEDIDO_COMPRA(?)",
            [
                $request->id
            ]
        );

        return response()->json([
            "success" => true
        ]);
    }

    public function rechazarPedido(Request $request)
    {
        DB::statement(
            "CALL SP_RECHAZAR_PEDIDO_COMPRA(?)",
            [
                $request->id
            ]
        );

        return response()->json([
            "success" => true
        ]);
    }

    public function verDetallePedido($id)
    {
        $cabecera = DB::select(
            "CALL SP_VER_CABECERA_PEDIDO_COMPRA(?)",
            [$id]
        );

        $detalle = DB::select(
            "CALL SP_VER_DETALLE_PEDIDO_COMPRA(?)",
            [$id]
        );

        return response()->json([
            'cabecera' => $cabecera[0],
            'detalle' => $detalle
        ]);
    }

    public function listarAdjuntos($id, $tipo = 'PRESUPUESTO')
    {
        $adjuntos = DB::select("CALL SP_LISTAR_ADJUNTOS_PEDIDO_COMPRA(?,?)", [$id, $tipo]);

        $adjuntos = collect($adjuntos)->map(function ($a) {
            return [
                'id'       => $a->id,
                'nombre'   => $a->nombre_original,
                'url'      => Storage::disk('public')->url($a->archivo),
                'esImagen' => preg_match('/\.(jpg|jpeg|png|webp)$/i', $a->archivo) === 1,
            ];
        });

        return response()->json(['data' => $adjuntos]);
    }

    public function subirOrdenCompra(Request $request, $id)
    {
        if (!in_array(Auth::id(), [1, 2, 5, 6])) {
            return response()->json(['success' => false, 'mensaje' => 'No tenés permisos para esta acción.'], 403);
        }

        $pedido = DB::table('pedidos_compras')->where('id', $id)->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'mensaje' => 'Pedido no encontrado.'], 404);
        }

        if ($pedido->autorizacion !== 'APROBADA') {
            return response()->json(['success' => false, 'mensaje' => 'Solo se puede cargar la orden de compra en pedidos ya autorizados.'], 422);
        }

        $request->validate([
            'archivo' => ['required', 'file', 'max:3072', 'mimes:pdf,jpg,jpeg,png,webp,xlsx,xls,doc,docx'],
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->store('pedidos_compras/' . $id, 'public');

        DB::statement(
            "CALL SP_GUARDAR_ADJUNTO_PEDIDO_COMPRA(?,?,?,?)",
            [$id, $path, $archivo->getClientOriginalName(), 'ORDEN_COMPRA']
        );

        return response()->json(['success' => true]);
    }

    public function crearProducto(Request $request)
    {
        $request->validate([
            'nombreProducto' => ['required', 'string', 'max:255'],
            'codigoProducto' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::statement(
                "CALL SP_NUEVO_PRODUCTO(?,?)",
                [$request->nombreProducto, $request->codigoProducto]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function editarProducto(Request $request)
    {
        $request->validate([
            'idProducto'      => ['required', 'integer', 'exists:productos,id'],
            'nombreProducto'  => ['required', 'string', 'max:255'],
            'codigoProducto'  => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::statement(
                "CALL SP_EDITAR_PRODUCTO(?,?,?)",
                [$request->idProducto, $request->nombreProducto, $request->codigoProducto]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function crearProveedor(Request $request)
    {
        $request->validate([
            'nombreProveedor' => ['required', 'string', 'max:255'],
            'razonSocial'     => ['nullable', 'string', 'max:255'],
            'cuitProveedor'   => ['nullable', 'string', 'max:20'],
            'codigoProveedor' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::statement(
                "CALL SP_NUEVO_PROVEEDOR(?,?,?,?)",
                [
                    $request->nombreProveedor,
                    $request->razonSocial,
                    $request->cuitProveedor,
                    $request->codigoProveedor,
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function editarProveedor(Request $request)
    {
        $request->validate([
            'idProveedor'      => ['required', 'integer', 'exists:proveedores,id'],
            'nombreProveedor'  => ['required', 'string', 'max:255'],
            'razonSocial'      => ['nullable', 'string', 'max:255'],
            'cuitProveedor'    => ['nullable', 'string', 'max:20'],
            'codigoProveedor'  => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::statement(
                "CALL SP_EDITAR_PROVEEDOR(?,?,?,?,?)",
                [
                    $request->idProveedor,
                    $request->nombreProveedor,
                    $request->razonSocial,
                    $request->cuitProveedor,
                    $request->codigoProveedor,
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
