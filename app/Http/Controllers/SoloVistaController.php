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
    
    /**
     * Muestra el panel principal de solo visualización
     */
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

        file_put_contents(storage_path('logs/debug.txt'), date('Y-m-d H:i:s') . ' - Método: ' . $request->method() . ' - Datos: ' . json_encode($request->all()) . PHP_EOL, FILE_APPEND);
        try {
            $request->validate([
                'cedula' => 'nullable|string',
                'cedulas_multiple' => 'nullable|array',
            ]);

            $cedulas = [];
            
            if ($request->filled('cedula')) {
                $cedulas[] = trim($request->cedula);
            }
            
            if ($request->filled('cedulas_multiple')) {
                foreach ($request->cedulas_multiple as $linea) {
                    $cedulasArray = explode("\n", $linea);
                    foreach ($cedulasArray as $ced) {
                        $cedula = trim($ced);
                        if (!empty($cedula)) {
                            $cedulas[] = $cedula;
                        }
                    }
                }
            }
            
            $cedulas = array_unique($cedulas);
            
            if (empty($cedulas)) {
                return back()->with('mensaje', 'Por favor ingrese al menos una cédula');
            }
            
            $resultados = [];
            
            foreach ($cedulas as $cedula) {
                $documentos = CitaRecibida::where('cedula', 'LIKE', "%{$cedula}%")
                    ->orderBy('fecha', 'desc')
                    ->get();
                
                if ($documentos->count() > 0) {
                    $resultados[$cedula] = $documentos;
                }
            }
            
            if (empty($resultados)) {
                return back()->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas');
            }
            
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
            
        } catch (\Exception $e) {
            Log::error('Error en búsqueda: ' . $e->getMessage());
            return back()->with('mensaje', 'Error al buscar: ' . $e->getMessage());
        }
    }
    
    /**
     * Ver TODOS los documentos (PDFs) de una cédula - FILTRADO POR PREFIJOS DEL USUARIO
     */
/**
 * Ver TODOS los documentos (PDFs) de una cédula - FILTRADO POR PREFIJOS DEL USUARIO
 */
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