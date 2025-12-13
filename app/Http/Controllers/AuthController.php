<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Trabajador;
use Illuminate\Support\Facades\Hash;

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

        // PRIMERO intentar con Usuarios normales (User)
        $user = null;
        
        // 1. Buscar por email
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $login)->first();
        }
        
        // 2. Buscar por nombre de usuario (campo 'name')
        if (!$user) {
            $user = User::where('name', $login)->first();
        }

        // Si encontramos usuario normal, intentar autenticar
        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password])) {
            $user = Auth::user();
            $user->load('profile');

            // Redirección según perfil de usuario
            return $this->redirectByProfile($user);
        }

        // SEGUNDO intentar con Trabajadores (SIN USAR GUARD)
        $trabajador = Trabajador::where('usuario', $login)->first();
        
       // En el método login, cuando autenticas al trabajador:
if ($trabajador && Hash::check($password, $trabajador->password)) {
    // Autenticar manualmente al trabajador
    $request->session()->put('trabajador_id', $trabajador->id);
    $request->session()->put('trabajador_nombre', $trabajador->nombre);
    $request->session()->put('trabajador_cedula', $trabajador->cedula); // <-- IMPORTANTE
    $request->session()->put('trabajador_autenticado', true);
    
    // 🔹 TRABAJADORES VAN A CERTIFICADOS_E.INDEX
    return redirect()->route('certificados_e.index');
}

        return back()->withErrors([
            'login' => 'Credenciales incorrectas.',
        ])->withInput();
    }

    private function redirectByProfile($user)
    {
        if ($user->profile && $user->profile->name === 'sanidad') {
            return redirect('/consultaArmas');
        } elseif ($user->profile && $user->profile->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->profile && $user->profile->name === 'cliente') {
            return redirect()->route('certificados_e.index');
        } elseif ($user->profile && $user->profile->name === 'empleado') {
            return redirect()->route('certificados_e.index'); // Empleados también van a certificados
        } else {
            return redirect()->route('certificados_e.index'); // Por defecto a certificados
        }
    }

    public function logout(Request $request)
    {
        // Verificar si es trabajador (sesión manual)
        if ($request->session()->has('trabajador_autenticado')) {
            // Limpiar sesión de trabajador
            $request->session()->forget([
                'trabajador_id',
                'trabajador_nombre',
                'trabajador_autenticado'
            ]);
        }
        
        // Cerrar sesión normal (usuarios)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}