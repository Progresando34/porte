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

        foreach ($empresas as $empresa) {
            try {
                $nit = $empresa['nit'] ?? null;
                
                if (!$nit) {
                    $errores++;
                    continue;
                }
                
                $existe = DB::table('empresas')->where('nit', $nit)->exists();

                $datos = [];
                foreach ($empresa as $key => $value) {
                    if ($value === '' || $value === null) {
                        $datos[$key] = null;
                    } else {
                        $datos[$key] = $value;
                    }
                }

                if (!$existe) {
                    DB::table('empresas')->insert(array_merge(
                        $datos,
                        ['created_at' => now(), 'updated_at' => now()]
                    ));
                    $insertadas++;
                } else {
                    DB::table('empresas')
                        ->where('nit', $nit)
                        ->update(array_merge(
                            $datos,
                            ['updated_at' => now()]
                        ));
                    $actualizadas++;
                }
                
            } catch (\Exception $e) {
                $errores++;
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