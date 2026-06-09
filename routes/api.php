<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// HEALTH CHECK
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// ENDPOINT PARA IMPORTAR EMPRESAS - FUNCIONA DIRECTAMENTE
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
        $detalles = [];

        foreach ($empresas as $index => $empresa) {
            try {
                // Obtener identificadores
                $nit = $empresa['nit'] ?? null;
                $codigo = $empresa['codigo'] ?? null;
                
                // Limpiar valores (convertir bytes a string si es necesario)
                if (is_numeric($nit)) {
                    $nit = (string)$nit;
                }
                if (is_numeric($codigo)) {
                    $codigo = (string)$codigo;
                }
                
                // Decidir qué identificador usar
                // Si NIT es válido (no es 1, no está vacío), usarlo
                // Si no, usar CODIGO
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
                    $detalles[] = "Índice {$index}: Sin identificador válido (nit: {$nit}, codigo: {$codigo})";
                    continue;
                }
                
                // Limpiar datos
                $datos = [];
                foreach ($empresa as $key => $value) {
                    if ($value === '' || $value === null) {
                        $datos[$key] = null;
                    } elseif (is_numeric($value)) {
                        // Convertir números a string para evitar problemas
                        $datos[$key] = (string)$value;
                    } else {
                        $datos[$key] = $value;
                    }
                }
                
                // Verificar si existe usando el identificador correspondiente
                $existe = DB::table('empresas')->where($campoIdentificador, $identificador)->exists();

                if (!$existe) {
                    DB::table('empresas')->insert(array_merge(
                        $datos,
                        ['created_at' => now(), 'updated_at' => now()]
                    ));
                    $insertadas++;
                } else {
                    DB::table('empresas')
                        ->where($campoIdentificador, $identificador)
                        ->update(array_merge(
                            $datos,
                            ['updated_at' => now()]
                        ));
                    $actualizadas++;
                }
                
            } catch (\Exception $e) {
                $errores++;
                $detalles[] = "Índice {$index}: " . $e->getMessage();
            }
        }
        
        $respuesta = [
            'success' => true,
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'errores' => $errores,
            'total_recibidas' => count($empresas)
        ];
        
        if (count($detalles) > 0 && $errores > 0) {
            $respuesta['detalles'] = array_slice($detalles, 0, 10); // Solo primeros 10 errores
        }
        
        return response()->json($respuesta);
        
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