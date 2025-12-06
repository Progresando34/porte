<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Prefijo; // ✅ Agregar esta línea
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // ✅ Agregar esta línea

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['profile', 'prefijos'])->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $perfiles = Profile::all();
        $prefijos = Prefijo::where('activo', true)->get(); // ✅ Agregar esta línea
        
        return view('usuarios.create', compact('perfiles', 'prefijos')); // ✅ Agregar $prefijos
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'profile_id' => 'required|exists:profiles,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prefijos' => 'nullable|array', // ✅ Agregar esta validación
            'prefijos.*' => 'exists:prefijos,id' // ✅ Agregar esta validación
        ]);

        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // ✅ Crear usuario y asignar prefijos
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_id' => $request->profile_id,
            'avatar' => $avatarPath,
        ]);

        // ✅ Asignar prefijos si existen
        if ($request->has('prefijos')) {
            $user->prefijos()->sync($request->prefijos);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }
    
    // ✅ Agregar método edit
    public function edit($id)
    {
        $user = User::with('prefijos')->findOrFail($id);
        $perfiles = Profile::all();
        $prefijos = Prefijo::where('activo', true)->get();
        
        return view('usuarios.edit', compact('user', 'perfiles', 'prefijos'));
    }
    
    // ✅ Agregar método update
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'profile_id' => 'required|exists:profiles,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prefijos' => 'nullable|array',
            'prefijos.*' => 'exists:prefijos,id'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'profile_id' => $request->profile_id,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();
        }

        $user->prefijos()->sync($request->prefijos ?? []);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }
    
    // ✅ Agregar método show
    public function show($id)
    {
        $user = User::with(['profile', 'prefijos'])->findOrFail($id);
        return view('usuarios.show', compact('user'));
    }
    
    // ✅ Agregar método destroy
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}