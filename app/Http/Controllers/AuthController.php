<?php

// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // Asegúrate de tener esta vista
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Si el usuario tiene relación con perfil, la cargamos
            $user->load('profile');

            // 🔹 Redirección según el perfil del usuario
            if ($user->profile && $user->profile->name === 'sanidad') {
                return redirect('/consultaArmas');
            } elseif ($user->profile && $user->profile->name === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($user->profile && $user->profile->name === 'cliente') {
              return redirect()->route('certificados_e.index');

            } else {
                return redirect('/');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
