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

    // nombre original
    $nombreArchivo = $file->getClientOriginalName();

    // 📁 carpeta por cédula
    $carpeta = 'RESULTADOS/' . $request->cedula;

    // 📌 guardar archivo en storage/app/public
    $ruta = $file->storeAs(
        $carpeta,
        $nombreArchivo,
        'public'
    );

    // guardar en DB
    RayosX::create([
        'nombre' => $request->nombre,
        'cedula' => $request->cedula,
        'fecha_rx' => $request->fecha_rx,
        'nombre_archivo' => $nombreArchivo,
        'ruta' => $ruta // 👈 IMPORTANTE: ya no FTP
    ]);

    return back()->with('success', 'Archivo guardado correctamente');
}

    // 🔹 👇 ESTE ES EL NUEVO (LISTADO)
    public function index()
    {
        $registros = RayosX::latest()->get();
        return view('rayosx.index', compact('registros'));
    }

    public function destroy($id)
{
    try {
        // Buscar el registro
        $registro = RayosX::findOrFail($id);
        
        // Guardar datos para el mensaje
        $nombre = $registro->nombre;
        $cedula = $registro->cedula;
        
        // Eliminar el archivo físico si existe
        if ($registro->ruta && Storage::disk('public')->exists($registro->ruta)) {
            Storage::disk('public')->delete($registro->ruta);
        }
        
        // Eliminar el registro de la base de datos
        $registro->delete();
        
        return redirect()->route('rayosx.index')
            ->with('success', "Registro de {$nombre} (Cédula: {$cedula}) eliminado correctamente");
            
    } catch (\Exception $e) {
        return redirect()->route('rayosx.index')
            ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
    }
}

}