<?php
// app/Http/Controllers/SoloVistaController.php

namespace App\Http\Controllers;

use App\Models\CitaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SoloVistaController extends Controller
{
    /**
     * Obtiene los prefijos permitidos para el usuario logueado (visualizador)
     */
    private function getUserAllowedPrefixes()
    {
        $user = auth()->user();
        
        if (!$user) {
            Log::warning('No hay usuario autenticado');
            return [];
        }
        
        // Obtener los prefijos del usuario desde la relación
        $prefijos = $user->prefijos()->where('activo', true)->pluck('prefijo')->toArray();
        
        Log::info('Usuario ' . $user->name . ' tiene prefijos: ' . implode(', ', $prefijos));
        
        return $prefijos;
    }
    
    /**
     * Extrae el prefijo del nombre del archivo
     * Ejemplos: "H20260522.pdf" -> "H", "C20260522.pdf" -> "C", "CMA20260522.pdf" -> "CMA"
     */
    private function extraerPrefijo($nombreArchivo)
    {
        // Busca letras al inicio del nombre (pueden ser 1 o más caracteres)
        preg_match('/^([A-Za-z]+)/', $nombreArchivo, $matches);
        return isset($matches[1]) ? strtoupper($matches[1]) : '';
    }
    
  
    public function index()
    {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.index', compact('prefijosPermitidos'));
    }

    /**
     * Busca documentos por cédula(s)
     */
public function buscar(Request $request)
{
    // 🔥 DEPURACIÓN EXTREMA - Esto DEBE aparecer en el log
    \Illuminate\Support\Facades\Log::info('🚨🚨🚨 EL MÉTODO BUSCAR SE EJECUTÓ 🚨🚨🚨');
    \Illuminate\Support\Facades\Log::info('Método HTTP: ' . $request->method());
    \Illuminate\Support\Facades\Log::info('Todos los datos:', $request->all());
    \Illuminate\Support\Facades\Log::info('Headers:', $request->headers->all());
    
    try {
        // Obtener datos
        $cedula = $request->input('cedula');
        $cedulasMultiple = $request->input('cedulas_multiple');
        
        \Illuminate\Support\Facades\Log::info('Cédula individual: ' . ($cedula ?? 'NULL'));
        \Illuminate\Support\Facades\Log::info('Cédulas múltiples: ', ['value' => $cedulasMultiple]);
        
        $cedulas = [];
        
        if (!empty($cedula)) {
            $cedulas[] = trim($cedula);
            \Illuminate\Support\Facades\Log::info('Agregada cédula individual: ' . $cedula);
        }
        
        if (!empty($cedulasMultiple)) {
            \Illuminate\Support\Facades\Log::info('Procesando cédulas múltiples, tipo: ' . gettype($cedulasMultiple));
            
            if (is_array($cedulasMultiple)) {
                foreach ($cedulasMultiple as $index => $linea) {
                    \Illuminate\Support\Facades\Log::info("Línea $index: " . $linea);
                    $cedulasArray = explode("\n", $linea);
                    foreach ($cedulasArray as $ced) {
                        $cedulaItem = trim($ced);
                        if (!empty($cedulaItem)) {
                            $cedulas[] = $cedulaItem;
                            \Illuminate\Support\Facades\Log::info("Agregada cédula: $cedulaItem");
                        }
                    }
                }
            } elseif (is_string($cedulasMultiple)) {
                $cedulasArray = explode("\n", $cedulasMultiple);
                foreach ($cedulasArray as $ced) {
                    $cedulaItem = trim($ced);
                    if (!empty($cedulaItem)) {
                        $cedulas[] = $cedulaItem;
                        \Illuminate\Support\Facades\Log::info("Agregada cédula: $cedulaItem");
                    }
                }
            }
        }
        
        $cedulas = array_unique($cedulas);
        
        \Illuminate\Support\Facades\Log::info('Total cédulas a buscar: ' . count($cedulas));
        \Illuminate\Support\Facades\Log::info('Cédulas: ' . json_encode($cedulas));
        
        if (empty($cedulas)) {
            \Illuminate\Support\Facades\Log::warning('⚠️ No hay cédulas para buscar');
            return redirect()->route('solo_vista.index')
                ->with('mensaje', '⚠️ Por favor ingrese al menos una cédula');
        }
        
        $resultados = [];
        
        foreach ($cedulas as $cedulaBuscar) {
            \Illuminate\Support\Facades\Log::info("Buscando cédula: $cedulaBuscar");
            
            $documentos = CitaRecibida::where('cedula', 'LIKE', "%{$cedulaBuscar}%")
                ->orderBy('fecha', 'desc')
                ->get();
            
            \Illuminate\Support\Facades\Log::info("Encontrados " . $documentos->count() . " documentos para $cedulaBuscar");
            
            if ($documentos->count() > 0) {
                $resultados[$cedulaBuscar] = $documentos;
            }
        }
        
        if (empty($resultados)) {
            \Illuminate\Support\Facades\Log::warning('No se encontraron resultados para ninguna cédula');
            return redirect()->route('solo_vista.index')
                ->with('mensaje', '⚠️ No se encontraron documentos para: ' . implode(', ', $cedulas));
        }
        
        \Illuminate\Support\Facades\Log::info('✅ Búsqueda exitosa. Resultados encontrados para: ' . implode(', ', array_keys($resultados)));
        
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        
        return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ ERROR: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
        return redirect()->route('solo_vista.index')
            ->with('mensaje', '❌ Error: ' . $e->getMessage());
    }
}
    


public function verDocumentos($cedula)
{
    try {
        // Obtener prefijos permitidos del usuario (ya están en mayúsculas)
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        
        if (empty($prefijosPermitidos)) {
            return back()->with('mensaje', 'No tiene prefijos asignados para visualizar documentos');
        }
        
        // Obtener las descripciones de los prefijos desde la base de datos
        $descripcionPrefijos = \App\Models\Prefijo::whereIn('prefijo', $prefijosPermitidos)
            ->pluck('descripcion', 'prefijo')
            ->toArray();
        
        $cita = CitaRecibida::where('cedula', $cedula)->first();
        
        if (!$cita) {
            return back()->with('mensaje', 'No se encontró registro para esta cédula');
        }
        
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
                    // Obtener la descripción del prefijo
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
    
    /**
     * Ver PDF específico - CON VERIFICACIÓN DE PREFIJO
     */
public function verPdf($id, Request $request)
{
    try {
        $cita = CitaRecibida::findOrFail($id);
        $nombreArchivo = $request->get('archivo');
        
        if (!$nombreArchivo) {
            abort(404, 'No se especificó el archivo');
        }
        
        // Verificar permiso del prefijo (convertir a mayúsculas)
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
    
    /**
     * Ver documentos fusionados
     */
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