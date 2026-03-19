<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RayosX;

class RayosXController extends Controller
{
    // 🔹 Mostrar formulario
    public function create()
    {
        return view('rayosx.create');
    }

    // 🔹 Guardar registro
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'cedula' => 'required',
            'fecha_rx' => 'required|date',
            'nombre_archivo' => 'required',
            'ruta' => 'required'
        ]);

        RayosX::create([
            'nombre' => $request->nombre,
            'cedula' => $request->cedula,
            'fecha_rx' => $request->fecha_rx,
            'nombre_archivo' => $request->nombre_archivo, // 🔒 NO modificar
            'ruta' => $request->ruta
        ]);

        return redirect()->back()->with('success', 'Registro guardado correctamente');
    }

    // 🔹 👇 ESTE ES EL NUEVO (LISTADO)
    public function index()
    {
        $registros = RayosX::latest()->get();
        return view('rayosx.index', compact('registros'));
    }
}