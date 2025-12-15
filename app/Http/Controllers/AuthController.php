<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Trabajador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        Log::info('=== NUEVO INTENTO DE LOGIN ===');
        Log::info('Login: ' . $login);

        // 1. Intentar autenticación como usuario normal
        $credentials = ['email' => $login, 'password' => $password];
        
        // También probar con name si no es email
        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('name', $login)->first();
            if ($user) {
                $credentials = ['email' => $user->email, 'password' => $password];
            }
        }

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            Log::info('✅ Usuario normal autenticado');
            $user = Auth::guard('web')->user();
            return $this->redirectByProfile($user);
        }

        // 2. Intentar autenticación como trabajador
        Log::info('🔍 Intentando autenticar como trabajador');
        
        $trabajador = Trabajador::where('usuario', $login)->first();
        
        if ($trabajador) {
            Log::info('📋 Trabajador encontrado: ' . $trabajador->usuario);
            
            if (!$trabajador->activo) {
                Log::warning('❌ Trabajador INACTIVO');
                return back()->withErrors([
                    'login' => 'Su cuenta está desactivada. Contacte al administrador.',
                ])->withInput();
            }
            
            // Verificar contraseña
            if (Hash::check($password, $trabajador->password)) {
                Log::info('✅ Contraseña válida para trabajador');
                
                // Autenticar usando el guard 'trabajador'
                Auth::guard('trabajador')->login($trabajador);
                
                Log::info('🎯 Redirigiendo trabajador a: certificados_e.index');
                
                return redirect()->route('certificados_e.index')
                    ->with('success', '¡Bienvenido ' . $trabajador->nombre . '!');
                    
            } else {
                Log::warning('❌ Contraseña incorrecta para trabajador');
            }
        }

        Log::error('❌ LOGIN COMPLETAMENTE FALLIDO');
        return back()->withErrors([
            'login' => 'Credenciales incorrectas. Verifique usuario y contraseña.',
        ])->withInput();
    }

    private function redirectByProfile($user)
    {
        $user->load('profile');
        
        if ($user->profile && $user->profile->name === 'sanidad') {
            return redirect('/consultaArmas');
        } elseif ($user->profile && $user->profile->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('certificados_e.index');
        }
    }

    public function logout(Request $request)
    {
        $currentGuard = null;
        $userName = null;
        
        // Determinar qué guard está activo
        if (Auth::guard('web')->check()) {
            $currentGuard = 'web';
            $userName = Auth::guard('web')->user()->name;
        } elseif (Auth::guard('trabajador')->check()) {
            $currentGuard = 'trabajador';
            $userName = Auth::guard('trabajador')->user()->nombre;
        }
        
        if ($currentGuard) {
            Log::info('👋 ' . ucfirst($currentGuard) . ' cerró sesión: ' . $userName);
            Auth::guard($currentGuard)->logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada correctamente.');
    }
}