<?php
// app/Http/Controllers/SoloVistaController.php

namespace App\Http\Controllers;

use App\Models\CitaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SoloVistaController extends Controller
{
    private function getUserAllowedPrefixes()
    {
        $user = auth()->user();
        
        if (!$user) {
            Log::warning('No hay usuario autenticado');
            return [];
        }
        
        $prefijos = $user->prefijos()->where('activo', true)->pluck('prefijo')->toArray();
        
        Log::info('Usuario ' . $user->name . ' tiene prefijos: ' . implode(', ', $prefijos));
        
        return $prefijos;
    }
    
    private function extraerPrefijo($nombreArchivo)
    {
        preg_match('/^([A-Za-z]+)/', $nombreArchivo, $matches);
        return isset($matches[1]) ? strtoupper($matches[1]) : '';
    }
    
    public function index()
    {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.index', compact('prefijosPermitidos'));
    }

public function buscar(Request $request)
{
    $cedula = $request->cedula;
    $cedulasMultiple = $request->cedulas_multiple;
    
    // Si hay cédulas múltiples, procesarlas
    if (!empty($cedulasMultiple)) {
        // Separar por saltos de línea y limpiar
        $cedulas = array_filter(array_map('trim', explode("\n", $cedulasMultiple)));
        
        if (empty($cedulas)) {
            return redirect()->route('solo_vista.index')->with('mensaje', 'No se ingresaron cédulas válidas');
        }
        
        $resultados = [];
        $totalEncontrados = 0;
        
        foreach ($cedulas as $ced) {
            $docs = CitaRecibida::where('cedula', 'LIKE', "%{$ced}%")->get();
            if ($docs->isNotEmpty()) {
                $resultados[$ced] = $docs;
                $totalEncontrados += $docs->count();
            }
        }
        
        if (empty($resultados)) {
            return redirect()->route('solo_vista.index')->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas');
        }
        
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
    }
    
    // Búsqueda individual (funcionamiento original)
    if (empty($cedula)) {
        return redirect()->route('solo_vista.index')->with('mensaje', 'Por favor ingrese una cédula');
    }
    
    $resultados = CitaRecibida::where('cedula', 'LIKE', "%{$cedula}%")->get();
    
    if ($resultados->isEmpty()) {
        return redirect()->route('solo_vista.index')->with('mensaje', "No se encontraron documentos para la cédula: {$cedula}");
    }
    
    $resultados = [$cedula => $resultados];
    $prefijosPermitidos = $this->getUserAllowedPrefixes();
    
    return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
}
    
    //  MÉTODO DE DEPURACIÓN - REEMPLAZA EL ANTERIOR
public function verDocumentos($cedula)
{
    try {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        
        if (empty($prefijosPermitidos)) {
            return back()->with('mensaje', 'No tiene prefijos asignados para visualizar documentos');
        }
        
        $descripcionPrefijos = \App\Models\Prefijo::whereIn('prefijo', $prefijosPermitidos)
            ->pluck('descripcion', 'prefijo')
            ->toArray();
        
        $cita = CitaRecibida::where('cedula', $cedula)->first();
        
        if (!$cita) {
            return back()->with('mensaje', 'No se encontró registro para esta cédula');
        }
        
        // Ruta correcta donde están los archivos
        $carpeta = storage_path('app/public/RESULTADOS/' . $cedula);
        $pdfs = [];
        
        if (is_dir($carpeta)) {
            $archivos = glob($carpeta . '/*.pdf');
            $archivos = array_merge($archivos, glob($carpeta . '/*.PDF'));
            sort($archivos);
            
            foreach ($archivos as $archivo) {
                $nombreArchivo = basename($archivo);
                $prefijo = $this->extraerPrefijo($nombreArchivo);
                $prefijo = strtoupper($prefijo);
                
                if (!empty($prefijo) && in_array($prefijo, $prefijosPermitidos)) {
                    $descripcion = $descripcionPrefijos[$prefijo] ?? 'Sin descripción';
                    
                    $pdfs[] = [
                        'nombre' => $nombreArchivo,
                        'prefijo' => $prefijo,
                        'descripcion' => $descripcion,
                        'ruta' => $archivo,
                        'fecha' => $cita->fecha,
                        'mision' => $cita->mision,
                        'empresa' => $cita->nombre_empresa
                    ];
                }
            }
        }
        
        if (empty($pdfs)) {
            $prefijosTexto = implode(', ', $prefijosPermitidos);
            return back()->with('mensaje', "No se encontraron archivos PDF con los prefijos permitidos ({$prefijosTexto}) para esta cédula");
        }
        
        return view('certificados_e.solo_vista.ver-documentos', compact('cita', 'pdfs', 'cedula'));
        
    } catch (\Exception $e) {
        Log::error('Error al ver documentos: ' . $e->getMessage());
        return back()->with('mensaje', 'Error al cargar los documentos: ' . $e->getMessage());
    }
}
    
    public function verPdf($id, Request $request)
    {
        try {
            $cita = CitaRecibida::findOrFail($id);
            $nombreArchivo = $request->get('archivo');
            
            if (!$nombreArchivo) {
                abort(404, 'No se especificó el archivo');
            }
            
            $prefijoArchivo = strtoupper($this->extraerPrefijo($nombreArchivo));
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            if (!in_array($prefijoArchivo, $prefijosPermitidos)) {
                abort(403, 'No tiene permiso para acceder a este documento (prefijo: ' . $prefijoArchivo . ')');
            }
            
            $path = storage_path('app/public/RESULTADOS/' . $cita->cedula . '/' . $nombreArchivo);
            
            if (!file_exists($path)) {
                abort(404, 'El archivo PDF no existe: ' . $path);
            }
            
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al ver PDF: ' . $e->getMessage());
            abort(404, 'Error al cargar el PDF');
        }
    }
    
    public function verFusionados($cedula)
    {
        try {
            $documentos = CitaRecibida::where('cedula', 'LIKE', "%{$cedula}%")
                ->orderBy('fecha', 'desc')
                ->get();
            
            if ($documentos->isEmpty()) {
                return back()->with('mensaje', 'No se encontraron documentos para esta cédula');
            }
            
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            return view('certificados_e.solo_vista.fusionados', compact('documentos', 'cedula', 'prefijosPermitidos'));
            
        } catch (\Exception $e) {
            Log::error('Error al ver documentos fusionados: ' . $e->getMessage());
            return back()->with('mensaje', 'Error al cargar los documentos fusionados');
        }
    }
}