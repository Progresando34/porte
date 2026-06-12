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