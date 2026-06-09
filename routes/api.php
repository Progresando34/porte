<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SincronizadorController;
use App\Http\Controllers\api\ResultadosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // ← AGREGA ESTA LÍNEA


Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::post('/importar-citas', function(Request $request) {
    $citas = $request->input('citas', []);
    $insertadas = 0;
    $errores = [];
    
    foreach ($citas as $index => $cita) {
        try {
            // Intentar insertar con los datos que lleguen
            $resultado = DB::table('citas_recibidas')->insert([
                'cedula' => $cita['cedula'] ?? null,
                'nombre' => $cita['nombre'] ?? null,
                'fecha' => $cita['fecha'] ?? null,
                'nit_empresa' => $cita['empresa'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if ($resultado) {
                $insertadas++;
            } else {
                $errores[] = "Cita $index: Falló la inserción sin excepción";
            }
        } catch (\Exception $e) {
            $errores[] = "Cita $index: " . $e->getMessage();
        }
    }
    
    return response()->json([
        'insertadas' => $insertadas,
        'errores' => $errores,
        'total' => count($citas)
    ]);
});

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