<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SincronizadorController;
use App\Http\Controllers\api\ResultadosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // ← AGREGA ESTA LÍNEA
use App\Http\Controllers\api\CertificadosArmasController;
use App\Http\Controllers\api\HistoriaController;  
use Illuminate\Support\Facades\Log;


Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::post('/importar-citas', function(Request $request) {
    $citas = $request->input('citas', []);
    $insertadas = 0;
    $errores = [];
    
    foreach ($citas as $index => $cita) {
        try {
            // BUSCAR si ya existe la cita (para evitar duplicados)
            $existe = DB::table('citas_recibidas')
                ->where('cedula', $cita['cedula'] ?? null)
                ->where('fecha', $cita['fecha'] ?? null)
                ->exists();
            
            if ($existe) {
                // Si ya existe, solo actualizamos los campos que faltan
                DB::table('citas_recibidas')
                    ->where('cedula', $cita['cedula'] ?? null)
                    ->where('fecha', $cita['fecha'] ?? null)
                    ->update([
                        'nombre_empresa' => $cita['nombre_empresa'] ?? '',
                        'mision' => $cita['mision'] ?? null,
                        'mision_empresa' => $cita['mision_empresa'] ?? null,
                        'updated_at' => now()
                    ]);
                $insertadas++;
            } else {
                // Si no existe, insertar nuevo registro
                DB::table('citas_recibidas')->insert([
                    'cedula' => $cita['cedula'] ?? null,
                    'nombre' => $cita['nombre'] ?? '',
                    'fecha' => $cita['fecha'] ?? null,
                    'nit_empresa' => $cita['empresa'] ?? null,
                    'nombre_empresa' => $cita['nombre_empresa'] ?? '',
                    'mision' => $cita['mision'] ?? null,
                    'mision_empresa' => $cita['mision_empresa'] ?? null,
                    'datos_completos' => json_encode($cita),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertadas++;
            }
        } catch (\Exception $e) {
            $errores[] = "Cita $index: " . $e->getMessage();
        }
    }
    
    return response()->json([
        'success' => true,
        'insertadas' => $insertadas,
        'errores' => $errores,
        'total' => count($citas)
    ]);
});

use App\Models\Profile;

Route::post('/perfil/crear', function(Request $request) {
    try {
        $name = $request->input('name');
        
        if (!$name) {
            return response()->json([
                'success' => false,
                'message' => 'El nombre del perfil es requerido'
            ], 400);
        }
        
        
        $profile = Profile::where('name', $name)->first();
        
        if (!$profile) {
            $profile = Profile::create(['name' => $name]);
            $creado = true;
        } else {
            $creado = false;
        }
        
        return response()->json([
            'success' => true,
            'id' => $profile->id,
            'name' => $profile->name,
            'creado' => $creado,
            'message' => $creado ? 'Perfil creado' : 'El perfil ya existía'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// ============================================
// 🗑️ ELIMINACIÓN DE ARCHIVOS CON PREFIJO 's'
// ============================================
Route::delete('/eliminar/archivos-prefijo-s', function() {
    try {
        $rutaBase = storage_path('app/public/RESULTADOS');
        $eliminados = 0;
        $errores = 0;
        $archivosEncontrados = [];
        $archivosOmitidos = [];
        $fechaMinima = '20260514'; // 🔥 FECHA MÍNIMA
        
        // Verificar que la carpeta existe
        if (!is_dir($rutaBase)) {
            return response()->json([
                'success' => false,
                'error' => 'La carpeta RESULTADOS no existe'
            ], 404);
        }
        
        // Recorrer todas las carpetas de cédulas
        foreach (glob($rutaBase . '/*', GLOB_ONLYDIR) as $carpeta) {
            $cedula = basename($carpeta);
            
            // Buscar archivos que empiecen con 's'
            foreach (glob($carpeta . '/s*.pdf') as $archivo) {
                $nombre = basename($archivo);
                
                // 🔥 VALIDACIÓN ESTRICTA: 's' + 8 dígitos + .pdf
                if (preg_match('/^s(\d{8})\.pdf$/i', $nombre, $matches)) {
                    $fechaArchivo = $matches[1]; // Extraer YYYYMMDD
                    
                    // 🔥 VALIDACIÓN DE FECHA: SOLO >= 20260514
                    if ($fechaArchivo < $fechaMinima) {
                        $archivosOmitidos[] = [
                            'cedula' => $cedula,
                            'nombre' => $nombre,
                            'fecha' => $fechaArchivo,
                            'motivo' => "Fecha {$fechaArchivo} < {$fechaMinima}"
                        ];
                        Log::info("⏭️ Archivo omitido (fecha antigua): {$cedula}/{$nombre} (fecha: {$fechaArchivo})");
                        continue;
                    }
                    
                    // Archivo válido para eliminar
                    $archivosEncontrados[] = [
                        'cedula' => $cedula,
                        'nombre' => $nombre,
                        'fecha' => $fechaArchivo,
                        'ruta' => $archivo,
                        'tamano' => filesize($archivo)
                    ];
                } else {
                    Log::warning("⚠️ Archivo omitido (formato inválido): {$cedula}/{$nombre}");
                }
            }
        }
        
        // 🔥 Si no hay archivos para eliminar
        if (empty($archivosEncontrados)) {
            return response()->json([
                'success' => true,
                'mensaje' => 'No se encontraron archivos con prefijo "s" y fecha >= ' . $fechaMinima,
                'eliminados' => 0,
                'omitidos_por_fecha' => count($archivosOmitidos),
                'archivos_omitidos' => $archivosOmitidos,
                'archivos_encontrados' => []
            ]);
        }
        
        // 🔥 Eliminar SOLO los archivos validados
        $erroresDetalle = [];
        foreach ($archivosEncontrados as $archivoInfo) {
            $archivo = $archivoInfo['ruta'];
            $cedula = $archivoInfo['cedula'];
            $nombre = $archivoInfo['nombre'];
            $fecha = $archivoInfo['fecha'];
            
            if (unlink($archivo)) {
                $eliminados++;
                Log::info("🗑️ Archivo eliminado: {$cedula}/{$nombre} (fecha: {$fecha})");
            } else {
                $errores++;
                $erroresDetalle[] = "No se pudo eliminar: {$cedula}/{$nombre}";
                Log::error("❌ Error eliminando: {$cedula}/{$nombre}");
            }
        }
        
        return response()->json([
            'success' => true,
            'eliminados' => $eliminados,
            'errores' => $errores,
            'detalle_errores' => $erroresDetalle,
            'omitidos_por_fecha' => count($archivosOmitidos),
            'archivos_omitidos' => $archivosOmitidos,
            'archivos_eliminados' => $archivosEncontrados,
            'fecha_minima' => $fechaMinima,
            'mensaje' => "Se eliminaron {$eliminados} archivos con prefijo 's' y fecha >= {$fechaMinima}"
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error en eliminación de archivos s: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Agrega esta ruta después de la ruta de perfiles
Route::post('/prefijo/crear', function(Request $request) {
    try {
        $prefijo = $request->input('prefijo');
        $descripcion = $request->input('descripcion');
        
        if (!$prefijo || !$descripcion) {
            return response()->json([
                'success' => false,
                'message' => 'El prefijo y la descripción son requeridos'
            ], 400);
        }
        
        // Buscar si ya existe
        $existe = DB::table('prefijos')->where('prefijo', $prefijo)->first();
        
        if (!$existe) {
            $id = DB::table('prefijos')->insertGetId([
                'prefijo' => $prefijo,
                'descripcion' => $descripcion,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'id' => $id,
                'prefijo' => $prefijo,
                'descripcion' => $descripcion,
                'creado' => true,
                'message' => 'Prefijo creado exitosamente'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'id' => $existe->id,
                'prefijo' => $existe->prefijo,
                'descripcion' => $existe->descripcion,
                'creado' => false,
                'message' => 'El prefijo ya existía'
            ]);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::prefix('certificados-armas')->group(function () {
    // Importar desde Excel
    Route::post('/importar', [CertificadosArmasController::class, 'importarDesdeExcel']);
    
    // Verificar existencia de un registro específico
    Route::post('/verificar', [CertificadosArmasController::class, 'verificarExistencia']);
    
    // Listar todos los registros
    Route::get('/todos', [CertificadosArmasController::class, 'listarTodos']);

        // 🔥 NUEVOS ENDPOINTS SEGUROS
    // Verificar estado de la tabla (sin modificar nada)
    Route::get('/estado', [CertificadosArmasController::class, 'verificarEstado']);
    
    // TRUNCATE: Eliminar TODOS los registros y reiniciar IDs
    Route::delete('/truncar', [CertificadosArmasController::class, 'truncarTabla']);

      // 🔥 NUEVO - Usa POST en lugar de DELETE
    Route::post('/reiniciar', [CertificadosArmasController::class, 'reiniciarCompleto']);
     
        Route::post('/modificar-columna', [CertificadosArmasController::class, 'modificarColumnaResultado']);
        Route::post('/corregir-sede', [CertificadosArmasController::class, 'corregirSedeIps']);
});


Route::get('/auditoria/archivos-prefijo-s', function() {
    try {
        $fechaMinima = '20260514';
        $resultados = [
            'total_archivos' => 0,
            'total_cedulas' => 0,
            'archivos' => [],
            'resumen_por_cedula' => [],
            'tamano_total_bytes' => 0,
            'fecha_consulta' => now()->toDateTimeString(),
            'fecha_minima' => $fechaMinima,
            'archivos_omitidos_por_fecha' => [],
            'advertencia' => '⚠️ ESTO ES SOLO UNA AUDITORÍA - NO SE ELIMINÓ NADA'
        ];
        
        $rutaBase = storage_path('app/public/RESULTADOS');
        
        if (!is_dir($rutaBase)) {
            return response()->json([
                'error' => 'La carpeta RESULTADOS no existe',
                'ruta' => $rutaBase
            ], 404);
        }
        
        // Recorrer todas las carpetas de cédulas
        $carpetas = glob($rutaBase . '/*', GLOB_ONLYDIR);
        $resultados['total_cedulas'] = count($carpetas);
        
        foreach ($carpetas as $carpeta) {
            $cedula = basename($carpeta);
            $archivosS = [];
            $tamanoCedula = 0;
            $cantidadCedula = 0;
            
            // Buscar archivos que empiecen con 's'
            foreach (glob($carpeta . '/s*.pdf') as $archivo) {
                $nombre = basename($archivo);
                
                // 🔥 VALIDACIÓN ESTRICTA: 's' + 8 dígitos + .pdf
                if (preg_match('/^s(\d{8})\.pdf$/i', $nombre, $matches)) {
                    $fechaArchivo = $matches[1];
                    
                    // 🔥 VALIDACIÓN DE FECHA: SOLO >= 20260514
                    if ($fechaArchivo < $fechaMinima) {
                        $resultados['archivos_omitidos_por_fecha'][] = [
                            'cedula' => $cedula,
                            'nombre' => $nombre,
                            'fecha' => $fechaArchivo,
                            'motivo' => "Fecha {$fechaArchivo} < {$fechaMinima}"
                        ];
                        continue; // Saltar archivos con fecha antigua
                    }
                    
                    $tamano = filesize($archivo);
                    $fechaMod = date('Y-m-d H:i:s', filemtime($archivo));
                    
                    $archivosS[] = [
                        'nombre' => $nombre,
                        'fecha_archivo' => $fechaArchivo,
                        'tamano_bytes' => $tamano,
                        'tamano_humano' => formatearTamano($tamano),
                        'fecha_modificacion' => $fechaMod,
                        'ruta_relativa' => 'RESULTADOS/' . $cedula . '/' . $nombre
                    ];
                    
                    $tamanoCedula += $tamano;
                    $cantidadCedula++;
                    $resultados['tamano_total_bytes'] += $tamano;
                }
            }
            
            if ($cantidadCedula > 0) {
                $resultados['archivos'][$cedula] = $archivosS;
                $resultados['resumen_por_cedula'][$cedula] = [
                    'cantidad' => $cantidadCedula,
                    'tamano_total_bytes' => $tamanoCedula,
                    'tamano_total_humano' => formatearTamano($tamanoCedula)
                ];
                $resultados['total_archivos'] += $cantidadCedula;
            }
        }
        
        $resultados['tamano_total_humano'] = formatearTamano($resultados['tamano_total_bytes']);
        
        Log::warning('AUDITORÍA DE ARCHIVOS CON PREFIJO S', [
            'total_archivos' => $resultados['total_archivos'],
            'total_cedulas_afectadas' => count($resultados['resumen_por_cedula']),
            'tamano_total' => $resultados['tamano_total_humano'],
            'fecha' => $resultados['fecha_consulta']
        ]);
        
        return response()->json($resultados);
        
    } catch (\Exception $e) {
        Log::error('Error en auditoría: ' . $e->getMessage());
        return response()->json([
            'error' => 'Error en auditoría',
            'message' => $e->getMessage()
        ], 500);
    }
});

// ✅ Helper function - CORREGIDO (sin $this)
function formatearTamano($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}





// ============================================
// 🔥 NUEVAS RUTAS PARA HISTORIA CLÍNICA
// ============================================
Route::prefix('historia')->group(function () {
    // Importar registros de historia clínica
    Route::post('/importar', [HistoriaController::class, 'importar']);
    
    // Verificar si un registro existe (para evitar duplicados)
    Route::post('/verificar', [HistoriaController::class, 'verificarExistencia']);
    
    // Obtener resumen de importaciones
    Route::get('/resumen', [HistoriaController::class, 'resumen']);
});



Route::get('/empresa/nombre/{codigo}', function($codigo) {
    try {
        $empresa = DB::table('empresas')->where('codigo', $codigo)->first();
        
        if ($empresa && $empresa->nombre) {
            return response()->json([
                'success' => true,
                'nombre' => $empresa->nombre
            ]);
        }
        
        return response()->json([
            'success' => false,
            'nombre' => ''
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'nombre' => '',
            'error' => $e->getMessage()
        ]);
    }
});

Route::post('/sincronizar/archivos', [SincronizadorController::class, 'recibirArchivos']);
Route::get('/sincronizar/pendientes/{nit}', [SincronizadorController::class, 'obtenerPendientes']);
Route::post('/sincronizar/citas/importar', [SincronizadorController::class, 'importarCitas']);
Route::post('/sincronizar/empresas/importar', [SincronizadorController::class, 'importarEmpresas']);


Route::prefix('resultados')->group(function () {
    Route::get('/archivos/{cedula}', [ResultadosController::class, 'listarArchivos']);
    Route::get('/descargar/{cedula}/{archivo}', [ResultadosController::class, 'descargarArchivo']);
    Route::get('/verificar/{cedula}', [ResultadosController::class, 'verificar']);
});

Route::get('/resultados/verificar/{cedula}', [ResultadosController::class, 'verificar']);

// Nueva ruta para obtener citas existentes
Route::get('/citas/existentes', function() {
    try {
        $citas = DB::table('citas_recibidas')
            ->select('cedula', 'fecha')
            ->get();
        
        return response()->json([
            'success' => true,
            'total' => count($citas),
            'citas' => $citas
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// Ruta para obtener TODAS las citas (sin importar carpeta_copiada)
Route::get('/sincronizar/todas/{nit}', function($nit) {
    try {
        $citas = DB::table('citas_recibidas')
            ->where('nit_empresa', $nit)
            ->where('fecha', '>=', '2026-05-14')
            ->get();
        
        $resultado = [];
        foreach ($citas as $cita) {
            $resultado[] = [
                'cedula' => $cita->cedula,
                'nombre' => $cita->nombre,
                'fecha_cita' => $cita->fecha,
                'mision' => $cita->mision ?? '',
                'nit_empresa' => $nit,
                'nombre_empresa' => $cita->nombre_empresa ?? '',
                'mision_empresa' => $cita->mision_empresa ?? '',
            ];
        }
        
        return response()->json([
            'success' => true,
            'citas' => $resultado
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});