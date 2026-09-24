<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BibliotecaNavigation
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // The same URL can return a full page, a library fragment, or JSON results.
        $response->setVary(['X-Biblioteca-Navigation', 'Accept'], false);
        if ($request->header('X-Biblioteca-Navigation') === '1') {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
