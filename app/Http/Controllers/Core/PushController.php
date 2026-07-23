<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->all();

        DB::table('push_subscriptions')->updateOrInsert(
            [
                'endpoint' => $data['endpoint']
            ],
            [
                'usuario_id' => Auth::id(),
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }
}