<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardPublicoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        if ($token !== env('DASHBOARD_CALIDAD_TOKEN')) {
            abort(403);
        }

        return $next($request);
    }
}
