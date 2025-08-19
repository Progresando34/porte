<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    /**
     * Mostrar los archivos existentes.
     */
    public function index()
    {
        // Obtener archivos del directorio storage/app/public/archivos
        $archivos = Storage::files('public/archivos');
        return view('archivos.index', compact('archivos'));
    }

    /**
     * Guardar un nuevo archivo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240', // 10MB máximo
        ]);

        // Guardar en storage/app/public/archivos
        $request->file('archivo')->store('public/archivos');

        return redirect()->route('archivos.index')->with('success', 'Archivo subido correctamente');
    }

    /**
     * Eliminar un archivo.
     */
    public function destroy($archivo)
    {
        Storage::delete('public/archivos/' . $archivo);

        return back()->with('success', 'Archivo eliminado correctamente');
    }
}
