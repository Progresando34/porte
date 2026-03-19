<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

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
        'archivo' => 'required|file'
    ]);

    $file = $request->file('archivo');

    // 🔒 nombre ORIGINAL (NO se modifica)
    $nombreArchivo = $file->getClientOriginalName();

    // ruta en FTP
    $rutaFTP = 'RESULTADOS/' . $nombreArchivo;

    // subir al FTP
    Storage::disk('ftp')->put($rutaFTP, fopen($file, 'r+'));

    // guardar en DB
    RayosX::create([
        'nombre' => $request->nombre,
        'cedula' => $request->cedula,
        'fecha_rx' => $request->fecha_rx,
        'nombre_archivo' => $nombreArchivo,
        'ruta' => $rutaFTP
    ]);

    return back()->with('success', 'Archivo subido y registrado correctamente');
}

    // 🔹 👇 ESTE ES EL NUEVO (LISTADO)
    public function index()
    {
        $registros = RayosX::latest()->get();
        return view('rayosx.index', compact('registros'));
    }
}