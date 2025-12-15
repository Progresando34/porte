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

        Log::info('=== INTENTO DE LOGIN ===');
        Log::info('Login: ' . $login);

        // PRIMERO intentar con Usuarios normales (User)
        $user = User::where('email', $login)
                    ->orWhere('name', $login)
                    ->first();

        if ($user) {
            Log::info('Usuario encontrado en tabla users: ' . $user->email);
            
            // Intentar autenticación
            if (Auth::attempt(['email' => $user->email, 'password' => $password])) {
                Log::info('✅ Usuario autenticado correctamente');
                $user = Auth::user();
                $user->load('profile');

                return $this->redirectByProfile($user);
            } else {
                Log::warning('❌ Contraseña incorrecta para usuario');
            }
        }

        // SEGUNDO intentar con Trabajadores
        Log::info('Buscando trabajador con usuario: ' . $login);
        $trabajador = Trabajador::where('usuario', $login)->first();
        
        if ($trabajador) {
            Log::info('Trabajador encontrado: ' . $trabajador->nombre);
            Log::info('Activo: ' . ($trabajador->activo ? 'SI' : 'NO'));
            Log::info('Password en DB (primeros 20 chars): ' . substr($trabajador->password, 0, 20));
            
            // Verificar si está activo
            if (!$trabajador->activo) {
                Log::warning('Trabajador inactivo');
                return back()->withErrors([
                    'login' => 'Su cuenta de trabajador está desactivada.',
                ])->withInput();
            }
            
            // Verificar contraseña CON MÁS DEPURACIÓN
            $passwordCheck = Hash::check($password, $trabajador->password);
            Log::info('Hash::check resultado: ' . ($passwordCheck ? 'TRUE' : 'FALSE'));
            
            if ($passwordCheck) {
                Log::info('✅ Contraseña válida para trabajador');
                
                // Autenticar manualmente al trabajador
                $request->session()->put('trabajador_id', $trabajador->id);
                $request->session()->put('trabajador_nombre', $trabajador->nombre);
                $request->session()->put('trabajador_cedula', $trabajador->cedula);
                $request->session()->put('trabajador_usuario', $trabajador->usuario);
                $request->session()->put('trabajador_autenticado', true);
                
                $request->session()->regenerate();
                
                Log::info('✅ Trabajador autenticado: ' . $trabajador->nombre);
                Log::info('Redirigiendo a: certificados_e.index');
                
                return redirect()->route('certificados_e.index')
                    ->with('success', '¡Bienvenido ' . $trabajador->nombre . '!');
            } else {
                Log::warning('❌ Hash::check FALLÓ');
                Log::info('Password ingresada: ' . $password);
                Log::info('Password en DB: ' . $trabajador->password);
                
                // INTENTO ALTERNATIVO: ¿Está la contraseña en texto plano?
                if ($password === $trabajador->password) {
                    Log::info('⚠️ La contraseña está en texto plano!');
                    // Aquí podrías actualizar el hash si quieres
                    $trabajador->password = Hash::make($password);
                    $trabajador->save();
                    Log::info('Contraseña actualizada con hash');
                    
                    // Reintentar login
                    $request->session()->put('trabajador_id', $trabajador->id);
                    $request->session()->put('trabajador_nombre', $trabajador->nombre);
                    $request->session()->put('trabajador_cedula', $trabajador->cedula);
                    $request->session()->put('trabajador_usuario', $trabajador->usuario);
                    $request->session()->put('trabajador_autenticado', true);
                    
                    return redirect()->route('certificados_e.index')
                        ->with('success', '¡Bienvenido ' . $trabajador->nombre . '!');
                }
            }
        } else {
            Log::warning('❌ Trabajador NO encontrado');
        }

        Log::warning('❌ Login fallido completamente');
        return back()->withErrors([
            'login' => 'Credenciales incorrectas.',
        ])->withInput();
    }

    private function redirectByProfile($user)
    {
        if (!$user->profile) {
            return redirect()->route('certificados_e.index');
        }
        
        if ($user->profile->name === 'sanidad') {
            return redirect('/consultaArmas');
        } elseif ($user->profile->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('certificados_e.index');
        }
    }

    public function logout(Request $request)
    {
        if ($request->session()->has('trabajador_autenticado')) {
            $nombre = session('trabajador_nombre');
            Log::info('Trabajador ' . $nombre . ' cerró sesión');
            
            $request->session()->forget([
                'trabajador_id',
                'trabajador_nombre',
                'trabajador_cedula',
                'trabajador_usuario',
                'trabajador_autenticado'
            ]);
        } else {
            Log::info('Usuario normal cerró sesión');
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada correctamente.');
    }
}