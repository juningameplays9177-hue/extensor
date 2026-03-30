<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'administrador') {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta area.');
        }

        return $next($request);
    }
}
