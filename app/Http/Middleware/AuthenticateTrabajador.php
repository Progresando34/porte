<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticateTrabajador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('=== MIDDLEWARE AuthenticateTrabajador INICIADO ===');
        Log::info('URL: ' . $request->url());
        Log::info('Session ID: ' . session()->getId());
        
        // Verificar si es trabajador autenticado
        if (!session()->has('trabajador_autenticado')) {
            Log::warning('❌ Trabajador NO autenticado. Redirigiendo al login.');
            return redirect()->route('login.form')
                ->with('error', 'Debes iniciar sesión como trabajador para acceder.');
        }
        
        Log::info('✅ Trabajador autenticado: ' . session('trabajador_nombre'));
        return $next($request);
    }
}