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

    // 🔥 STORE CORREGIDO
    public function store(Request $request)
    {
        // Validación de datos - ACEPTA 0, 1 y 2
        $validated = $request->validate([
            'resultado_apto' => 'required|in:0,1,2', // 🔥 CAMBIADO
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

    // 🔥 LISTAR CORREGIDO (orden ascendente)
    public function listar()
    {
        $certificados = Certificado::orderBy('id', 'asc')->get(); // 🔥 CAMBIADO
        return view('registro-infotenencia.listar', compact('certificados'));
    }

    public function edit($id)
    {
        $certificado = Certificado::findOrFail($id);
        return view('registro-infotenencia.edit', compact('certificado'));
    }

    // 🔥 UPDATE CORREGIDO
    public function update(Request $request, $id)
    {
        $certificado = Certificado::findOrFail($id);

        // Validación de datos - ACEPTA 0, 1 y 2
        $validated = $request->validate([
            'resultado_apto' => 'required|in:0,1,2', // 🔥 CAMBIADO
            'direccion_ips' => 'required|string',
            'sede_ips' => 'required|string',
            'nombre' => 'required|string|max:255',
            'cedula' => 'required|string|max:255',
            'archivo_certificado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'fecha_expedicion' => 'required|date',
        ]);

        // Procesar archivo si se subió uno nuevo
        if ($request->hasFile('archivo_certificado')) {
            // Eliminar archivo anterior si existe
            if ($certificado->archivo_certificado && Storage::disk('public')->exists($certificado->archivo_certificado)) {
                Storage::disk('public')->delete($certificado->archivo_certificado);
            }

            $file = $request->file('archivo_certificado');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('certificados', $filename, 'public');
            $validated['archivo_certificado'] = $path;
        }

        // Actualizar el registro
        $certificado->update($validated);

        return redirect()->route('registro-infotenencia.listar')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $certificado = Certificado::findOrFail($id);

        // Eliminar archivo si existe
        if ($certificado->archivo_certificado && Storage::disk('public')->exists($certificado->archivo_certificado)) {
            Storage::disk('public')->delete($certificado->archivo_certificado);
        }

        $certificado->delete();

        return redirect()->route('registro-infotenencia.listar')
            ->with('success', 'Registro eliminado exitosamente.');
    }
}