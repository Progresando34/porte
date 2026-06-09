<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CitaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;  // ← AGREGA ESTA LÍNEA

class SincronizadorController extends Controller
{
    public function obtenerPendientes(Request $request, $nit)
    {
        try {
            $fechaCorte = '2026-05-14';
            $prefijosPermitidos = ['a', 's', 'c', 'vis', 'ev'];
            
            $empresa = DB::table('empresas')->where('nit', $nit)->first();
            
            if (!$empresa) {
                Log::warning("No se encontró la empresa con NIT: {$nit}");
                return response()->json([
                    'success' => false,
                    'message' => "No se encontró la empresa con NIT: {$nit}"
                ], 404);
            }
            
            $nombreEmpresa = $empresa->nombre ?? '';
            $misionEmpresaConcatenado = $nombreEmpresa;
            
            Log::info("Procesando NIT: {$nit} - Empresa: {$nombreEmpresa}");
            
            $citasPendientes = DB::table('citas')
                ->where('empresa', $nit)
                ->where('fecha', '>=', $fechaCorte)
                ->orderBy('fecha', 'desc')
                ->get();
            
            $resultado = [];
            
            foreach ($citasPendientes as $cita) {
                $rutaOrigen = "Z:/Saips2/pdf/{$cita->cedula}";
                $archivos = [];
                
                if (is_dir($rutaOrigen)) {
                    $archivosEncontrados = scandir($rutaOrigen);
                    
                    foreach ($archivosEncontrados as $archivo) {
                        if ($archivo === '.' || $archivo === '..') continue;
                        
                        $archivoLower = strtolower($archivo);
                        $prefijoValido = false;
                        
                        foreach ($prefijosPermitidos as $prefijo) {
                            if (strpos($archivoLower, $prefijo) === 0) {
                                $prefijoValido = true;
                                break;
                            }
                        }
                        
                        if (!$prefijoValido) continue;
                        
                        preg_match('/^[a-z]+(\d{8})/', $archivoLower, $matches);
                        if (!$matches || $matches[1] < str_replace('-', '', $fechaCorte)) continue;
                        
                        $archivos[] = [
                            'nombre' => $archivo,
                            'ruta' => $rutaOrigen . '/' . $archivo,
                            'fecha' => $matches[1],
                            'prefijo' => substr($archivo, 0, strcspn($archivo, '0123456789'))
                        ];
                    }
                }
                
                if (!empty($archivos)) {
                    $resultado[] = [
                        'cedula' => $cita->cedula,
                        'nombre' => $cita->nombre,
                        'fecha_cita' => $cita->fecha,
                        'mision' => $cita->mision ?? '',
                        'nit_empresa' => $nit,
                        'nombre_empresa' => $nombreEmpresa,
                        'mision_empresa' => $misionEmpresaConcatenado,  
                        'archivos' => $archivos
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'nit' => $nit,
                'nombre_empresa' => $nombreEmpresa,
                'mision_empresa' => $misionEmpresaConcatenado, 
                'total_colaboradores' => count($resultado),
                'citas' => $resultado
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en obtenerPendientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

public function importarCitas(Request $request)
{
    try {
        $citas = $request->input('citas', []);
        
        if (empty($citas)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay citas para importar'
            ], 400);
        }
        
        $insertadas = 0;
        $errores = 0;
        
        foreach ($citas as $cita) {
            try {
                // Insertar directamente en la tabla citas_recibidas
                DB::table('citas_recibidas')->insert([
                    'cedula' => $cita['cedula'] ?? null,
                    'nombre' => $cita['nombre'] ?? null,
                    'fecha' => $cita['fecha'] ?? null,
                    'mision' => $cita['mision'] ?? null,
                    'nit_empresa' => $cita['empresa'] ?? null,  // Campo 'empresa' del DBF
                    'nombre_empresa' => $cita['nombre_empresa'] ?? null,
                    'mision_empresa' => $cita['mision_empresa'] ?? null,
                    'datos_completos' => json_encode($cita),
                    'recibido_en' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertadas++;
                
            } catch (\Exception $e) {
                $errores++;
                Log::error("Error insertando cita: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'insertadas' => $insertadas,
            'errores' => $errores,
            'total_recibidas' => count($citas)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function importarEmpresas(Request $request)
{
    try {
        Log::info('=== INICIO importarEmpresas ===');
        
        $empresas = $request->input('empresas', []);
        
        Log::info('Cantidad de empresas recibidas: ' . count($empresas));

        if (empty($empresas)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay empresas para importar'
            ], 400);
        }

        // Obtener las columnas que existen en la tabla
        $columnasExistentes = Schema::getColumnListing('empresas');
        
        $insertadas = 0;
        $actualizadas = 0;
        $errores = 0;

        foreach ($empresas as $empresa) {
            try {
                // Limpiar datos - incluir TODOS los campos
                $datos = [];
                foreach ($empresa as $key => $value) {
                    $columnaLimpia = strtolower(trim($key));
                    // Incluir si la columna existe
                    if (in_array($columnaLimpia, $columnasExistentes)) {
                        // Convertir valores vacíos a null
                        if ($value === '' || $value === null) {
                            $datos[$columnaLimpia] = null;
                        } else {
                            $datos[$columnaLimpia] = $value;
                        }
                    }
                }
                
                // Usar CODIGO como identificador principal (incluyendo valor '1')
                $codigo = $datos['codigo'] ?? null;
                $nit = $datos['nit'] ?? null;
                
                // Determinar identificador - INCLUIR VALOR '1'
                $identificador = null;
                $campo = null;
                
             
                if ($codigo !== null && $codigo !== '') {
                    $identificador = $codigo;
                    $campo = 'codigo';
                } 
                // Si no hay CODIGO, usar NIT (aunque sea '1')
                elseif ($nit !== null && $nit !== '') {
                    $identificador = $nit;
                    $campo = 'nit';
                }
                
                // Si no hay identificador, usar una combinación única
                if (!$identificador) {
                    $identificador = 'temp_' . uniqid();
                    $campo = 'codigo';
                    $datos['codigo'] = $identificador;
                }
                
                // Verificar si existe
                $existe = DB::table('empresas')->where($campo, $identificador)->exists();

                $datos['updated_at'] = now();
                
                if (!$existe) {
                    $datos['created_at'] = now();
                    DB::table('empresas')->insert($datos);
                    $insertadas++;
                    Log::info("Empresa INSERTADA: {$campo}={$identificador}");
                } else {
                    DB::table('empresas')
                        ->where($campo, $identificador)
                        ->update($datos);
                    $actualizadas++;
                    Log::info("Empresa ACTUALIZADA: {$campo}={$identificador}");
                }
                
            } catch (\Exception $e) {
                $errores++;
                Log::error("Error en empresa: " . $e->getMessage());
            }
        }

        Log::info("=== FIN importarEmpresas: Insertadas={$insertadas}, Actualizadas={$actualizadas}, Errores={$errores} ===");
        
        return response()->json([
            'success' => true,
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'errores' => $errores,
            'total_recibidas' => count($empresas)
        ]);
        
    } catch (\Exception $e) {
        Log::error('ERROR GENERAL en importarEmpresas: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ], 500);
    }
}

public function recibirArchivos(Request $request)
{
    try {
        $archivos = $request->input('archivos', []);
        
        if (empty($archivos)) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron archivos'
            ], 400);
        }
        
        DB::beginTransaction();
        
        $citasGuardadas = 0;
        $citasOmitidas = 0;
        $errores = 0;
        
        // Fecha de corte para filtrar citas
        $fechaCorte = '2026-05-14';
        
        // Agrupar por cédula para evitar duplicados
        $citasUnicas = [];
        foreach ($archivos as $archivoData) {
            $cedula = $archivoData['cedula'];
            if (!isset($citasUnicas[$cedula])) {
                $citasUnicas[$cedula] = $archivoData;
            }
        }
        
        foreach ($citasUnicas as $cedula => $citaData) {
            try {
                $fechaCita = $citaData['fecha_cita'] ?? null;
                
                // VERIFICAR CONDICIÓN: Solo citas con fecha >= fechaCorte Y cédula no esté vacía
                if ($fechaCita && $fechaCita >= $fechaCorte && $cedula && $cedula !== '') {
                    
                    // Verificar si ya existe para evitar duplicados
                    $existe = CitaRecibida::where('cedula', $cedula)
                        ->where('fecha', $fechaCita)
                        ->exists();
                    
                    if (!$existe) {
                        // Crear la cita en la tabla citas_recibidas
                        $cita = CitaRecibida::create([
                            'cedula' => $cedula,
                            'fecha' => $fechaCita,
                            'nombre' => $citaData['nombre'] ?? '',
                            'mision' => $citaData['mision'] ?? '',
                            'nit_empresa' => $citaData['nit_empresa'] ?? '',
                            'nombre_empresa' => $citaData['nombre_empresa'] ?? '',
                            'mision_empresa' => $citaData['mision_empresa'] ?? '',
                            'carpeta_copiada' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        $citasGuardadas++;
                        Log::info("Cita guardada: {$cedula} - {$fechaCita}");
                    } else {
                        $citasOmitidas++;
                        Log::info("Cita ya existe, omitida: {$cedula} - {$fechaCita}");
                    }
                } else {
                    $citasOmitidas++;
                    Log::info("Cita omitida por condición: {$cedula} - {$fechaCita}");
                }
                
            } catch (\Exception $e) {
                $errores++;
                Log::error("Error guardando cita {$cedula}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Citas guardadas: {$citasGuardadas}, Omitidas: {$citasOmitidas}, Errores: {$errores}",
            'citas_guardadas' => $citasGuardadas,
            'citas_omitidas' => $citasOmitidas,
            'errores' => $errores,
            'total_recibidas' => count($citasUnicas)
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error en recibirArchivos: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error al procesar citas: ' . $e->getMessage()
        ], 500);
    }
}
}