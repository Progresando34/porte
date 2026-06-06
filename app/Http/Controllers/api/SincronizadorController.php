<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CitaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizadorController extends Controller
{
    /**
     * El sincronizador consulta qué archivos necesita enviar
     */
    public function obtenerPendientes(Request $request, $nit)
    {
        try {
            $fechaCorte = '2026-05-14';
            $prefijosPermitidos = ['a', 's', 'c', 'vis', 'ev'];
            
            // aqui consulto en base de datos para tonar el nombre y mision de la empresa, solo de las que cumplen con las condiciones
            $empresa = DB::table('empresas')->where('nit', $nit)->first();
            
            if (!$empresa) {
                Log::warning("No se encontró la empresa con NIT: {$nit}");
                return response()->json([
                    'success' => false,
                    'message' => "No se encontró la empresa con NIT: {$nit}"
                ], 404);
            }
            
            $nombreEmpresa = $empresa->nombre ?? '';
            $misionEmpresa = $empresa->mision ?? '';
            
            // CONCATENAR misión y nombre_empresa con un guión
            $misionEmpresaConcatenado = '';
            if ($misionEmpresa || $nombreEmpresa) {
                $misionEmpresaConcatenado = trim($misionEmpresa . ' - ' . $nombreEmpresa);
            }
            
            Log::info("Procesando NIT: {$nit} - Empresa: {$nombreEmpresa}");
            Log::info("Misión empresa concatenada: {$misionEmpresaConcatenado}");
            
            // Usar la conexión por defecto
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
                'mision_empresa' => $misionEmpresaConcatenado,  // ✅ También a nivel raíz
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

        $empresas = $request->input('empresas', []);

        if (empty($empresas)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay empresas para importar'
            ], 400);
        }

        $insertadas = 0;
        $actualizadas = 0;
        $errores = 0;

        foreach ($empresas as $empresa) {

            try {

                $nit = $empresa['nit'] ?? null;

                if (!$nit) {
                    continue;
                }

                $existe = DB::table('empresas')
                    ->where('nit', $nit)
                    ->exists();

                if (!$existe) {

                    DB::table('empresas')->insert(
                        array_merge(
                            $empresa,
                            [
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        )
                    );

                    $insertadas++;

                } else {

                    DB::table('empresas')
                        ->where('nit', $nit)
                        ->update(
                            array_merge(
                                $empresa,
                                [
                                    'updated_at' => now()
                                ]
                            )
                        );

                    $actualizadas++;
                }

            } catch (\Exception $e) {

                $errores++;

                Log::error(
                    'Error importando empresa: ' .
                    $e->getMessage()
                );
            }
        }

        return response()->json([
            'success' => true,
            'insertadas' => $insertadas,
            'actualizadas' => $actualizadas,
            'errores' => $errores
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);

    }
}
    
    /**
     * Recibe archivos en base64 y los guarda como archivos físicos
     */
    public function recibirArchivos(Request $request)
    {
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
            
            // Agrupar por cédula
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
                
                // Buscar o crear la cita
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
                
                // Si ya tiene carpeta copiada, no volver a copiar
                if ($cita->carpeta_copiada) {
                    $existentes += count($data['archivos']);
                    continue;
                }
                
                // Crear la carpeta destino
                $rutaDestino = storage_path('app/public/RESULTADOS/' . $cedula);
                if (!is_dir($rutaDestino)) {
                    mkdir($rutaDestino, 0777, true);
                    Log::info("Carpeta creada: " . $rutaDestino);
                }
                
                // Guardar cada archivo
                $archivosGuardados = 0;
                foreach ($data['archivos'] as $archivoData) {
                    $nombreArchivo = $archivoData['nombre_archivo'];
                    $rutaArchivo = $rutaDestino . '/' . $nombreArchivo;
                    
                    // Verificar si ya existe
                    if (file_exists($rutaArchivo)) {
                        Log::info("Archivo ya existe: " . $nombreArchivo);
                        continue;
                    }
                    
                    // ✅ IMPORTANTE: Decodificar base64 a binario
                    $contenidoBase64 = $archivoData['contenido_base64'];
                    $contenidoBinario = base64_decode($contenidoBase64);
                    
                    if ($contenidoBinario === false) {
                        Log::error("Error al decodificar base64: " . $nombreArchivo);
                        $errores++;
                        continue;
                    }
                    
                    // Guardar el archivo binario
                    $bytesEscritos = file_put_contents($rutaArchivo, $contenidoBinario);
                    
                    if ($bytesEscritos === false) {
                        Log::error("Error al guardar archivo: " . $rutaArchivo);
                        $errores++;
                        continue;
                    }
                    
                    Log::info("✅ Archivo guardado: {$nombreArchivo} - Tamaño: {$bytesEscritos} bytes");
                    $archivosGuardados++;
                    $guardados++;
                }
                
                if ($archivosGuardados > 0) {
                    $cita->update([
                        'ruta_resultados' => 'storage/RESULTADOS/' . $cedula,
                        'carpeta_copiada' => true,
                        'fecha_copia' => now()
                    ]);
                    Log::info("✅ Cita actualizada: {$cedula} - {$archivosGuardados} archivos");
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
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar archivos: ' . $e->getMessage()
            ], 500);
        }
    }
}