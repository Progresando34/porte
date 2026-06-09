<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SincronizadorController;
use App\Http\Controllers\api\ResultadosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // ← AGREGA ESTA LÍNEA
use Illuminate\Support\Facades\Artisan;  // ← AGREGA ESTA LÍNEA


Route::post('/importar-citas', function(Request $request) {
    $citas = $request->input('citas', []);
    $insertadas = 0;
    $errores_detalle = [];
    
    foreach ($citas as $index => $cita) {
        try {
            // Verificar que los datos necesarios existen
            if (empty($cita['cedula']) || empty($cita['empresa']) || empty($cita['fecha'])) {
                $errores_detalle[] = "Cita $index: Datos incompletos - cedula: {$cita['cedula']}, empresa: {$cita['empresa']}, fecha: {$cita['fecha']}";
                continue;
            }
            
            DB::table('citas_recibidas')->insert([
                'cedula' => $cita['cedula'],
                'nombre' => $cita['nombre'] ?? '',
                'fecha' => $cita['fecha'],
                'nit_empresa' => $cita['empresa'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $insertadas++;
        } catch (\Exception $e) {
            $errores_detalle[] = "Cita $index: " . $e->getMessage();
        }
    }
    
    return response()->json([
        'insertadas' => $insertadas,
        'errores' => $errores_detalle,
        'total' => count($citas)
    ]);
});



Route::post('/ejecutar-importar-citas', function() {
    try {
        // Ejecutar el comando artesano
        $exitCode = Artisan::call('importar:dbf --solo-citas');
        $output = Artisan::output();
        
        return response()->json([
            'success' => $exitCode === 0,
            'output' => $output,
            'message' => $exitCode === 0 ? 'Importación completada' : 'Error en la importación'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
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