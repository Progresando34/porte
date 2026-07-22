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
            $archivosOmitidos = 0;
            $errores = 0;
            $detalles = [];
            
            foreach ($archivos as $index => $archivoData) {
                try {
                    $cedula = $archivoData['cedula'] ?? null;
                    $nombreArchivo = $archivoData['nombre_archivo'] ?? null;
                    
                    if (!$cedula || !$nombreArchivo) {
                        $errores++;
                        Log::warning("Archivo {$index}: faltan datos");
                        continue;
                    }
                    
                    // 🔥 VERIFICAR SI EL ARCHIVO YA EXISTE EN EL SISTEMA DE ARCHIVOS
                    $rutaCompleta = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $nombreArchivo);
                    
                    if (file_exists($rutaCompleta)) {
                        $archivosOmitidos++;
                        Log::info("Archivo ya existe en disco: {$cedula}/{$nombreArchivo}");
                        $detalles[] = "Omitido (ya existe): {$nombreArchivo}";
                        continue;
                    }
                    
                    // Extraer prefijo y fecha del nombre del archivo
                    preg_match('/^([a-z]+)(\d{8})\.pdf$/i', $nombreArchivo, $matches);
                    
                    if (count($matches) >= 3) {
                        $prefijo = strtoupper($matches[1]);
                        $fechaArchivo = $matches[2];
                        
                        // Fecha mínima
                        if ($fechaArchivo < '20260514') {
                            Log::info("Archivo omitido por fecha: {$nombreArchivo}");
                            $archivosOmitidos++;
                            continue;
                        }
                        
                        // Validar prefijos permitidos
                        $prefijosPermitidos = ['A', 'C', 'EV', 'VF', 'VIS', 'H', 'VF'];
                        if (!in_array($prefijo, $prefijosPermitidos)) {
                            Log::info("Archivo omitido por prefijo: {$nombreArchivo}");
                            $archivosOmitidos++;
                            continue;
                        }
                    }
                    
                    // Decodificar contenido
                    $contenidoBase64 = $archivoData['contenido_base64'] ?? null;
                    if (!$contenidoBase64) {
                        $errores++;
                        Log::warning("Archivo {$index}: sin contenido base64");
                        continue;
                    }
                    
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
                    Log::error("Error guardando archivo {$index}: " . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'archivos_guardados' => $archivosGuardados,
                'archivos_omitidos' => $archivosOmitidos,
                'errores' => $errores,
                'total_recibidos' => count($archivos),
                'detalles' => $detalles
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


