<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SincronizadorController;
use App\Http\Controllers\Api\ResultadosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  

// HEALTH CHECK
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// RUTA DE PRUEBA
Route::post('/sincronizar/empresas/test-simple', function(Request $request) {
    try {
        $data = $request->all();
        return response()->json([
            'success' => true,
            'message' => 'Endpoint funciona',
            'keys' => array_keys($data),
            'count' => count($data['empresas'] ?? [])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/debug-import', function(Request $request) {
    try {
        $data = $request->all();
        
        $testInsert = DB::table('empresas')->insert([
            'nit' => '999999999',
            'nombre' => 'EMPRESA TEST',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'received_keys' => array_keys($data),
            'empresas_count' => count($data['empresas'] ?? []),
            'first_empresa' => ($data['empresas'][0] ?? null),
            'test_insert' => $testInsert ? 'OK' : 'FAIL'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// mis endpoints para el script del sincro
Route::post('/sincronizar/archivos', [SincronizadorController::class, 'recibirArchivos']);
Route::get('/sincronizar/pendientes/{nit}', [SincronizadorController::class, 'obtenerPendientes']);
Route::post('/sincronizar/citas/importar', [SincronizadorController::class, 'importarCitas']);
Route::post('/sincronizar/empresas/importar', [SincronizadorController::class, 'importarEmpresas']);

Route::prefix('resultados')->group(function () {
    Route::get('/archivos/{cedula}', [ResultadosController::class, 'listarArchivos']);
    Route::get('/descargar/{cedula}/{archivo}', [ResultadosController::class, 'descargarArchivo']);
    Route::get('/verificar/{cedula}', [ResultadosController::class, 'verificar']);
});