<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CertificadoEController extends Controller
{
    public function index()
    {
        Log::info('=== Vista cliente (sin restricciones) ===');
        return view('certificados_e.cliente');
    }


public function buscar(Request $request)
{
    $cedulasInput = $request->input('cedulas_multiple', []);
    $cedulaSimple = $request->input('cedula', '');
    $resultados = [];

    // ========== OBTENER PREFIJOS DEL USUARIO AUTENTICADO ==========
    $usuario = auth()->user();
    $trabajadorAutenticado = session('trabajador_autenticado', false);
    $filtrarPrefijos = true;
    $prefijosPermitidos = [];
    $cedulaTrabajador = session('trabajador_cedula', '');
    
    Log::info("=== CONFIGURACIÓN DE USUARIO ===");
    Log::info("Trabajador autenticado: " . ($trabajadorAutenticado ? 'Sí' : 'No'));
    Log::info("Usuario autenticado: " . (auth()->check() ? 'Sí' : 'No'));
    
    if ($trabajadorAutenticado) {
        $trabajadorId = session('trabajador_id');
        Log::info("Trabajador ID: {$trabajadorId}, Cédula: {$cedulaTrabajador}");
        
        $trabajador = \App\Models\Trabajador::find($trabajadorId);
        if ($trabajador) {
            $prefijosIds = $trabajador->obtenerPrefijosIds();
            if (!empty($prefijosIds)) {
                $prefijosPermitidos = \App\Models\Prefijo::whereIn('id', $prefijosIds)
                    ->where('activo', true)
                    ->pluck('prefijo')
                    ->toArray();
            }
        }
        $filtrarPrefijos = true;
        
    } elseif (auth()->check()) {
        Log::info("Usuario ID: {$usuario->id}, Profile ID: {$usuario->profile_id}");
        
        // Administradores NO tienen restricciones
        if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
            $filtrarPrefijos = false;
            Log::info("Usuario ADMINISTRADOR - Sin restricciones de prefijos");
        } else {
            $prefijosIds = $usuario->obtenerPrefijosArray();
            if (!empty($prefijosIds)) {
                $prefijosPermitidos = \App\Models\Prefijo::whereIn('id', $prefijosIds)
                    ->where('activo', true)
                    ->pluck('prefijo')
                    ->toArray();
            }
            $filtrarPrefijos = true;
            Log::info("Usuario normal - Filtrando por prefijos");
        }
    } else {
        Log::warning("Usuario NO autenticado");
        return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
    }
    
    Log::info("Filtrar prefijos: " . ($filtrarPrefijos ? 'Sí' : 'No'));
    Log::info("Prefijos permitidos: " . json_encode($prefijosPermitidos));

    // ========== LIMPIAR Y PREPARAR CÉDULAS A BUSCAR ==========
    $cedulasABuscar = [];
    
    // Agregar cédula simple si existe
    if (!empty($cedulaSimple)) {
        $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
        if (!empty($cedulaLimpia)) {
            $cedulasABuscar[] = $cedulaLimpia;
        }
    }
    
    // Agregar cédulas múltiples
    if (!empty($cedulasInput) && is_array($cedulasInput)) {
        foreach ($cedulasInput as $cedula) {
            if (!empty($cedula)) {
                $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedula));
                if (!empty($cedulaLimpia) && !in_array($cedulaLimpia, $cedulasABuscar)) {
                    $cedulasABuscar[] = $cedulaLimpia;
                }
            }
        }
    }
    
    // Si es trabajador, solo puede buscar su propia cédula
    if ($trabajadorAutenticado && !empty($cedulaTrabajador)) {
        $cedulasABuscar = array_intersect($cedulasABuscar, [$cedulaTrabajador]);
        if (empty($cedulasABuscar)) {
            return back()->with('mensaje', 'Solo puedes buscar tu propia cédula: ' . $cedulaTrabajador);
        }
    }
    
    if (empty($cedulasABuscar)) {
        return back()->with('mensaje', 'Ingrese al menos una cédula válida');
    }

    Log::info("=== INICIO BÚSQUEDA ===");
    Log::info("Cédulas a buscar: " . json_encode($cedulasABuscar));

    // ========== BUSCAR EN AMBAS TABLAS PARA CADA CÉDULA ==========
    foreach ($cedulasABuscar as $cedula) {
        $documentos = [];
        
        try {
            Log::info("--- Buscando documentos para cédula: {$cedula} ---");
            
            // 1. BUSCAR EN TABLA documentos_empresas
            $documentosEmpresas = DB::table('documentos_empresas')
                ->where('cedula', $cedula)
                ->get();
            
            Log::info("Documentos empresa encontrados (sin filtro): " . $documentosEmpresas->count());
            
            foreach ($documentosEmpresas as $doc) {
                $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
                
                // Obtener prefijo del archivo
                $info = $this->interpretarCertificado($nombreArchivo);
                $prefijoArchivo = $info['prefijo'];
                
                Log::info("Procesando documento empresa ID: {$doc->id}, Prefijo: '{$prefijoArchivo}'");
                
                // APLICAR FILTRO DE PREFIJOS
                if ($filtrarPrefijos) {
                    if (empty($prefijosPermitidos)) {
                        Log::info("Usuario sin prefijos permitidos, saltando documento ID: {$doc->id}");
                        continue;
                    }
                    if (!empty($prefijoArchivo) && !in_array($prefijoArchivo, $prefijosPermitidos)) {
                        Log::info("Documento con prefijo '{$prefijoArchivo}' no permitido, saltando");
                        continue;
                    }
                }
                
                // Buscar la ruta física
                $rutaFisica = $this->buscarRutaFisicaDocumento($doc, 'documentos_empresas', $cedula);
                
                if ($rutaFisica && file_exists($rutaFisica)) {
                    $documentos[] = (object)[
                        'id' => $doc->id,
                        'origen' => 'documentos_empresas',
                        'nombre_archivo' => $nombreArchivo,
                        'descripcion' => $info['descripcion'] ?? 'Documento',
                        'fecha' => $info['fecha'] ?? ($doc->created_at ?? ''),
                        'tipo' => $prefijoArchivo,
                        'ruta_fisica' => $rutaFisica,
                        'archivo_existe' => true,
                    ];
                    Log::info("✅ Documento empresa AGREGADO: {$nombreArchivo}");
                } else {
                    Log::warning("❌ Archivo no encontrado: {$nombreArchivo}");
                }
            }
            
            // 2. BUSCAR EN TABLA rayosxod
            $rayosXod = DB::table('rayosxod')
                ->where('cedula', $cedula)
                ->get();
            
            Log::info("Rayos X encontrados (sin filtro): " . $rayosXod->count());
            
            foreach ($rayosXod as $doc) {
                $nombreArchivo = $doc->nombre_archivo ?? 'rx_' . $doc->id . '.jpg';
                
                // Obtener prefijo del archivo
                $info = $this->interpretarCertificado($nombreArchivo);
                $prefijoArchivo = $info['prefijo'];
                
                Log::info("Procesando rayos X ID: {$doc->id}, Prefijo: '{$prefijoArchivo}'");
                
                // APLICAR FILTRO DE PREFIJOS
                if ($filtrarPrefijos) {
                    if (empty($prefijosPermitidos)) {
                        Log::info("Usuario sin prefijos permitidos, saltando rayos X ID: {$doc->id}");
                        continue;
                    }
                    if (!empty($prefijoArchivo) && !in_array($prefijoArchivo, $prefijosPermitidos)) {
                        Log::info("Rayos X con prefijo '{$prefijoArchivo}' no permitido, saltando");
                        continue;
                    }
                }
                
                // Buscar la ruta física
                $rutaFisica = $this->buscarRutaFisicaDocumento($doc, 'rayosxod', $cedula);
                
                if ($rutaFisica && file_exists($rutaFisica)) {
                    $documentos[] = (object)[
                        'id' => $doc->id,
                        'origen' => 'rayosxod',
                        'nombre_archivo' => $nombreArchivo,
                        'descripcion' => 'RX Odontología',
                        'fecha' => $doc->fecha_rx ?? ($doc->created_at ?? ''),
                        'tipo' => $prefijoArchivo,
                        'ruta_fisica' => $rutaFisica,
                        'archivo_existe' => true,
                    ];
                    Log::info("✅ Rayos X AGREGADO: {$nombreArchivo}");
                } else {
                    Log::warning("❌ Archivo no encontrado: {$nombreArchivo}");
                }
            }
            
            // Ordenar documentos por fecha
            usort($documentos, function($a, $b) {
                return strtotime($b->fecha) - strtotime($a->fecha);
            });
            
            $resultados[$cedula] = $documentos;
            Log::info("Total documentos para cédula {$cedula}: " . count($documentos));
            
        } catch (\Exception $e) {
            Log::error("Error al buscar cédula {$cedula}: " . $e->getMessage());
            $resultados[$cedula] = [];
        }
    }

    // Verificar si hay resultados
    $hayResultados = false;
    foreach ($resultados as $docs) {
        if (!empty($docs)) {
            $hayResultados = true;
            break;
        }
    }
    
    if (!$hayResultados) {
        Log::warning("No se encontraron documentos con los permisos actuales");
        return back()->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas o no tienes permisos para verlos.');
    }

    Log::info("=== FIN BÚSQUEDA ===");
    return view('certificados_e.resultados', compact('resultados'));
}

/**
 * Busca la ruta física de un documento según su origen
 */
private function buscarRutaFisicaDocumento($documento, $origen, $cedula)
{
    if ($origen === 'documentos_empresas') {
        $filename = $documento->filename ?? '';
        
        // Intentar con ruta_archivo
        if (!empty($documento->ruta_archivo)) {
            $rutasPosibles = [
                storage_path('app/public/' . str_replace('storage/', 'public/', $documento->ruta_archivo)),
                public_path($documento->ruta_archivo),
                public_path('storage/' . $documento->ruta_archivo),
                storage_path('app/public/' . $documento->ruta_archivo)
            ];
            
            foreach ($rutasPosibles as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }
        
        // Buscar por estructura de carpetas
        if (!empty($cedula) && !empty($filename)) {
            $rutasAlternativas = [
                storage_path('app/public/RESULTADOS/' . $cedula . '/' . $filename),
                public_path('storage/RESULTADOS/' . $cedula . '/' . $filename),
            ];
            
            foreach ($rutasAlternativas as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }
        
    } elseif ($origen === 'rayosxod') {
        $filename = $documento->nombre_archivo ?? '';
        
        if (!empty($cedula) && !empty($filename)) {
            $rutasRayos = [
                storage_path('app/public/RESULTADOS/' . $cedula . '/' . $filename),
                public_path('storage/RESULTADOS/' . $cedula . '/' . $filename),
            ];
            
            foreach ($rutasRayos as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }
        
        // Intentar con ruta en BD
        if (!empty($documento->ruta)) {
            $rutasBD = [
                public_path('storage/' . $documento->ruta),
                storage_path('app/public/' . $documento->ruta)
            ];
            
            foreach ($rutasBD as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }
    }
    
    return null;
}


// Método para ver rayos X
public function verRayos($id)
{
    Log::info("=== VER RAYOS X ID: {$id} ===");
    
    try {
        $rayo = DB::table('rayosxod')
            ->where('id', $id)
            ->first();
        
        if (!$rayo) {
            Log::error("Rayos X no encontrado ID: {$id}");
            abort(404, 'Documento no encontrado');
        }
        
        $cedula = $rayo->cedula;
        $nombreArchivo = $rayo->nombre_archivo;
        
        Log::info("Rayos X encontrado: ID={$id}, Cédula={$cedula}, Archivo={$nombreArchivo}");
        
        // Construir ruta física
        $rutaFisica = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
        $rutaAlternativa = public_path('storage/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
        
        $rutaUsar = null;
        if (file_exists($rutaFisica)) {
            $rutaUsar = $rutaFisica;
        } elseif (file_exists($rutaAlternativa)) {
            $rutaUsar = $rutaAlternativa;
        }
        
        if (!$rutaUsar) {
            Log::error("Archivo físico no encontrado para rayos X ID: {$id}");
            abort(404, 'Archivo físico no encontrado');
        }
        
        $tamañoArchivo = filesize($rutaUsar);
        Log::info("Archivo físico: {$rutaUsar}, tamaño: {$tamañoArchivo} bytes");
        
        // Determinar el tipo MIME
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        $mimeType = $extension === 'pdf' ? 'application/pdf' : 'image/jpeg';
        
        // Servir el archivo
        return response()->file($rutaUsar, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
            'Content-Length' => $tamañoArchivo,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        
    } catch (\Exception $e) {
        Log::error("Error en verRayos: " . $e->getMessage());
        abort(500, 'Error al cargar el documento: ' . $e->getMessage());
    }
}

// Método para descargar rayos X
public function descargarRayos($id)
{
    Log::info("=== DESCARGAR RAYOS X ID: {$id} ===");
    
    try {
        $rayo = DB::table('rayosxod')
            ->where('id', $id)
            ->first();
        
        if (!$rayo) {
            abort(404, 'Documento no encontrado');
        }
        
        $cedula = $rayo->cedula;
        $nombreArchivo = $rayo->nombre_archivo;
        
        // Construir ruta física
        $rutaFisica = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
        $rutaAlternativa = public_path('storage/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
        
        $rutaUsar = null;
        if (file_exists($rutaFisica)) {
            $rutaUsar = $rutaFisica;
        } elseif (file_exists($rutaAlternativa)) {
            $rutaUsar = $rutaAlternativa;
        }
        
        if (!$rutaUsar) {
            Log::error("Archivo físico no encontrado para descarga ID: {$id}");
            abort(404, 'Archivo físico no encontrado');
        }
        
        $tamañoArchivo = filesize($rutaUsar);
        Log::info("Descargando: {$nombreArchivo}, tamaño: {$tamañoArchivo} bytes, ruta: {$rutaUsar}");
        
        // Descargar el archivo
        return response()->download($rutaUsar, $nombreArchivo, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => $tamañoArchivo,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        
    } catch (\Exception $e) {
        Log::error("Error en descargarRayos: " . $e->getMessage());
        abort(500, 'Error al descargar el documento');
    }
}

// NUEVO método simplificado para procesar documentos
private function procesarDocumentoSimple($doc, $origen)
{
    $cedula = $doc->cedula ?? '';
    
    if ($origen === 'documentos_empresas') {
        $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
        $rutaBD = $doc->ruta_archivo ?? '';
    } else {
        $nombreArchivo = $doc->nombre_archivo ?? 'rx_' . $doc->id . '.jpg';
        $rutaBD = $doc->ruta ?? '';
    }

    // Buscar la ruta física
    $rutaFisica = null;
    
    // Ruta principal para rayosxod
    if ($origen === 'rayosxod' && !empty($cedula) && !empty($nombreArchivo)) {
        $rutaPosible = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
        if (file_exists($rutaPosible)) {
            $rutaFisica = $rutaPosible;
        }
    }
    
    // Si no se encontró, probar otras rutas
    if (!$rutaFisica && !empty($rutaBD)) {
        $rutasAlternativas = [
            storage_path('app/public/' . $rutaBD),
            public_path('storage/' . $rutaBD),
            base_path($rutaBD)
        ];
        
        foreach ($rutasAlternativas as $ruta) {
            if (file_exists($ruta)) {
                $rutaFisica = $ruta;
                break;
            }
        }
    }

    if (!$rutaFisica || !file_exists($rutaFisica)) {
        Log::warning("Archivo no encontrado para ID {$doc->id}");
        return null;
    }

    return (object)[
        'id' => $doc->id,
        'origen' => $origen,
        'nombre_archivo' => $nombreArchivo,
        'descripcion' => $this->obtenerDescripcion($nombreArchivo),
        'fecha' => $doc->fecha_rx ?? $doc->created_at ?? '',
        'tipo' => $this->extraerPrefijo($nombreArchivo),
        'ruta_fisica' => $rutaFisica,
        'archivo_existe' => true,
    ];
}

// NUEVO método para extraer prefijo
private function extraerPrefijo($nombreArchivo)
{
    $nombre = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    $nombreMayus = strtoupper($nombre);
    
    $prefijos = ['dx', 'RT', 'R', 'C', 'H', 'A', 'O', 'E'];
    
    foreach ($prefijos as $prefijo) {
        if (strpos($nombreMayus, $prefijo) === 0) {
            return $prefijo;
        }
    }
    
    return '';
}

// NUEVO método para obtener descripción
private function obtenerDescripcion($nombreArchivo)
{
    $prefijo = $this->extraerPrefijo($nombreArchivo);
    
    $descripciones = [
        'dx' => 'RX Odontología',
        'RT' => 'Rx Torax',
        'R' => 'RX Columna',
        'C' => 'Certificado de aptitud ocupacional',
        'H' => 'Historia ocupacional',
    ];
    
    return $descripciones[$prefijo] ?? 'Documento';
}

    
    /**
     * Aplica el filtro de prefijos a una query si es necesario.
     */
    private function aplicarFiltroPrefijos($query, $campoNombre, $filtrarPrefijos, $prefijosCadenas)
    {
        if ($filtrarPrefijos && !empty($prefijosCadenas)) {
            $query->where(function($q) use ($campoNombre, $prefijosCadenas) {
                foreach ($prefijosCadenas as $prefijo) {
                    $q->orWhere($campoNombre, 'LIKE', $prefijo . '%');
                }
            });
        } elseif ($filtrarPrefijos && empty($prefijosCadenas)) {
            $query->whereRaw('1 = 0');
        }
        return $query;
    }

    /**
     * Procesa un documento de cualquier tabla para unificarlo en un objeto estándar.
     */
private function procesarDocumento($doc, $origen, $filtrarPrefijos, $prefijosCadenas)
{
    $nombreArchivo = '';
    $rutaArchivoBD = null;

    if ($origen === 'documentos_empresas') {
        $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
        $rutaArchivoBD = $doc->ruta_archivo ?? null;
    } elseif ($origen === 'rayosxod') {
        $nombreArchivo = $doc->nombre_archivo ?? 'rx_' . $doc->id . '.jpg'; // Por defecto .jpg
        $rutaArchivoBD = $doc->ruta ?? null;
    }

    $info = $this->interpretarCertificado($nombreArchivo);
    $prefijoArchivo = $info['prefijo'];

    if ($filtrarPrefijos && !empty($prefijosCadenas) && !empty($prefijoArchivo)) {
        if (!in_array($prefijoArchivo, $prefijosCadenas)) {
            Log::info("Documento ID {$doc->id} (origen: {$origen}) con prefijo '{$prefijoArchivo}' no permitido. Saltando.");
            return null;
        }
    }

    $rutaFisica = $this->obtenerRutaFisica($doc, $origen);
    $archivoExiste = $rutaFisica && file_exists($rutaFisica);

    if (!$archivoExiste) {
        Log::warning("Archivo físico NO encontrado para documento ID {$doc->id} (origen: {$origen}). Ruta: " . ($rutaFisica ?? 'N/A'));
        
        // AGREGAR DEBUG PARA VER QUÉ RUTAS SE ESTÁN PROBANDO
        Log::debug("Rutas probadas para documento ID {$doc->id}:");
        if ($origen === 'rayosxod') {
            Log::debug("- Ruta en BD: " . ($doc->ruta ?? 'NULL'));
            Log::debug("- Nombre archivo: " . ($doc->nombre_archivo ?? 'NULL'));
        }
        
        return null;
    }

    return (object)[
        'id' => $doc->id,
        'origen' => $origen,
        'nombre_archivo' => $nombreArchivo,
        'descripcion' => $info['descripcion'],
        'fecha' => $info['fecha'],
        'tipo' => $prefijoArchivo,
        'fecha_creacion' => $doc->created_at ?? null,
        'ruta_archivo' => $rutaArchivoBD,
        'ruta_fisica' => $rutaFisica,
        'archivo_existe' => $archivoExiste,
    ];
}
    /**
     * Obtiene la ruta física del archivo
     */
private function obtenerRutaFisica($documento, $origen)
{
    $cedula = $documento->cedula ?? '';
    $filename = '';

    Log::info("=== Buscando ruta física ===", [
        'origen' => $origen,
        'id' => $documento->id ?? 'N/A',
        'cedula' => $cedula
    ]);

    if ($origen === 'documentos_empresas') {
        $filename = $documento->filename ?? '';
        if (isset($documento->ruta_archivo) && !empty($documento->ruta_archivo)) {
            $rutaRelativa = $documento->ruta_archivo;
            $rutaPublic = str_replace('storage/', 'public/', $rutaRelativa);
            $rutaAbsoluta = storage_path('app/' . $rutaPublic);
            $rutaAlternativa = public_path($rutaRelativa);
            if (file_exists($rutaAbsoluta)) return $rutaAbsoluta;
            if (file_exists($rutaAlternativa)) return $rutaAlternativa;
        }
    } elseif ($origen === 'rayosxod') {
        $filename = $documento->nombre_archivo ?? '';
        
        Log::info("Buscando archivo rayosxod:", [
            'id' => $documento->id,
            'cedula' => $cedula,
            'nombre_archivo' => $filename,
            'ruta_bd' => $documento->ruta ?? 'NULL'
        ]);
        
        // ===== RUTA CORRECTA SEGÚN EL DEBUG =====
        // El archivo está en: storage/app/public/RESULTADOS/1091657847/dxodo20250514.jpeg
        
        // Construir la ruta correcta
        if (!empty($cedula) && !empty($filename)) {
            // Ruta 1: storage/app/public/RESULTADOS/[cedula]/[archivo]
            $ruta1 = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $filename);
            
            // Ruta 2: public/storage/RESULTADOS/[cedula]/[archivo] (symlink)
            $ruta2 = public_path('storage/RESULTADOS/' . $cedula . '/' . $filename);
            
            // Ruta 3: Si la ruta en BD es relativa a public/storage
            if (!empty($documento->ruta)) {
                $ruta3 = public_path('storage/' . $documento->ruta);
                $ruta4 = storage_path('app/public/' . $documento->ruta);
                
                if (file_exists($ruta3)) {
                    Log::info("✅ Archivo encontrado en ruta3: {$ruta3}");
                    return $ruta3;
                }
                if (file_exists($ruta4)) {
                    Log::info("✅ Archivo encontrado en ruta4: {$ruta4}");
                    return $ruta4;
                }
            }
            
            // Verificar ruta1
            if (file_exists($ruta1)) {
                Log::info("✅ Archivo encontrado en ruta1: {$ruta1}");
                return $ruta1;
            }
            
            // Verificar ruta2
            if (file_exists($ruta2)) {
                Log::info("✅ Archivo encontrado en ruta2: {$ruta2}");
                return $ruta2;
            }
            
            // Si no se encontró, listar las rutas probadas
            Log::warning("❌ Archivo NO encontrado. Rutas probadas:");
            Log::warning("   - ruta1: {$ruta1}");
            Log::warning("   - ruta2: {$ruta2}");
            if (!empty($documento->ruta)) {
                Log::warning("   - ruta3: {$ruta3}");
                Log::warning("   - ruta4: {$ruta4}");
            }
        }
    }

    Log::warning("No se pudo determinar la ruta física. Origen: {$origen}, ID: " . ($documento->id ?? 'N/A'));
    return null;
}
    /**
     * Visualizar documento
     */
    public function verDocumento($id, Request $request)
    {
        $origen = $request->get('origen', 'documentos_empresas');

        Log::info("=== VER DOCUMENTO ID: {$id}, Origen: {$origen} ===");

        try {
            $documento = null;
            if ($origen === 'documentos_empresas') {
                $documento = DB::table('documentos_empresas')->where('id', $id)->first();
            } elseif ($origen === 'rayosxod') {
                $documento = DB::table('rayosxod')->where('id', $id)->first();
            }

            if (!$documento) {
                Log::error("Documento no encontrado ID: {$id} en origen: {$origen}");
                abort(404, 'Documento no encontrado');
            }

            $nombreArchivo = '';
            if ($origen === 'documentos_empresas') {
                $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            } else {
                $nombreArchivo = $documento->nombre_archivo ?? 'rx_' . $id . '.pdf';
            }

            $rutaFisica = $this->obtenerRutaFisica($documento, $origen);

            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para ID: {$id}, Origen: {$origen}");
                abort(404, 'Archivo físico no encontrado');
            }

            $mimeType = $this->obtenerMimeType($nombreArchivo);
            return response()->file($rutaFisica, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
            ]);

        } catch (\Exception $e) {
            Log::error("Error en verDocumento: " . $e->getMessage());
            abort(500, 'Error al cargar el documento');
        }
    }

    /**
     * Descargar documento individual
     */
    public function descargarDocumento($id, Request $request)
    {
        $origen = $request->get('origen', 'documentos_empresas');
        Log::info("=== DESCARGAR DOCUMENTO ID: {$id}, Origen: {$origen} ===");

        try {
            $documento = null;
            if ($origen === 'documentos_empresas') {
                $documento = DB::table('documentos_empresas')->where('id', $id)->first();
            } elseif ($origen === 'rayosxod') {
                $documento = DB::table('rayosxod')->where('id', $id)->first();
            }

            if (!$documento) {
                abort(404, 'Documento no encontrado');
            }

            $nombreArchivo = '';
            if ($origen === 'documentos_empresas') {
                $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            } else {
                $nombreArchivo = $documento->nombre_archivo ?? 'rx_' . $id . '.pdf';
            }

            $rutaFisica = $this->obtenerRutaFisica($documento, $origen);

            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para descarga ID: {$id}");
                abort(404, 'Archivo físico no encontrado');
            }

            return response()->download($rutaFisica, $nombreArchivo, [
                'Content-Type' => 'application/octet-stream',
            ]);

        } catch (\Exception $e) {
            Log::error("Error en descargarDocumento: " . $e->getMessage());
            abort(500, 'Error al descargar el documento');
        }
    }

    /**
     * Descargar múltiples documentos en ZIP
     */
    public function descargarMultiples(Request $request)
    {
        try {
            Log::info("=== INICIO DESCARGA MÚLTIPLE UNIFICADA ===");
            $cedula = $request->input('cedula', '');

            if (empty($cedula)) {
                return back()->with('mensaje', 'No se especificó la cédula para descargar');
            }

            // ========== VALIDACIONES DE SEGURIDAD ==========
            $usuario = auth()->user();
            $trabajadorAutenticado = false;
            $filtrarPrefijos = true;
            $prefijosUsuario = [];
            $prefijosCadenas = [];
            $trabajadorCedula = '';

            if (session('trabajador_autenticado')) {
                $trabajadorAutenticado = true;
                $trabajadorId = session('trabajador_id');
                $trabajadorCedula = session('trabajador_cedula') ?? '';
                if ($cedula !== $trabajadorCedula) {
                    return back()->with('mensaje', 'Solo puedes descargar documentos de tu propia cédula: ' . $trabajadorCedula);
                }
                $trabajador = \App\Models\Trabajador::find($trabajadorId);
                $prefijosUsuario = $trabajador ? $trabajador->obtenerPrefijosIds() : [];
                $filtrarPrefijos = true;
            } elseif ($usuario) {
                $prefijosUsuario = $usuario->obtenerPrefijosArray();
                $filtrarPrefijos = true;
                if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
                    $filtrarPrefijos = false;
                }
            } else {
                return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
            }

            if ($filtrarPrefijos && !empty($prefijosUsuario)) {
                $prefijosCadenas = \App\Models\Prefijo::whereIn('id', $prefijosUsuario)
                    ->where('activo', true)
                    ->pluck('prefijo')
                    ->toArray();
            }

            // ========== BUSCAR DOCUMENTOS EN AMBAS TABLAS ==========
            $documentosFiltrados = [];

            $queryEmpresas = DB::table('documentos_empresas')->where('cedula', $cedula);
            $docsEmpresas = $this->aplicarFiltroPrefijos($queryEmpresas, 'filename', $filtrarPrefijos, $prefijosCadenas)->get();
            foreach ($docsEmpresas as $doc) {
                $procesado = $this->procesarDocumento($doc, 'documentos_empresas', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosFiltrados[] = $procesado;
                }
            }

            $queryRayos = DB::table('rayosxod')->where('cedula', $cedula);
            $docsRayos = $this->aplicarFiltroPrefijos($queryRayos, 'nombre_archivo', $filtrarPrefijos, $prefijosCadenas)->get();
            foreach ($docsRayos as $doc) {
                $procesado = $this->procesarDocumento($doc, 'rayosxod', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosFiltrados[] = $procesado;
                }
            }

            Log::info("Total documentos para descarga múltiple: " . count($documentosFiltrados));

            if (empty($documentosFiltrados)) {
                return back()->with('mensaje', 'No hay documentos disponibles para descargar');
            }

            if (count($documentosFiltrados) === 1) {
                $doc = $documentosFiltrados[0];
                Log::info("Solo un documento, redirigiendo a descarga individual. Origen: {$doc->origen}, ID: {$doc->id}");
                return redirect()->route('documento.descargar', ['id' => $doc->id, 'origen' => $doc->origen]);
            }

            $zipFileName = 'certificados_' . $cedula . '_' . date('Ymd_His') . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
                Log::error("No se pudo crear el archivo ZIP");
                return back()->with('mensaje', 'Error al crear el archivo comprimido');
            }

            $archivosAgregados = 0;
            foreach ($documentosFiltrados as $doc) {
                if ($doc->archivo_existe && isset($doc->ruta_fisica) && file_exists($doc->ruta_fisica)) {
                    $zip->addFile($doc->ruta_fisica, $doc->nombre_archivo);
                    $archivosAgregados++;
                    Log::info("Agregado al ZIP: {$doc->nombre_archivo}");
                }
            }
            $zip->close();

            if ($archivosAgregados === 0) {
                return back()->with('mensaje', 'No se encontraron archivos físicos para descargar');
            }

            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error("Error en descargarMultiples: " . $e->getMessage());
            return back()->with('mensaje', 'Error al procesar la descarga: ' . $e->getMessage());
        }
    }

    /**
     * Obtener MIME type basado en extensión
     */
  private function obtenerMimeType($nombreArchivo)
{
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        // PDF
        'pdf' => 'application/pdf',
        
        // Imágenes
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        
        // Documentos
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        
        // Otros
        'txt' => 'text/plain',
        'csv' => 'text/csv',
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

    /**
     * Interpretar el nombre del certificado
     */
  
/**
 * Interpretar el nombre del certificado
 */
private function interpretarCertificado($nombreArchivo)
{
    Log::info("Interpretando certificado: {$nombreArchivo}");

    // Diccionario de prefijos (ordenados de mayor a menor longitud para priorizar coincidencias exactas)
    $prefijos = [
        // Prefijos largos primero
        'HING' => 'Historia ocupacional de ingreso en el examen ingreso/egreso',
        'HCV' => 'Historia Cardiovascular',
        'HNU' => 'Historia de Nutrición',
        'TESTPSICOLOGIA' => 'Test psicológico',
        'CILEGAL' => 'Certificado legal',
        'CILD' => 'Certificado legal documentado',
        'CIL' => 'Certificado legal intermedio',
        'CTA' => 'Certificado CENS o certificado de Alturas',
        'CMA' => 'Certificado de manipulación de alimentos',
        'EKG' => 'Electrocardiograma',
        'REM' => 'Remisión a EPS',
        'RPYP' => 'Remisión a PYP',
        'CV' => 'Carnet de vacunas',
        'VF' => 'Valoración Fisioterapia',
        'CM' => 'Coordinación Motriz',
        'PS' => 'Psicosensometrica',
        'ARM' => 'Documento adicional',
        // Prefijos medianos
        'HG' => 'Historia Medicina General',
        'RT' => 'Rx Torax',
        'RE' => 'Resultado Espirometría',
        'CI' => 'Certificado de ingreso',
        'TH' => 'Toxicología',
        'dxodo' => 'RX Odontología',  // ⚠️ IMPORTANTE: agregar dxodo
        // Prefijos cortos
        'H' => 'Historia ocupacional, ingreso, egreso, periódico',
        'C' => 'Certificado de aptitud ocupacional',
        'V' => 'Vertigo',
        'OM' => 'Osteomuscular',
        'A' => 'Audiometría',
        'EV' => 'Examen de voz',
        'O' => 'Optometría',
        'VIS' => 'Visiometría',
        'E' => 'Espirometría',
        'L' => 'Laboratorio Clínico',
        'S' => 'Psicología',
        'R' => 'RX Columna',
        'J' => 'Documento general',
        'dx' => 'RX ODONTOLOGIA',
    ];

    $nombreLimpio = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    $nombreMayus = strtoupper($nombreLimpio);

    Log::info("Nombre limpio: {$nombreLimpio}, Mayúsculas: {$nombreMayus}");

    // Si el nombre es genérico, no intentar buscar prefijo
    if (in_array($nombreMayus, ['DOCUMENTO', 'DOC', 'FILE', 'ARCHIVO'])) {
        return ['prefijo' => '', 'descripcion' => 'Documento empresarial', 'fecha' => '', 'nombre_original' => $nombreArchivo];
    }

    $prefijoEncontrado = '';
    $descripcion = 'Documento empresarial';

    // Buscar el prefijo que coincida exactamente al inicio del nombre
    foreach ($prefijos as $prefijo => $desc) {
        $prefijoMayus = strtoupper($prefijo);
        // Verificar si el nombre comienza con este prefijo
        if (strpos($nombreMayus, $prefijoMayus) === 0) {
            Log::info("Coincidencia encontrada: '{$prefijoMayus}' en '{$nombreMayus}'");
            // Tomar el prefijo más largo que coincida
            if (strlen($prefijo) > strlen($prefijoEncontrado)) {
                $prefijoEncontrado = $prefijo;
                $descripcion = $desc;
                Log::info("✅ Prefijo seleccionado: {$prefijo} - {$desc}");
            }
        }
    }

    // Extraer fecha
    $fecha = '';
    if ($prefijoEncontrado) {
        $resto = substr($nombreLimpio, strlen($prefijoEncontrado));
        Log::info("Resto después del prefijo: {$resto}");
        
        $patronesFecha = [
            '/^[_-]*(\d{8})/',  // YYYYMMDD
            '/^[_-]*(\d{6})/',  // YYMMDD
            '/^[_-]*(\d{4}-\d{2}-\d{2})/', // YYYY-MM-DD
            '/^[_-]*(\d{2}-\d{2}-\d{4})/', // DD-MM-YYYY
        ];
        
        foreach ($patronesFecha as $patron) {
            if (preg_match($patron, $resto, $matches)) {
                $fechaStr = $matches[1];
                
                if (strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                    $fecha = substr($fechaStr, 0, 4) . '-' . 
                             substr($fechaStr, 4, 2) . '-' . 
                             substr($fechaStr, 6, 2);
                } elseif (strlen($fechaStr) === 6 && is_numeric($fechaStr)) {
                    $anio = '20' . substr($fechaStr, 0, 2);
                    $fecha = $anio . '-' . 
                             substr($fechaStr, 2, 2) . '-' . 
                             substr($fechaStr, 4, 2);
                } elseif (strpos($fechaStr, '-') !== false) {
                    $fecha = $fechaStr;
                }
                
                if (!empty($fecha)) {
                    Log::info("Fecha extraída: {$fecha}");
                    break;
                }
            }
        }
    }

    return [
        'prefijo' => $prefijoEncontrado,
        'descripcion' => $descripcion,
        'fecha' => $fecha,
        'nombre_original' => $nombreArchivo
    ];
}



    public function indexTrabajador()
    {
        Log::info('=== Vista trabajador ===');
        if (!session()->has('trabajador_autenticado')) {
            return redirect()->route('login.form')->with('error', 'Debe iniciar sesión como trabajador.');
        }
        return view('certificados_e.trabajador', [
            'nombre' => session('trabajador_nombre'),
            'cedula' => session('trabajador_cedula'),
            'usuario' => session('trabajador_usuario'),
        ]);
    }

    public function debugDirecto()
{
    $cedula = '1091657847';
    
    echo "<h1>DEBUG DIRECTO - Cédula: {$cedula}</h1>";
    
    try {
        // 1. Verificar conexión a BD
        echo "<h2>1. Conexión a Base de Datos</h2>";
        try {
            DB::connection()->getPdo();
            echo "✅ Conexión exitosa a: " . DB::connection()->getDatabaseName() . "<br>";
        } catch (\Exception $e) {
            echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
        }
        
        // 2. Verificar que la tabla existe
        echo "<h2>2. Verificando tabla rayosxod</h2>";
        $tables = DB::select('SHOW TABLES');
        $tablaExiste = false;
        foreach ($tables as $table) {
            $tableName = current($table);
            if ($tableName == 'rayosxod') {
                $tablaExiste = true;
                echo "✅ Tabla 'rayosxod' encontrada<br>";
            }
        }
        if (!$tablaExiste) {
            echo "❌ Tabla 'rayosxod' NO existe<br>";
        }
        
        // 3. Buscar registros
        echo "<h2>3. Buscando registros para cédula {$cedula}</h2>";
        $registros = DB::table('rayosxod')
            ->where('cedula', $cedula)
            ->get();
        
        echo "Registros encontrados: " . $registros->count() . "<br>";
        
        if ($registros->count() > 0) {
            echo "<h3>Registros:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Fecha</th><th>Archivo</th><th>Ruta</th></tr>";
            
            foreach ($registros as $r) {
                echo "<tr>";
                echo "<td>{$r->id}</td>";
                echo "<td>{$r->nombre}</td>";
                echo "<td>{$r->cedula}</td>";
                echo "<td>{$r->fecha_rx}</td>";
                echo "<td>{$r->nombre_archivo}</td>";
                echo "<td>{$r->ruta}</td>";
                echo "</tr>";
                
                // 4. Verificar archivo físico
                echo "<tr><td colspan='6' style='background:#f0f0f0;'>";
                
                // Probar diferentes rutas
                $rutasProbadas = [
                    'Ruta directa' => $r->ruta,
                    'Public storage' => public_path('storage/' . $r->ruta),
                    'App public' => storage_path('app/public/' . $r->ruta),
                    'Base path' => base_path($r->ruta),
                    'Con carpeta rayosxod' => public_path('storage/rayosxod/' . $r->cedula . '/' . $r->nombre_archivo),
                    'Con carpeta RESULTADOS' => public_path('storage/RESULTADOS/' . $r->cedula . '/' . $r->nombre_archivo),
                ];
                
                foreach ($rutasProbadas as $nombre => $ruta) {
                    if (file_exists($ruta)) {
                        $tamano = filesize($ruta);
                        echo "✅ {$nombre}: Archivo encontrado en:<br> &nbsp;&nbsp; {$ruta}<br>";
                        echo "&nbsp;&nbsp; Tamaño: " . round($tamano/1024, 2) . " KB<br>";
                        
                        // Mostrar imagen si es JPEG
                        $ext = strtolower(pathinfo($r->nombre_archivo, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $url = asset('storage/' . $r->ruta);
                            echo "&nbsp;&nbsp; <img src='{$url}' style='max-width:200px; max-height:200px; border:1px solid #ccc;'><br>";
                        }
                    }
                }
                
                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            // Mostrar algunos registros de ejemplo de la tabla
            echo "<h3>Últimos 5 registros en rayosxod (cualquier cédula):</h3>";
            $ultimos = DB::table('rayosxod')->limit(5)->get();
            if ($ultimos->count() > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Fecha</th><th>Archivo</th><th>Ruta</th></tr>";
                foreach ($ultimos as $u) {
                    echo "<tr>";
                    echo "<td>{$u->id}</td>";
                    echo "<td>{$u->nombre}</td>";
                    echo "<td>{$u->cedula}</td>";
                    echo "<td>{$u->fecha_rx}</td>";
                    echo "<td>{$u->nombre_archivo}</td>";
                    echo "<td>{$u->ruta}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "❌ No hay ningún registro en la tabla rayosxod<br>";
            }
        }
        
        // 5. Verificar usuario actual
        echo "<h2>4. Usuario actual</h2>";
        if (auth()->check()) {
            $user = auth()->user();
            echo "✅ Usuario autenticado: {$user->name}<br>";
            echo "Email: {$user->email}<br>";
            echo "Profile ID: {$user->profile_id}<br>";
            echo "Es admin: " . ($user->esAdministrador() ? 'Sí' : 'No') . "<br>";
            
            // Prefijos del usuario
            $prefijos = $user->obtenerPrefijosArray();
            echo "Prefijos del usuario (IDs): " . json_encode($prefijos) . "<br>";
            
            if (!empty($prefijos)) {
                $prefijosNombres = \App\Models\Prefijo::whereIn('id', $prefijos)->pluck('prefijo')->toArray();
                echo "Prefijos (nombres): " . json_encode($prefijosNombres) . "<br>";
            }
        } else {
            echo "❌ Usuario NO autenticado<br>";
        }
        
    } catch (\Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
}