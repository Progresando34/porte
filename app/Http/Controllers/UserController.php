<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
 public function create()
{
    $perfiles = Profile::all();
    return view('usuarios.create', compact('perfiles'));
}

    //seguridadeagle
    public function index()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:6|confirmed',
            'profile_id' => 'required|exists:profiles,id',
               'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 👈 validación aquí
        ]);


                // 👇 Inicializamos la variable
        $avatarPath = null;

        // Si subieron un archivo, lo guardamos en storage/app/public/avatars
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }


        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'profile_id' => $request->profile_id,
             'avatar'     => $avatarPath, // 👈 aquí guardamos la ruta
        ]);

        return redirect()->route('usuarios.create')->with('success', 'Usuario registrado correctamente.');
    }
}
