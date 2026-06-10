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
            $resultado = DB::table('citas_recibidas')->insert([
                'cedula' => $cita['cedula'] ?? null,
                'nombre' => $cita['nombre'] ?? '',
                'fecha' => $cita['fecha'] ?? null,
                'nit_empresa' => $cita['empresa'] ?? null,
                'nombre_empresa' => $cita['nombre_empresa'] ?? '',  // ← CAMPO REQUERIDO
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if ($resultado) {
                $insertadas++;
            } else {
                $errores[] = "Cita $index: Falló la inserción";
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