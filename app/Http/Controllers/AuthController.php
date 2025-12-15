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
    Log::info('Password: ' . str_repeat('*', strlen($password)));

    // PRIMERO intentar con Usuarios normales (User)
    $user = User::where('email', $login)
                ->orWhere('name', $login)
                ->first();

    if ($user) {
        Log::info('Usuario encontrado en tabla users: ' . $user->email);
        
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
        Log::info('ID: ' . $trabajador->id);
        Log::info('Activo: ' . ($trabajador->activo ? 'SI' : 'NO'));
        Log::info('Password en DB: ' . $trabajador->password);
        
        if (!$trabajador->activo) {
            Log::warning('Trabajador inactivo');
            return back()->withErrors([
                'login' => 'Su cuenta de trabajador está desactivada.',
            ])->withInput();
        }
        
        // Verificar contraseña
        $passwordCheck = Hash::check($password, $trabajador->password);
        Log::info('Hash::check resultado: ' . ($passwordCheck ? 'TRUE' : 'FALSE'));
        
        if ($passwordCheck) {
            Log::info('✅ Contraseña válida para trabajador');
            
            // Guardar en sesión
            session([
                'trabajador_id' => $trabajador->id,
                'trabajador_nombre' => $trabajador->nombre,
                'trabajador_cedula' => $trabajador->cedula,
                'trabajador_usuario' => $trabajador->usuario,
                'trabajador_autenticado' => true
            ]);
            
            Log::info('✅ Sesión establecida: ' . json_encode(session()->all()));
            Log::info('Redirigiendo a: certificados_e.index');
            
            return redirect()->route('certificados_e.index')
                ->with('success', '¡Bienvenido ' . $trabajador->nombre . '!');
        } else {
            Log::warning('❌ Hash::check FALLÓ');
            
            // Verificar si es texto plano
            if ($password === $trabajador->password) {
                Log::info('⚠️ La contraseña está en texto plano! Actualizando...');
                $trabajador->password = Hash::make($password);
                $trabajador->save();
                
                // Reintentar login
                session([
                    'trabajador_id' => $trabajador->id,
                    'trabajador_nombre' => $trabajador->nombre,
                    'trabajador_cedula' => $trabajador->cedula,
                    'trabajador_usuario' => $trabajador->usuario,
                    'trabajador_autenticado' => true
                ]);
                
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