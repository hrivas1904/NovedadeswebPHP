<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestringirIndexAContable
{
    public function handle(Request $request, Closure $next): Response
    {
        // Impedir que acontable acceda al index general
        if ($request->user()?->username === 'acontable') {
            return redirect()->route('administracion.panelAdminView');
        }

        return $next($request);
    }
}
