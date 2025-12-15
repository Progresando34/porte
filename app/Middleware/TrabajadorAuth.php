<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrabajadorAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('trabajador_autenticado')) {
            Log::warning('Acceso no autorizado a ruta protegida para trabajadores');
            return redirect()->route('login.form')
                ->with('error', 'Debes iniciar sesión como trabajador para acceder.');
        }
        
        Log::info('Trabajador autenticado accediendo: ' . session('trabajador_nombre'));
        return $next($request);
    }
}