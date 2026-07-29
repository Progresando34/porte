<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CertificadosArmasController extends Controller
{
    /**
     * Importar certificados desde Excel
     */
    public function importarDesdeExcel(Request $request)
    {
        try {
            Log::info('=== INICIO IMPORTACIÓN CERTIFICADOS ARMAS ===');
            
            $registros = $request->input('registros', []);
            $totalRecibidos = count($registros);
            
            Log::info("Total registros recibidos: {$totalRecibidos}");
            
            if (empty($registros)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron registros para importar'
                ], 400);
            }
            
            $insertados = 0;
            $omitidos = 0;
            $errores = 0;
            $detalles = [];
            
            foreach ($registros as $index => $registro) {
                try {
                    // Validar datos requeridos
                    $validator = Validator::make($registro, [
                        'fecha' => 'required|date',
                        'cedula' => 'required|string',
                        'nombre' => 'required|string',
                        'resultado_apto' => 'required|in:APTO,NO APTO,APLAZADO'
                    ]);
                    
                    if ($validator->fails()) {
                        $errores++;
                        $detalles[] = "Registro {$index}: Error de validación - " . implode(', ', $validator->errors()->all());
                        Log::warning("Registro {$index}: Validación fallida", $validator->errors()->toArray());
                        continue;
                    }
                    
                    // VERIFICAR SI YA EXISTE (misma cédula y misma fecha)
                    $existe = DB::table('certificados_armas')
                        ->where('cedula', $registro['cedula'])
                        ->where('fecha_expedicion', $registro['fecha'])
                        ->exists();
                    
                    if ($existe) {
                        $omitidos++;
                        $detalles[] = "Registro {$index}: Omitido - Ya existe (Cédula: {$registro['cedula']}, Fecha: {$registro['fecha']})";
                        Log::info("Registro omitido - ya existe: Cédula {$registro['cedula']}, Fecha {$registro['fecha']}");
                        continue;
                    }
                    
                    // 🔥 CONVERTIR APTO/NO APTO a booleano (tinyint)
                    // APTO = 1, NO APTO = 0, APLAZADO = 2
if ($registro['resultado_apto'] === 'APTO') {
    $resultado_booleano = 1;
} elseif ($registro['resultado_apto'] === 'NO APTO') {
    $resultado_booleano = 0;
} else { // APLAZADO
    $resultado_booleano = 2;
}
                    
                    // PREPARAR DATOS PARA INSERCIÓN
                    $datos = [
                        'resultado_apto' => $resultado_booleano, // 1 = APTO, 0 = NO APTO
                        'direccion_ips' => 'CL 21 A 0 B 75 BRR EL ROSAL',
                        'sede_ips' => 'Sede Cúcuta',
                        'nombre' => $registro['nombre'],
                        'cedula' => $registro['cedula'],
                        'archivo_certificado' => null,
                        'fecha_expedicion' => $registro['fecha'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    // INSERTAR REGISTRO
                    $id = DB::table('certificados_armas')->insertGetId($datos);
                    $insertados++;
                    
                    $detalles[] = "Registro {$index}: INSERTADO - ID: {$id}, Cédula: {$registro['cedula']}, Resultado: {$registro['resultado_apto']}";
                    Log::info("Registro insertado - ID: {$id}, Cédula: {$registro['cedula']}, Resultado: {$registro['resultado_apto']}");
                    
                } catch (\Exception $e) {
                    $errores++;
                    $detalles[] = "Registro {$index}: ERROR - " . $e->getMessage();
                    Log::error("Error en registro {$index}: " . $e->getMessage(), [
                        'registro' => $registro ?? null
                    ]);
                }
            }
            
            // RESUMEN FINAL
            Log::info("=== FIN IMPORTACIÓN CERTIFICADOS ARMAS ===");
            Log::info("Total recibidos: {$totalRecibidos}");
            Log::info("Insertados: {$insertados}");
            Log::info("Omitidos (duplicados): {$omitidos}");
            Log::info("Errores: {$errores}");
            
            return response()->json([
                'success' => true,
                'total_recibidos' => $totalRecibidos,
                'insertados' => $insertados,
                'omitidos' => $omitidos,
                'errores' => $errores,
                'detalles' => $detalles
            ]);
            
        } catch (\Exception $e) {
            Log::error('ERROR GENERAL en importarDesdeExcel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si un registro ya existe
     */
    public function verificarExistencia(Request $request)
    {
        try {
            $cedula = $request->input('cedula');
            $fecha = $request->input('fecha');
            
            if (!$cedula || !$fecha) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cédula y fecha son requeridos'
                ], 400);
            }
            
            $existe = DB::table('certificados_armas')
                ->where('cedula', $cedula)
                ->where('fecha_expedicion', $fecha)
                ->exists();
            
            return response()->json([
                'success' => true,
                'existe' => $existe,
                'cedula' => $cedula,
                'fecha' => $fecha
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener todos los registros (para verificar)
     */
public function listarTodos()
{
    try {
        $registros = DB::table('certificados_armas')
            ->orderBy('id', 'asc')  // Ordenar por ID ascendente
            ->get();
        
        // Convertir resultado_apto de vuelta a texto para mejor visualización
        $registrosFormateados = $registros->map(function($item) {
            // 🔥 CORREGIDO: Ahora soporta APTO, NO APTO y APLAZADO
            if ($item->resultado_apto == 1) {
                $item->resultado_apto_texto = 'APTO';
            } elseif ($item->resultado_apto == 2) {
                $item->resultado_apto_texto = 'APLAZADO';
            } else {
                $item->resultado_apto_texto = 'NO APTO';
            }
            return $item;
        });
        
        return response()->json([
            'success' => true,
            'total' => count($registros),
            'registros' => $registrosFormateados
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


    /**
 * Eliminar TODOS los registros y reiniciar auto-increment (TRUNCATE)
 * ⚠️ ESTA OPERACIÓN ES PERMANENTE Y NO TIENE MARCHA ATRÁS
 */
public function truncarTabla(Request $request)
{
    try {
        // Verificar autenticación (opcional pero recomendado)
        // $request->validate(['token' => 'required|string']);
        
        // Obtener conteo antes de truncar
        $total = DB::table('certificados_armas')->count();
        
        Log::warning('⚠️ INTENTO DE TRUNCATE - Usuario: ' . ($request->ip() ?? 'desconocido'));
        Log::warning("Registros a eliminar: {$total}");
        
        if ($total === 0) {
            return response()->json([
                'success' => true,
                'message' => 'La tabla ya está vacía. No se requiere acción.',
                'registros_eliminados' => 0,
                'auto_increment_reiniciado' => true
            ]);
        }
        
        // 🔥 TRUNCATE: Elimina todos los registros Y reinicia el auto-increment
        DB::statement('TRUNCATE TABLE certificados_armas');
        
        Log::info('✅ TRUNCATE ejecutado correctamente en certificados_armas');
        Log::info("Registros eliminados: {$total}");
        
        return response()->json([
            'success' => true,
            'message' => "Tabla vaciada correctamente. Se eliminaron {$total} registros y el auto-increment fue reiniciado.",
            'registros_eliminados' => $total,
            'auto_increment_reiniciado' => true,
            'tabla' => 'certificados_armas',
            'accion' => 'TRUNCATE'
        ]);
        
    } catch (\Exception $e) {
        Log::error('ERROR en truncarTabla: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error al truncar la tabla: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * MODIFICAR LA ESTRUCTURA DE LA TABLA PARA ACEPTAR APLAZADO (2)
 * ⚠️ ESTA OPERACIÓN MODIFICA LA ESTRUCTURA DE LA TABLA
 */
public function modificarColumnaResultado(Request $request)
{
    try {
        Log::warning('⚠️ MODIFICANDO COLUMNA resultado_apto - IP: ' . $request->ip());
        
        // Verificar el tipo actual de la columna
        $columnInfo = DB::select("SHOW COLUMNS FROM certificados_armas LIKE 'resultado_apto'");
        
        if (empty($columnInfo)) {
            return response()->json([
                'success' => false,
                'message' => 'La columna resultado_apto no existe en la tabla'
            ], 404);
        }
        
        $tipoActual = $columnInfo[0]->Type ?? 'unknown';
        Log::info("Tipo actual de la columna: {$tipoActual}");
        
        // Si ya es TINYINT(2) o superior, no hacer nada
        if (strpos($tipoActual, 'tinyint(2)') !== false || strpos($tipoActual, 'tinyint(3)') !== false) {
            return response()->json([
                'success' => true,
                'message' => "La columna ya soporta valores hasta 2. Tipo actual: {$tipoActual}",
                'tipo_actual' => $tipoActual,
                'ya_modificada' => true
            ]);
        }
        
        // MODIFICAR LA COLUMNA
        DB::statement('ALTER TABLE certificados_armas MODIFY resultado_apto TINYINT(2) NOT NULL DEFAULT 0');
        
        Log::info('✅ Columna resultado_apto modificada a TINYINT(2)');
        
        // Verificar después de la modificación
        $columnInfoNuevo = DB::select("SHOW COLUMNS FROM certificados_armas LIKE 'resultado_apto'");
        $tipoNuevo = $columnInfoNuevo[0]->Type ?? 'unknown';
        
        return response()->json([
            'success' => true,
            'message' => 'Columna resultado_apto modificada exitosamente de TINYINT(1) a TINYINT(2)',
            'tipo_anterior' => $tipoActual,
            'tipo_nuevo' => $tipoNuevo,
            'ya_modificada' => false
        ]);
        
    } catch (\Exception $e) {
        Log::error('ERROR modificando columna: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error al modificar la columna: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Verificar el estado actual de la tabla
 */
public function verificarEstado(Request $request)
{
    try {
        $total = DB::table('certificados_armas')->count();
        
        // Obtener el último ID insertado (si hay registros)
        $ultimo_id = null;
        if ($total > 0) {
            $ultimo = DB::table('certificados_armas')
                ->orderBy('id', 'desc')
                ->first();
            $ultimo_id = $ultimo ? $ultimo->id : null;
        }
        
        return response()->json([
            'success' => true,
            'total_registros' => $total,
            'ultimo_id' => $ultimo_id,
            'tabla_vacia' => $total === 0,
            'auto_increment_actual' => $total > 0 ? $ultimo_id + 1 : 1
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/**
 * REINICIAR TABLA COMPLETAMENTE (DELETE + ALTER)
 * ⚠️ ESTO ELIMINA TODOS LOS REGISTROS Y REINICIA IDs
 */
public function reiniciarCompleto(Request $request)
{
    try {
        // Contar registros actuales
        $total = DB::table('certificados_armas')->count();
        
        Log::warning('⚠️ REINICIO COMPLETO - IP: ' . $request->ip());
        Log::warning("Registros a eliminar: {$total}");
        
        if ($total === 0) {
            return response()->json([
                'success' => true,
                'message' => 'La tabla ya está vacía.',
                'registros_eliminados' => 0
            ]);
        }
        
        // 1. ELIMINAR TODOS LOS REGISTROS
        DB::table('certificados_armas')->delete();
        
        // 2. REINICIAR AUTO_INCREMENT A 1
        DB::statement('ALTER TABLE certificados_armas AUTO_INCREMENT = 1');
        
        Log::info('✅ Tabla reiniciada - Registros eliminados: ' . $total);
        
        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$total} registros. Auto-increment reiniciado a 1.",
            'registros_eliminados' => $total,
            'auto_increment_reiniciado' => true
        ]);
        
    } catch (\Exception $e) {
        Log::error('ERROR reiniciarCompleto: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

public function corregirSedeIps(Request $request)
{
    try {
        Log::info('=== INICIO CORRECCIÓN SEDE IPS ===');
        
        // 🔥 CONTAR los que tienen "Sede Cucuta" (sin tilde)
        $totalAfectados = DB::table('certificados_armas')
            ->where('sede_ips', 'Sede Cucuta')
            ->count();
        
        Log::info("Registros a corregir: {$totalAfectados}");
        
        if ($totalAfectados === 0) {
            return response()->json([
                'success' => true,
                'message' => 'No hay registros con "Sede Cucuta" para corregir.',
                'actualizados' => 0
            ]);
        }
        
        // 🔥 ACTUALIZAR SOLO sede_ips de "Sede Cucuta" a "Sede Cúcuta"
        $actualizados = DB::table('certificados_armas')
            ->where('sede_ips', 'Sede Cucuta')
            ->update([
                'sede_ips' => 'Sede Cúcuta',
                'updated_at' => now()
            ]);
        
        Log::info("✅ Registros actualizados: {$actualizados}");
        
        return response()->json([
            'success' => true,
            'message' => "Se actualizaron {$actualizados} registros de 'Sede Cucuta' a 'Sede Cúcuta'.",
            'actualizados' => $actualizados
        ]);
        
    } catch (\Exception $e) {
        Log::error('ERROR corregirSedeIps: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


}