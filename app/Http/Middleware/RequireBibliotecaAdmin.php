<?php

namespace App\Http\Middleware;

use App\Services\Biblioteca\Library;
use Closure;
use Illuminate\Http\Request;

class RequireBibliotecaAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Library::manages($request->user()), 403, 'Esta sección de la biblioteca es exclusiva de administradores.');

        return $next($request);
    }
}
