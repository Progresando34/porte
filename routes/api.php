<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// HEALTH CHECK
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// ENDPOINT PARA IMPORTAR EMPRESAS - CON CREACIÓN AUTOMÁTICA DE COLUMNAS
Route::post('/sincronizar/empresas/importar', function(Request $request) {
    try {
        $empresas = $request->input('empresas', []);
        
        if (empty($empresas)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay empresas para importar'
            ], 400);
        }

        $insertadas = 0;
        $actualizadas = 0;
        $errores = 0;

        foreach ($empresas as $index => $empresa) {
            try {
                // Limpiar datos
                $datos = [];
                foreach ($empresa as $key => $value) {
                    $columnaLimpia = strtolower(trim($key));
                    $columnaLimpia = preg_replace('/[^a-z0-9_]/', '_', $columnaLimpia);
                    $columnaLimpia = preg_replace('/_+/', '_', $columnaLimpia);
                    $columnaLimpia = trim($columnaLimpia, '_');
                    
                    if (empty($columnaLimpia) || is_numeric($columnaLimpia[0])) {
                        $columnaLimpia = 'col_' . $columnaLimpia;
                    }
                    $columnaLimpia = substr($columnaLimpia, 0, 64);
                    
                    // Crear columna si no existe
                    if (!Schema::hasColumn('empresas', $columnaLimpia)) {
                        try {
                            Schema::table('empresas', function (Blueprint $table) use ($columnaLimpia) {
                                $table->text($columnaLimpia)->nullable();
                            });
                        } catch (\Exception $e) {
                            // La columna ya existe o hubo error
                        }
                    }
                    
                    // Limpiar valor
                    if ($value === '' || $value === null) {
                        $datos[$columnaLimpia] = null;
                    } elseif (is_numeric($value)) {
                        $datos[$columnaLimpia] = (string)$value;
                    } else {
                        $datos[$columnaLimpia] = $value;
                    }
                }
                
                // Obtener identificador (usar codigo o nit)
                $codigo = $datos['codigo'] ?? null;
                $nit = $datos['nit'] ?? null;
                
                $identificador = null;
                $campoIdentificador = null;
                
                if ($nit && $nit !== '1' && $nit !== '') {
                    $identificador = $nit;
                    $campoIdentificador = 'nit';
                } elseif ($codigo && $codigo !== '1' && $codigo !== '') {
                    $identificador = $codigo;
                    $campoIdentificador = 'codigo';
                }
                
                if (!$identificador) {
                    $errores++;
                    continue;
                }
                
                // Verificar si existe
                $existe = DB::table('empresas')->where($campoIdentificador, $identificador)->exists();

                // Agregar timestamps
                $datos['updated_at'] = now();
                
                if (!$existe) {
                    $datos['created_at'] = now();
                    DB::table('empresas')->insert($datos);
                    $insertadas++;
                } else {
                    DB::table('empresas')
                        ->where($campoIdentificador, $identificador)
                        ->update($datos);
                    $actualizadas++;
                }
                
            } catch (\Exception $e) {
                $errores++;
                if ($errores <= 5) {
                    \Log::error("Error en empresa {$index}: " . $e->getMessage());
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'errores' => $errores,
            'total_recibidas' => count($empresas)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ], 500);
    }
});

// ENDPOINTS SIMPLIFICADOS PARA LOS DEMÁS
Route::post('/sincronizar/archivos', function(Request $request) {
    return response()->json(['success' => true, 'message' => 'Endpoint funcionando']);
});

Route::get('/sincronizar/pendientes/{nit}', function($nit) {
    return response()->json(['success' => true, 'nit' => $nit, 'citas' => []]);
});

Route::post('/sincronizar/citas/importar', function(Request $request) {
    return response()->json(['success' => true, 'insertadas' => 0, 'errores' => 0]);
});

Route::prefix('resultados')->group(function () {
    Route::get('/archivos/{cedula}', function($cedula) {
        return response()->json(['success' => true, 'archivos' => []]);
    });
    Route::get('/descargar/{cedula}/{archivo}', function($cedula, $archivo) {
        return response()->json(['success' => false, 'message' => 'No implementado'], 404);
    });
    Route::get('/verificar/{cedula}', function($cedula) {
        return response()->json(['success' => true, 'existe' => false]);
    });
});