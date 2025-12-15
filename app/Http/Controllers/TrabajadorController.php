<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\Prefijo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ¡IMPORTANTE!

class TrabajadorController extends Controller
{
    public function index()
    {
        $trabajadores = Trabajador::with('prefijos')->paginate(10);
        return view('trabajadores.index', compact('trabajadores'));
    }

    public function create()
    {
        $prefijos = Prefijo::where('activo', true)->get();
        return view('trabajadores.create', compact('prefijos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'cedula'   => 'required|string|unique:trabajadores,cedula|max:20',
            'usuario'  => 'required|string|unique:trabajadores,usuario|max:50',
            'password' => 'required|string|min:6|confirmed',
            'activo'   => 'boolean',
            'prefijos' => 'nullable|array',
            'prefijos.*' => 'exists:prefijos,id',
        ]);

        $trabajador = Trabajador::create([
            'nombre'   => $request->nombre,
            'cedula'   => $request->cedula,
            'usuario'  => $request->usuario,
            'password' => Hash::make($request->password), // ¡AQUÍ ESTÁ EL CAMBIO!
            'activo'   => $request->has('activo'),
        ]);

        $trabajador->prefijos()->sync($request->input('prefijos', []));

        return redirect()->route('trabajadores.index')
            ->with('success', 'Trabajador creado correctamente.');
    }

    public function show($id)
    {
        $trabajador = Trabajador::with('prefijos')->findOrFail($id);
        return view('trabajadores.show', compact('trabajador'));
    }

    public function edit($id)
    {
        $trabajador = Trabajador::withTrashed()->with('prefijos')->findOrFail($id);
        $prefijos = Prefijo::where('activo', true)->get();

        return view('trabajadores.edit', compact('trabajador', 'prefijos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'cedula'   => 'required|string|unique:trabajadores,cedula,' . $id,
            'usuario'  => 'required|string|unique:trabajadores,usuario,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'activo'   => 'boolean',
            'prefijos' => 'nullable|array',
            'prefijos.*' => 'exists:prefijos,id',
        ]);

        $trabajador = Trabajador::withTrashed()->findOrFail($id);

        $data = [
            'nombre'  => $request->nombre,
            'cedula'  => $request->cedula,
            'usuario' => $request->usuario,
            'activo'  => $request->has('activo'),
        ];

        // Solo actualizar la contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password); // ¡AQUÍ TAMBIÉN!
        }

        $trabajador->update($data);

        $trabajador->prefijos()->sync($request->input('prefijos', []));

        return redirect()->route('trabajadores.index')
            ->with('success', 'Trabajador actualizado correctamente.');
    }

    public function destroy($id)
    {
        $trabajador = Trabajador::withTrashed()->findOrFail($id);

        $trabajador->prefijos()->detach(); // limpiar pivote
        $trabajador->forceDelete();        // borrar real

        return redirect()->route('trabajadores.index')
            ->with('success', 'Trabajador eliminado definitivamente.');
    }
}