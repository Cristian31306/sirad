<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Permitir acceso a las rutas necesarias para cambiar la contraseña
            if (!$request->routeIs('password.force.change') && !$request->routeIs('password.force.update') && !$request->routeIs('logout')) {
                return redirect()->route('password.force.change');
            }
        }

        return $next($request);
    }
}
