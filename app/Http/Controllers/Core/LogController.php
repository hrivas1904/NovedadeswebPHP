<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    public function logTransactView()
    {
        return view('core.logTransac');
    }

    public function listarLogTransact(Request $request){
        try {

            $fechaDesde=$request->fechaDesde;
            $fechaHasta=$request->fechaHasta;

            $logTransact = DB::select(
                'CALL SP_LISTAR_LOG_TRANSACT(?,?)',
                [$fechaDesde, $fechaHasta]
            );

            return response()->json($logTransact);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error al obtener log de transacciones'
            ], 500);
        }
    }
}