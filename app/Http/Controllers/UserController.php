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
        Log::info('=== INICIO REGISTRO DE USUARIO ===');
        Log::info('Datos recibidos:', $request->all());
        
        try {
            // Validación
            Log::info('Validando datos...');
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'profile_id' => 'required|exists:profiles,id',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'prefijos' => 'nullable|array',
                'prefijos.*' => 'exists:prefijos,id'
            ]);
            Log::info('✅ Validación pasada correctamente');

            // Procesar avatar
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                Log::info('Procesando archivo de avatar...');
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                Log::info('Avatar guardado en: ' . $avatarPath);
            } else {
                Log::info('No se recibió archivo de avatar');
            }

            // Crear usuario
            Log::info('Creando usuario en BD...', [
                'name' => $request->name,
                'email' => $request->email,
                'profile_id' => $request->profile_id
            ]);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_id' => $request->profile_id,
                'avatar' => $avatarPath,
            ]);
            
            Log::info(' Usuario creado con ID: ' . $user->id);

            // Asignar prefijos
            if ($request->has('prefijos')) {
                $prefijosIds = $request->prefijos;
                Log::info('Asignando prefijos al usuario: ' . json_encode($prefijosIds));
                $user->prefijos()->sync($prefijosIds);
                Log::info('✅ Prefijos asignados correctamente');
            } else {
                Log::info('No se asignaron prefijos');
            }

            Log::info('=== REGISTRO EXITOSO ===');
            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario registrado correctamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Error de base de datos: ' . $e->getMessage());
            Log::error('SQL: ' . $e->getSql());
            Log::error('Bindings: ' . json_encode($e->getBindings()));
            return back()->with('error', 'Error en la base de datos: ' . $e->getMessage())->withInput();
            
        } catch (\Exception $e) {
            Log::error('❌ Error general al registrar usuario: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error inesperado: ' . $e->getMessage())->withInput();
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