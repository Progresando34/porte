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

        // Asegurarse de cargar el perfil
        $user->load('profile');

        // Redirige según el perfil
        if ($user->profile && $user->profile->name === 'sanidad') {
            return redirect('/consultaArmas');
        } elseif ($user->profile && $user->profile->name === 'admin') {
            return redirect('/admin/dashboard');
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
