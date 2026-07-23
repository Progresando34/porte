<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Prefijo; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Log; // ✅ Importante agregar esto

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
    try {
        // Mostrar datos
        echo "📝 PASO 1: Datos recibidos<br>";
        echo "<pre>";
        print_r($request->all());
        echo "</pre><br>";
        
        // PASO 2: Intentar crear usuario
        echo "📝 PASO 2: Creando usuario...<br>";
        
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->profile_id = $request->profile_id;
        $user->save();
        
        echo "✅ USUARIO CREADO!<br>";
        echo "ID: " . $user->id . "<br>";
        echo "Nombre: " . $user->name . "<br>";
        echo "Email: " . $user->email . "<br>";
        echo "Profile ID: " . $user->profile_id . "<br>";
        
        // PASO 3: Verificar que existe en BD
        echo "<br>📝 PASO 3: Verificando en BD...<br>";
        $verificar = User::find($user->id);
        if ($verificar) {
            echo "✅ Usuario encontrado en BD<br>";
        } else {
            echo "❌ Usuario NO encontrado en BD<br>";
        }
        
        exit();
        
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "<br>";
        echo "Archivo: " . $e->getFile() . " (Línea " . $e->getLine() . ")<br>";
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
        exit();
    }
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