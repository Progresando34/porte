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
        
        // Solo devolver las citas, sin buscar archivos
        $citasPendientes = DB::table('citas_recibidas')
            ->where('nit_empresa', $nit)
            ->where('fecha', '>=', $fechaCorte)
            ->where('carpeta_copiada', false)
            ->orderBy('fecha', 'desc')
            ->get();
        
        $resultado = [];
        
        foreach ($citasPendientes as $cita) {
            $resultado[] = [
                'cedula' => $cita->cedula,
                'nombre' => $cita->nombre,
                'fecha_cita' => $cita->fecha,
                'mision' => $cita->mision ?? '',
                'nit_empresa' => $nit,
                'nombre_empresa' => $cita->nombre_empresa ?? '',
                'mision_empresa' => $cita->mision_empresa ?? '',
                'archivos' => []  
            ];
        }
        
        return response()->json([
            'success' => true,
            'nit' => $nit,
            'total_colaboradores' => count($resultado),
            'citas' => $resultado
        ]);
        
    } catch (\Exception $e) {
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
        
        $archivosGuardados = 0;
        $errores = 0;
        
        foreach ($archivos as $archivoData) {
            try {
                $cedula = $archivoData['cedula'];
                $nombreArchivo = $archivoData['nombre_archivo'];
                $contenidoBase64 = $archivoData['contenido_base64'];
                
                // Extraer prefijo y fecha del nombre del archivo
                // Formato: vis20260514.pdf
                preg_match('/^([a-z]+)(\d{8})\.pdf$/i', $nombreArchivo, $matches);
                
                if (count($matches) >= 3) {
                    $prefijo = strtoupper($matches[1]);
                    $fechaArchivo = $matches[2];
                    
                    // Validar fecha >= 20260514
                    if ($fechaArchivo < '20260514') {
                        Log::info("Archivo omitido por fecha: {$nombreArchivo} (fecha: {$fechaArchivo})");
                        continue;
                    }
                    
                    // Validar prefijos permitidos
                    $prefijosPermitidos = ['A', 'C', 'EV', 'S', 'VIS'];
                    if (!in_array($prefijo, $prefijosPermitidos)) {
                        Log::info("Archivo omitido por prefijo: {$nombreArchivo} (prefijo: {$prefijo})");
                        continue;
                    }
                }
                
                // Decodificar contenido
                $contenido = base64_decode($contenidoBase64);
                
                if ($contenido === false) {
                    throw new \Exception('Error al decodificar archivo');
                }
                
                // Crear carpeta
                $carpeta = storage_path('app/public/RESULTADOS/' . $cedula);
                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }
                
                // Guardar archivo
                $rutaCompleta = $carpeta . '/' . $nombreArchivo;
                file_put_contents($rutaCompleta, $contenido);
                
                // Actualizar o crear cita
                CitaRecibida::updateOrCreate(
                    [
                        'cedula' => $cedula,
                        'fecha' => $archivoData['fecha_cita'] ?? null
                    ],
                    [
                        'nombre' => $archivoData['nombre'] ?? '',
                        'mision' => $archivoData['mision'] ?? '',
                        'nit_empresa' => $archivoData['nit_empresa'] ?? '',
                        'nombre_empresa' => $archivoData['nombre_empresa'] ?? '',
                        'mision_empresa' => $archivoData['mision_empresa'] ?? '',
                        'carpeta_copiada' => true,
                        'updated_at' => now()
                    ]
                );
                
                $archivosGuardados++;
                Log::info("Archivo guardado: {$cedula}/{$nombreArchivo}");
                
            } catch (\Exception $e) {
                $errores++;
                Log::error("Error guardando archivo: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'archivos_guardados' => $archivosGuardados,
            'errores' => $errores,
            'total_recibidos' => count($archivos)
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error en recibirArchivos: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error al procesar archivos: ' . $e->getMessage()
        ], 500);
    }
}


}