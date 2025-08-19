<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificadoController extends Controller
{
    public function create()
    {
        return view('certificados.create'); // Asegúrate de tener esta vista
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'cedula' => 'required|string|max:100',
            'tipo_certificado' => 'required|string',
            'archivo_certificado' => 'required|file|mimes:pdf,jpg,jpeg,png,mp4,mov,avi|max:10240',
            'fecha_expedicion' => 'required|date',
        ]);

        // Guarda el archivo en /storage/app/public/certificados
        $archivo = $request->file('archivo_certificado')->store('certificados', 'public');

        Certificado::create([
            'nombre' => $request->nombre,
            'cedula' => $request->cedula,
            'tipo_certificado' => $request->tipo_certificado,
            'archivo_certificado' => $archivo,
            'fecha_expedicion' => $request->fecha_expedicion,
        ]);

        return redirect()->back()->with('success', 'Certificado cargado exitosamente.');
    }

    // NUEVO MÉTODO PARA DESCARGAR
    public function descargar($filename)
    {
        $path = storage_path('app/public/certificados/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'El archivo no existe');
        }

        return response()->download($path);
    }
}
