
<?php

// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SincronizadorController;
use App\Http\Controllers\Api\ResultadosController;

// HEALTH CHECK
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});


// mis endpoints para el script del sincro


//  El sincro envía archivos en base64
Route::post('/sincronizar/archivos', [SincronizadorController::class, 'recibirArchivos']);

//  Endpoint para que el sincronizador consulte que citas se enviaran 
Route::get('/sincronizar/pendientes/{nit}', [SincronizadorController::class, 'obtenerPendientes']);


// estos enpoints son para la consulta de certificados


Route::prefix('resultados')->group(function () {
    // Listar archivos de una cédula (reconstruidos desde base64)
    Route::get('/archivos/{cedula}', [ResultadosController::class, 'listarArchivos']);
    
    // Descargar archivo específico (reconstruido desde base64)
    Route::get('/descargar/{cedula}/{archivo}', [ResultadosController::class, 'descargarArchivo']);
    
    // Verificar si existe información
    Route::get('/verificar/{cedula}', [ResultadosController::class, 'verificar']);
});