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
                    $existe = DB::table('citas')
                        ->where('consecutivo', $cita['consecutivo'] ?? null)
                        ->where('nit_empresa', $cita['nit_empresa'] ?? null)
                        ->exists();
                    
                    if (!$existe) {
                        DB::table('citas')->insert([
                            'consecutivo' => $cita['consecutivo'] ?? null,
                            'nit_empresa' => $cita['nit_empresa'] ?? null,
                            'documento' => $cita['documento'] ?? null,
                            'cliente' => $cita['cliente'] ?? null,
                            'fecha' => $cita['fecha'] ?? null,
                            'hora' => $cita['hora'] ?? null,
                            'estado' => $cita['estado'] ?? null,
                            'observaciones' => $cita['observaciones'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $insertadas++;
                    }
                } catch (\Exception $e) {
                    $errores++;
                    Log::error('Error insertando cita: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'insertadas' => $insertadas,
                'errores' => $errores
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
                
                // Priorizar CODIGO si existe (aunque sea '1')
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
        //verifico
        try {
            $archivos = $request->input('archivos');
            
            if (empty($archivos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron archivos'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $guardados = 0;
            $existentes = 0;
            $errores = 0;
            
            $porCedula = [];
            foreach ($archivos as $archivoData) {
                $cedula = $archivoData['cedula'];
                if (!isset($porCedula[$cedula])) {
                    $porCedula[$cedula] = [
                        'cita' => $archivoData,
                        'archivos' => []
                    ];
                }
                $porCedula[$cedula]['archivos'][] = $archivoData;
            }
            
            foreach ($porCedula as $cedula => $data) {
                $primerArchivo = $data['cita'];
                
                $cita = CitaRecibida::firstOrCreate(
                    [
                        'cedula' => $cedula,
                        'fecha' => $primerArchivo['fecha_cita']
                    ],
                    [
                        'nombre' => $primerArchivo['nombre'],
                        'mision' => $primerArchivo['mision'] ?? '',
                        'nit_empresa' => $primerArchivo['nit_empresa'],
                        'nombre_empresa' => $primerArchivo['nombre_empresa'] ?? '',
                        'mision_empresa' => $primerArchivo['mision_empresa'] ?? ''
                    ]
                );
                
                if ($cita->carpeta_copiada) {
                    $existentes += count($data['archivos']);
                    continue;
                }
                
                $rutaDestino = storage_path('app/public/RESULTADOS/' . $cedula);
                if (!is_dir($rutaDestino)) {
                    mkdir($rutaDestino, 0777, true);
                }
                
                $archivosGuardados = 0;
                foreach ($data['archivos'] as $archivoData) {
                    $nombreArchivo = $archivoData['nombre_archivo'];
                    $rutaArchivo = $rutaDestino . '/' . $nombreArchivo;
                    
                    if (file_exists($rutaArchivo)) {
                        continue;
                    }
                    
                    $contenidoBase64 = $archivoData['contenido_base64'];
                    $contenidoBinario = base64_decode($contenidoBase64);
                    
                    if ($contenidoBinario === false) {
                        $errores++;
                        continue;
                    }
                    
                    $bytesEscritos = file_put_contents($rutaArchivo, $contenidoBinario);
                    
                    if ($bytesEscritos === false) {
                        $errores++;
                        continue;
                    }
                    
                    $archivosGuardados++;
                    $guardados++;
                }
                
                if ($archivosGuardados > 0) {
                    $cita->update([
                        'ruta_resultados' => 'storage/RESULTADOS/' . $cedula,
                        'carpeta_copiada' => true,
                        'fecha_copia' => now()
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Archivos procesados: {$guardados} nuevos, {$existentes} existentes, {$errores} errores",
                'archivos_guardados' => $guardados,
                'archivos_existentes' => $existentes,
                'errores' => $errores
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en recibirArchivos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar archivos: ' . $e->getMessage()
            ], 500);
        }
    }
}