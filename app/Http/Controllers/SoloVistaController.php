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
        
        // 🔥 NUEVO: Sobrescribir la descripción para el prefijo VF
        if (isset($descripcionPrefijos['VF'])) {
            $descripcionPrefijos['VF'] = 'Psicología';
        }
        
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
                    
                    // 🔥 NUEVO: Si el prefijo es VF, forzar descripción a "Psicología"
                    if ($prefijo === 'VF') {
                        $descripcion = 'Psicología';
                    }
                    
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

        public function descargarCarpetaCompleta(Request $request)
    {
        try {
            $cedulas = $request->input('cedulas', []);
            
            if (empty($cedulas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionaron cédulas'
                ], 400);
            }
            
            // Validar que el usuario tenga permisos
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            if (empty($prefijosPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para descargar documentos'
                ], 403);
            }
            
            // Crear ZIP temporal
            $zipFileName = 'Carpeta_Completa_' . date('Ymd_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            // Asegurar que existe el directorio temp
            if (!is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }
            
            $totalArchivos = 0;
            $cedulasProcesadas = [];
            
            foreach ($cedulas as $cedula) {
                $cedula = trim($cedula);
                if (empty($cedula)) continue;
                
                // Verificar que la cédula existe en la base de datos
                $cita = CitaRecibida::where('cedula', $cedula)->first();
                if (!$cita) {
                    Log::warning("Cédula no encontrada en BD: {$cedula}");
                    continue;
                }
                
                // Ruta de la carpeta de resultados
                $carpetaOrigen = storage_path('app/public/RESULTADOS/' . $cedula);
                
                if (!is_dir($carpetaOrigen)) {
                    Log::warning("Carpeta no existe: {$carpetaOrigen}");
                    continue;
                }
                
                // Crear subcarpeta dentro del ZIP para esta cédula
                $zip->addEmptyDir($cedula);
                
                // Recorrer archivos en la carpeta
                $archivos = glob($carpetaOrigen . '/*.pdf');
                $archivos = array_merge($archivos, glob($carpetaOrigen . '/*.PDF'));
                
                foreach ($archivos as $archivo) {
                    $nombreArchivo = basename($archivo);
                    $prefijo = $this->extraerPrefijo($nombreArchivo);
                    $prefijo = strtoupper($prefijo);
                    
                    // Verificar que el prefijo esté permitido
                    if (!in_array($prefijo, $prefijosPermitidos)) {
                        Log::debug("Archivo omitido (prefijo no permitido): {$nombreArchivo}");
                        continue;
                    }
                    
                    // Agregar archivo al ZIP dentro de la subcarpeta
                    $zip->addFile($archivo, $cedula . '/' . $nombreArchivo);
                    $totalArchivos++;
                }
                
                $cedulasProcesadas[] = $cedula;
            }
            
            $zip->close();
            
            if ($totalArchivos === 0) {
                // Limpiar archivo ZIP vacío
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron archivos para las cédulas seleccionadas'
                ], 404);
            }
            
            // Descargar el archivo ZIP
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error en descargarCarpetaCompleta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la carpeta completa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 NUEVO: Obtener información detallada de cédulas para el panel
     */
    public function obtenerInfoCedulas(Request $request)
    {
        try {
            $cedulas = $request->input('cedulas', []);
            
            if (empty($cedulas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se enviaron cédulas'
                ], 400);
            }
            
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            $resultados = [];
            
            foreach ($cedulas as $cedula) {
                $cedula = trim($cedula);
                if (empty($cedula)) continue;
                
                // Buscar la cita
                $cita = CitaRecibida::where('cedula', $cedula)->first();
                
                if (!$cita) {
                    $resultados[$cedula] = [
                        'encontrado' => false,
                        'mensaje' => 'Cédula no encontrada'
                    ];
                    continue;
                }
                
                // Obtener archivos de la carpeta
                $carpeta = storage_path('app/public/RESULTADOS/' . $cedula);
                $archivos = [];
                $totalArchivos = 0;
                
                if (is_dir($carpeta)) {
                    $archivosLista = glob($carpeta . '/*.pdf');
                    $archivosLista = array_merge($archivosLista, glob($carpeta . '/*.PDF'));
                    
                    foreach ($archivosLista as $archivo) {
                        $nombre = basename($archivo);
                        $prefijo = $this->extraerPrefijo($nombre);
                        $prefijo = strtoupper($prefijo);
                        
                        if (in_array($prefijo, $prefijosPermitidos)) {
                            $archivos[] = [
                                'nombre' => $nombre,
                                'prefijo' => $prefijo,
                                'tamano' => filesize($archivo)
                            ];
                            $totalArchivos++;
                        }
                    }
                }
                
                $resultados[$cedula] = [
                    'encontrado' => true,
                    'cedula' => $cedula,
                    'nombre' => $cita->nombre ?? 'N/A',
                    'nit_empresa' => $cita->nit_empresa ?? 'N/A',
                    'nombre_empresa' => $cita->nombre_empresa ?? 'N/A',
                    'fecha_cita' => $cita->fecha ? date('d/m/Y', strtotime($cita->fecha)) : 'N/A',
                    'mision' => $cita->mision ?? 'N/A',
                    'total_archivos' => $totalArchivos,
                    'archivos' => $archivos
                ];
            }
            
            return response()->json([
                'success' => true,
                'resultados' => $resultados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en obtenerInfoCedulas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información: ' . $e->getMessage()
            ], 500);
        }
    }

}