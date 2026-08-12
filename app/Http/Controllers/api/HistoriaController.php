<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HistoriaController extends Controller
{
    /**
     * Importar registros de historia clínica desde 2025
     * 🔥 PREVIENE DUPLICADOS usando CEDULA + FECHA
     */
    public function importar(Request $request)
    {
        try {
            $registros = $request->input('registros', []);
            
            if (empty($registros)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay registros para importar'
                ], 400);
            }
            
            $insertados = 0;
            $actualizados = 0;
            $omitidos = 0;
            $errores = 0;
            $detalles = [];
            
            // Fecha mínima (2025-01-01)
            $fechaMinima = '2025-01-01';
            
            foreach ($registros as $index => $registro) {
                try {
                    // Validar fecha
                    $fecha = $registro['FECHA'] ?? null;
                    if (!$fecha || $fecha < $fechaMinima) {
                        $omitidos++;
                        $detalles[] = "Registro {$index}: fecha {$fecha} anterior a 2025";
                        continue;
                    }
                    
                    $cedula = $registro['CEDULA'] ?? null;
                    if (!$cedula) {
                        $errores++;
                        $detalles[] = "Registro {$index}: sin cédula";
                        continue;
                    }
                    
                    // 🔥 VERIFICAR DUPLICADO (misma cédula y misma fecha)
                    $existe = DB::table('datoshistoria')
                        ->where('CEDULA', $cedula)
                        ->where('FECHA', $fecha)
                        ->exists();
                    
                    if ($existe) {
                        // 🔥 ACTUALIZAR (no duplicar)
                        DB::table('datoshistoria')
                            ->where('CEDULA', $cedula)
                            ->where('FECHA', $fecha)
                            ->update([
                                'TIPODOC' => $registro['TIPODOC'] ?? null,
                                'DE' => $registro['DE'] ?? null,
                                'EXAMEN' => $registro['EXAMEN'] ?? null,
                                'TRABALTU' => $this->parseBoolean($registro['TRABALTU'] ?? null),
                                'ESPACIOS' => $this->parseBoolean($registro['ESPACIOS'] ?? null),
                                'EOSTEO' => $this->parseBoolean($registro['EOSTEO'] ?? null),
                                'ENFASIS' => $registro['ENFASIS'] ?? null,
                                'EMPRESA' => $registro['EMPRESA'] ?? null,
                                'MISION' => $registro['MISION'] ?? null,
                                'NOMBRE' => $registro['NOMBRE'] ?? null,
                                'CARGO' => $registro['CARGO'] ?? null,
                                'CVALINEA' => $registro['CVALINEA'] ?? null,
                                'ESPIROME' => $registro['ESPIROME'] ?? null,
                                'AUDIOME' => $registro['AUDIOME'] ?? null,
                                'EVOZ' => $registro['EVOZ'] ?? null,
                                'OPTOMETRA' => $registro['OPTOMETRA'] ?? null,
                                'VISIOMETRA' => $registro['VISIOMETRA'] ?? null,
                                'SICOLOGIA' => $registro['SICOLOGIA'] ?? null,
                                'RAYOSXTO' => $registro['RAYOSXTO'] ?? null,
                                'RAYOSXCV' => $registro['RAYOSXCV'] ?? null,
                                'EKG' => $registro['EKG'] ?? null,
                                'PSICOSEN' => $registro['PSICOSEN'] ?? null,
                                'CMOTRIZ' => $registro['CMOTRIZ'] ?? null,
                                'LABORATO' => $registro['LABORATO'] ?? null,
                                'LABORATOC' => $registro['LABORATOC'] ?? null,
                                'updated_at' => now()
                            ]);
                        $actualizados++;
                        $detalles[] = "Actualizado: {$cedula} - {$fecha}";
                    } else {
                        // 🔥 INSERTAR NUEVO
                        DB::table('datoshistoria')->insert([
                            'TIPODOC' => $registro['TIPODOC'] ?? null,
                            'CEDULA' => $cedula,
                            'DE' => $registro['DE'] ?? null,
                            'FECHA' => $fecha,
                            'EXAMEN' => $registro['EXAMEN'] ?? null,
                            'TRABALTU' => $this->parseBoolean($registro['TRABALTU'] ?? null),
                            'ESPACIOS' => $this->parseBoolean($registro['ESPACIOS'] ?? null),
                            'EOSTEO' => $this->parseBoolean($registro['EOSTEO'] ?? null),
                            'ENFASIS' => $registro['ENFASIS'] ?? null,
                            'EMPRESA' => $registro['EMPRESA'] ?? null,
                            'MISION' => $registro['MISION'] ?? null,
                            'NOMBRE' => $registro['NOMBRE'] ?? null,
                            'CARGO' => $registro['CARGO'] ?? null,
                            'CVALINEA' => $registro['CVALINEA'] ?? null,
                            'ESPIROME' => $registro['ESPIROME'] ?? null,
                            'AUDIOME' => $registro['AUDIOME'] ?? null,
                            'EVOZ' => $registro['EVOZ'] ?? null,
                            'OPTOMETRA' => $registro['OPTOMETRA'] ?? null,
                            'VISIOMETRA' => $registro['VISIOMETRA'] ?? null,
                            'SICOLOGIA' => $registro['SICOLOGIA'] ?? null,
                            'RAYOSXTO' => $registro['RAYOSXTO'] ?? null,
                            'RAYOSXCV' => $registro['RAYOSXCV'] ?? null,
                            'EKG' => $registro['EKG'] ?? null,
                            'PSICOSEN' => $registro['PSICOSEN'] ?? null,
                            'CMOTRIZ' => $registro['CMOTRIZ'] ?? null,
                            'LABORATO' => $registro['LABORATO'] ?? null,
                            'LABORATOC' => $registro['LABORATOC'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $insertados++;
                        $detalles[] = "Insertado: {$cedula} - {$fecha}";
                    }
                    
                } catch (\Exception $e) {
                    $errores++;
                    Log::error("Error en registro {$index}: " . $e->getMessage());
                    $detalles[] = "Error en registro {$index}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'insertados' => $insertados,
                'actualizados' => $actualizados,
                'omitidos' => $omitidos,
                'errores' => $errores,
                'total_recibidos' => count($registros),
                'detalles' => $detalles
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en importar historia: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si un registro existe (para evitar duplicados)
     */
    public function verificarExistencia(Request $request)
    {
        try {
            $cedula = $request->input('cedula');
            $fecha = $request->input('fecha');
            
            if (!$cedula || !$fecha) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cédula y fecha son requeridas'
                ], 400);
            }
            
            $existe = DB::table('datoshistoria')
                ->where('CEDULA', $cedula)
                ->where('FECHA', $fecha)
                ->exists();
            
            // Si existe, obtener el registro
            $registro = null;
            if ($existe) {
                $registro = DB::table('datoshistoria')
                    ->where('CEDULA', $cedula)
                    ->where('FECHA', $fecha)
                    ->first();
            }
            
            return response()->json([
                'success' => true,
                'existe' => $existe,
                'registro' => $registro
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Resumen de importaciones
     */
    public function resumen()
    {
        try {
            $total = DB::table('datoshistoria')->count();
            $desde2025 = DB::table('datoshistoria')
                ->where('FECHA', '>=', '2025-01-01')
                ->count();
            $ultimaFecha = DB::table('datoshistoria')
                ->max('FECHA');
            $ultimaActualizacion = DB::table('datoshistoria')
                ->max('updated_at');
            
            return response()->json([
                'success' => true,
                'total_registros' => $total,
                'registros_desde_2025' => $desde2025,
                'ultima_fecha' => $ultimaFecha,
                'ultima_actualizacion' => $ultimaActualizacion
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Parsear valores booleanos de DBF
     */
    private function parseBoolean($valor)
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_string($valor)) {
            $valor = strtoupper(trim($valor));
            return in_array($valor, ['T', 'TRUE', '1', 'YES', 'SI']);
        }
        if (is_numeric($valor)) {
            return (bool) $valor;
        }
        return null;
    }
}