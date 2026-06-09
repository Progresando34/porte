<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SincronizadorController;
use App\Http\Controllers\api\ResultadosController;
use Illuminate\Http\Request;

// HEALTH CHECK
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::post('/importar-citas', function(Request $request) {
    $citas = $request->input('citas', []);
    $insertadas = 0;
    
    foreach ($citas as $cita) {
        try {
            DB::table('citas_recibidas')->insert([
                'cedula' => $cita['cedula'],
                'nombre' => $cita['nombre'],
                'fecha' => $cita['fecha'],
                'nit_empresa' => $cita['empresa'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $insertadas++;
        } catch (\Exception $e) {}
    }
    
    return response()->json(['insertadas' => $insertadas]);
});

// ENDPOINTS DEL SINCRONIZADOR - USANDO EL CONTROLADOR
Route::post('/sincronizar/archivos', [SincronizadorController::class, 'recibirArchivos']);
Route::get('/sincronizar/pendientes/{nit}', [SincronizadorController::class, 'obtenerPendientes']);
Route::post('/sincronizar/citas/importar', [SincronizadorController::class, 'importarCitas']);
Route::post('/sincronizar/empresas/importar', [SincronizadorController::class, 'importarEmpresas']);

// ENDPOINTS DE RESULTADOS
Route::prefix('resultados')->group(function () {
    Route::get('/archivos/{cedula}', [ResultadosController::class, 'listarArchivos']);
    Route::get('/descargar/{cedula}/{archivo}', [ResultadosController::class, 'descargarArchivo']);
    Route::get('/verificar/{cedula}', [ResultadosController::class, 'verificar']);
});