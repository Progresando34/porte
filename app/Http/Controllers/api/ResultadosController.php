<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CitaRecibida;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ResultadosController extends Controller
{
    /**
     * Verificar si existen resultados para una cédula
     */
    public function verificar($cedula)
    {
        try {
            $cita = CitaRecibida::where('cedula', $cedula)
                ->where('carpeta_copiada', true)
                ->first();
            
            if (!$cita) {
                return response()->json([
                    'success' => true,
                    'cedula' => $cedula,
                    'existe' => false,
                    'total_archivos' => 0,
                    'archivos' => []
                ]);
            }
            
            // Obtener archivos físicos en la carpeta
            $rutaFisica = storage_path('app/public/RESULTADOS/' . $cedula);
            $archivos = [];
            $totalArchivos = 0;
            
            if (is_dir($rutaFisica)) {
                $archivosLista = scandir($rutaFisica);
                $archivos = array_filter($archivosLista, function($item) {
                    return $item !== '.' && $item !== '..';
                });
                $totalArchivos = count($archivos);
            }
            
            return response()->json([
                'success' => true,
                'cedula' => $cedula,
                'existe' => $totalArchivos > 0,
                'total_archivos' => $totalArchivos,
                'archivos' => array_values($archivos),
                'ruta_resultados' => $cita->ruta_resultados ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en verificar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Listar archivos disponibles (leyendo del disco)
     */
    public function listarArchivos($cedula)
    {
        try {
            $cita = CitaRecibida::where('cedula', $cedula)
                ->where('carpeta_copiada', true)
                ->first();
            
            if (!$cita) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron resultados para esta cédula'
                ], 404);
            }
            
            // Leer archivos físicos del disco
            $rutaFisica = storage_path('app/public/RESULTADOS/' . $cedula);
            
            if (!is_dir($rutaFisica)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta de resultados no existe'
                ], 404);
            }
            
            $archivos = scandir($rutaFisica);
            $listaArchivos = [];
            
            foreach ($archivos as $archivo) {
                if ($archivo === '.' || $archivo === '..') continue;
                
                $rutaCompleta = $rutaFisica . '/' . $archivo;
                $listaArchivos[] = [
                    'nombre' => $archivo,
                    'tamaño' => filesize($rutaCompleta),
                    'fecha_modificacion' => date('Y-m-d H:i:s', filemtime($rutaCompleta)),
                    'ruta_descarga' => $cita->ruta_resultados . '/' . $archivo
                ];
            }
            
            return response()->json([
                'success' => true,
                'cedula' => $cedula,
                'total_archivos' => count($listaArchivos),
                'archivos' => $listaArchivos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en listarArchivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al listar archivos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Descargar archivo (desde archivo físico)
     */
    public function descargarArchivo($cedula, $archivoNombre)
    {
        try {
            $cita = CitaRecibida::where('cedula', $cedula)
                ->where('carpeta_copiada', true)
                ->first();
            
            if (!$cita) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron resultados'
                ], 404);
            }
            
            $rutaArchivo = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $archivoNombre);
            
            if (!file_exists($rutaArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }
            
            return response()->download($rutaArchivo, $archivoNombre, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en descargarArchivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}