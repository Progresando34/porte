<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistroInfotenenciaController extends Controller
{
    public function index()
    {
        return view('registro-infotenencia.index');
    }

    public function store(Request $request)
    {
        // Validación de datos
        $validated = $request->validate([
            'resultado_apto' => 'required|boolean',
            'direccion_ips' => 'required|string',
            'sede_ips' => 'required|string',
            'nombre' => 'required|string|max:255',
            'cedula' => 'required|string|max:255',
            'archivo_certificado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'fecha_expedicion' => 'required|date',
        ]);

        // Procesar archivo si se subió
        if ($request->hasFile('archivo_certificado')) {
            $file = $request->file('archivo_certificado');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('certificados', $filename, 'public');
            $validated['archivo_certificado'] = $path;
        }

        // Guardar usando el modelo Certificado
        $certificado = Certificado::create($validated);

        return redirect()->route('registro-infotenencia.index')
            ->with('success', 'Registro guardado exitosamente. ID: ' . $certificado->id);
    }

    public function listar()
    {
        $certificados = Certificado::orderBy('created_at', 'desc')->get();
        return view('registro-infotenencia.listar', compact('certificados'));
    }
}