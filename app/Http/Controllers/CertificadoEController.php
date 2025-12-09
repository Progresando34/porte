<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificadoEController extends Controller
{
    public function index()
    {
        return view('certificados_e.index');
    }

    public function buscar(Request $request)
    {
        $cedulasInput = $request->input('cedulas_multiple', []);
        $cedulaSimple = $request->input('cedula', '');
        $resultados = [];

        Log::info("=== INICIO BÚSQUEDA CERTIFICADOS ===");
        Log::info("Cédula simple: {$cedulaSimple}");

        // Obtener usuario autenticado
        $usuario = auth()->user();
        
        if (!$usuario) {
            Log::warning("Usuario no autenticado");
            return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
        }
        
        $prefijosUsuario = $usuario->obtenerPrefijosArray();
        
        // Si es admin, no filtrar (tiene acceso a todo)
        $filtrarPrefijos = true;
        if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
            $filtrarPrefijos = false;
            Log::info("Usuario es administrador, sin restricciones de prefijos");
        }
        
        Log::info("Usuario ID: {$usuario->id}, Perfil: {$usuario->profile_id}");
        Log::info("Prefijos del usuario: " . json_encode($prefijosUsuario));
        Log::info("¿Filtrar por prefijos?: " . ($filtrarPrefijos ? 'Sí' : 'No'));

        // Procesar una sola cédula
        if (!empty($cedulaSimple)) {
            $cedula = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
            
            if (empty($cedula)) {
                Log::warning("Cédula vacía después de limpiar");
                return back()->with('mensaje', 'Cédula inválida');
            }
            
            Log::info("Buscando cédula: {$cedula}");
            
            try {
                // Construir consulta base
                $query = DB::table('documentos_empresas')
                    ->where('cedula', $cedula);
                
                // Si hay que filtrar por prefijos y el usuario tiene prefijos asignados
                if ($filtrarPrefijos && !empty($prefijosUsuario)) {
                    // ✅ SOLO usar la columna 'filename' que sí existe
                    $query->where(function($q) use ($prefijosUsuario) {
                        foreach ($prefijosUsuario as $prefijo) {
                            $q->orWhere('filename', 'LIKE', $prefijo . '%');
                        }
                    });
                } elseif ($filtrarPrefijos && empty($prefijosUsuario)) {
                    // Si no es admin y no tiene prefijos, no mostrar nada
                    Log::info("Usuario no es admin y no tiene prefijos asignados. Sin resultados.");
                    return back()->with('mensaje', 'No tienes permisos para ver ningún certificado. Contacta al administrador.');
                }
                
                $documentosDB = $query->get();
                
                Log::info("Documentos encontrados después de filtro: " . $documentosDB->count());
                
                if ($documentosDB->isNotEmpty()) {
                    $resultados[$cedula] = [];
                    foreach ($documentosDB as $doc) {
                        // OBTENER EL NOMBRE DEL ARCHIVO
                        $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
                        
                        Log::info("Procesando documento ID: {$doc->id}");
                        Log::info("Nombre del archivo: {$nombreArchivo}");
                        Log::info("Ruta en BD: " . ($doc->ruta_archivo ?? 'No especificada'));
                        
                        // Interpretar el nombre del archivo
                        $info = $this->interpretarCertificado($nombreArchivo);
                        
                        // Verificar si el usuario tiene acceso a este prefijo específico
                        if ($filtrarPrefijos && !empty($info['prefijo'])) {
                            if (!in_array($info['prefijo'], $prefijosUsuario)) {
                                Log::info("Usuario no tiene acceso al prefijo: {$info['prefijo']}, omitiendo documento");
                                continue;
                            }
                        }
                        
                        // Obtener la ruta física del archivo
                        $rutaArchivo = $this->obtenerRutaFisica($doc);
                        
                        // Verificar si el archivo físico existe
                        $archivoExiste = false;
                        if ($rutaArchivo && file_exists($rutaArchivo)) {
                            $archivoExiste = true;
                            $tamañoArchivo = filesize($rutaArchivo);
                            Log::info("Archivo físico encontrado: {$rutaArchivo}, tamaño: {$tamañoArchivo} bytes");
                        } else {
                            Log::warning("Archivo físico NO encontrado: " . ($rutaArchivo ?? 'Ruta no disponible'));
                        }
                        
                        // Generar URLs
                        $urlVer = route('documento.ver', ['id' => $doc->id]);
                        $urlDescargar = route('documento.descargar', ['id' => $doc->id]);
                        
                        $resultados[$cedula][] = (object)[
                            'nombre_archivo' => $nombreArchivo,
                            'url' => $urlVer,
                            'descargar_url' => $urlDescargar,
                            'descripcion' => $info['descripcion'],
                            'fecha' => $info['fecha'],
                            'tipo' => $info['prefijo'],
                            'fecha_creacion' => $doc->created_at ? 
                                (is_string($doc->created_at) ? $doc->created_at : $doc->created_at->format('Y-m-d H:i:s')) : '',
                            'origen' => 'archivo_fisico',
                            'id' => $doc->id,
                            'ruta_archivo' => $doc->ruta_archivo ?? null,
                            'ruta_fisica' => $rutaArchivo,
                            'archivo_existe' => $archivoExiste,
                            'datos_completos' => $doc
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error al consultar base de datos: " . $e->getMessage());
                return back()->with('mensaje', 'Error al consultar la base de datos: ' . $e->getMessage());
            }
        }

        if (empty($resultados)) {
            Log::warning("No se encontraron resultados");
            return back()->with('mensaje', 'No se encontraron documentos para la cédula ingresada o no tienes permisos para verlos.');
        }

        Log::info("Total resultados: " . count($resultados));
        Log::info("=== FIN BÚSQUEDA ===");

        return view('certificados_e.resultados', compact('resultados'));
    }

    // Método para obtener la ruta física del archivo
    private function obtenerRutaFisica($documento)
    {
        // 1. Primero intentar con ruta_archivo si existe
        if (isset($documento->ruta_archivo) && !empty($documento->ruta_archivo)) {
            Log::info("Usando ruta_archivo: {$documento->ruta_archivo}");
            
            // La ruta está almacenada como: storage/RESULTADOS/[cedula]/[archivo.pdf]
            $rutaRelativa = $documento->ruta_archivo;
            
            // Convertir ruta relativa a ruta absoluta
            // storage/RESULTADOS/... -> storage/app/public/RESULTADOS/...
            $rutaPublic = str_replace('storage/', 'public/', $rutaRelativa);
            
            $rutaAbsoluta = storage_path('app/' . $rutaPublic);
            
            // También intentar con la ruta directa desde public/storage
            $rutaAlternativa = public_path($rutaRelativa);
            
            if (file_exists($rutaAbsoluta)) {
                return $rutaAbsoluta;
            } elseif (file_exists($rutaAlternativa)) {
                return $rutaAlternativa;
            }
        }
        
        // 2. Si no hay ruta_archivo, construirla basada en cedula y filename
        $cedula = $documento->cedula ?? '';
        $filename = $documento->filename ?? '';
        
        if (!empty($cedula) && !empty($filename)) {
            // Construir diferentes rutas posibles
            $rutasPosibles = [
                // Ruta desde storage/app/public
                storage_path('app/public/storage/RESULTADOS/' . $cedula . '/' . $filename),
                
                // Ruta desde public/storage
                public_path('storage/RESULTADOS/' . $cedula . '/' . $filename),
                
                // Ruta absoluta directa (ajusta según tu configuración)
                base_path('storage/app/public/RESULTADOS/' . $cedula . '/' . $filename),
                
                // Ruta para XAMPP/Laragon
                'C:\progresando\public\storage\RESULTADOS\\' . $cedula . '\\' . $filename,
            ];
            
            foreach ($rutasPosibles as $ruta) {
                if (file_exists($ruta)) {
                    Log::info("Archivo encontrado en: {$ruta}");
                    return $ruta;
                }
            }
        }
        
        Log::warning("No se pudo determinar la ruta física para documento ID: " . ($documento->id ?? 'N/A'));
        return null;
    }

    // Método para visualizar documento desde archivo físico
    public function verDocumento($id)
    {
        Log::info("=== VER DOCUMENTO ID: {$id} ===");
        
        try {
            $documento = DB::table('documentos_empresas')
                ->where('id', $id)
                ->first();
            
            if (!$documento) {
                Log::error("Documento no encontrado ID: {$id}");
                abort(404, 'Documento no encontrado');
            }
            
            // Obtener nombre del archivo
            $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            
            Log::info("Documento encontrado: ID={$id}, Nombre={$nombreArchivo}");
            
            // Obtener ruta física del archivo
            $rutaFisica = $this->obtenerRutaFisica($documento);
            
            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para ID: {$id}");
                Log::error("Ruta buscada: " . ($rutaFisica ?? 'No disponible'));
                
                // Mostrar información de debug
                echo "<h1>Error: Archivo no encontrado</h1>";
                echo "<p>ID: {$id}</p>";
                echo "<p>Nombre: {$nombreArchivo}</p>";
                echo "<p>Ruta en BD: " . ($documento->ruta_archivo ?? 'No disponible') . "</p>";
                echo "<p>Ruta buscada: " . ($rutaFisica ?? 'No disponible') . "</p>";
                echo "<p>Cédula: " . ($documento->cedula ?? 'No disponible') . "</p>";
                exit();
                
                // abort(404, 'Archivo físico no encontrado');
            }
            
            $tamañoArchivo = filesize($rutaFisica);
            Log::info("Archivo físico: {$rutaFisica}, tamaño: {$tamañoArchivo} bytes");
            
            // Determinar el tipo MIME basado en la extensión
            $mimeType = $this->obtenerMimeType($nombreArchivo);
            
            Log::info("Sirviendo documento: {$nombreArchivo}, tipo: {$mimeType}, tamaño: {$tamañoArchivo} bytes");
            
            // Servir el archivo
            return response()->file($rutaFisica, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
                'Content-Length' => $tamañoArchivo,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error en verDocumento: " . $e->getMessage());
            abort(500, 'Error al cargar el documento: ' . $e->getMessage());
        }
    }

    // Método para descargar documento desde archivo físico
    public function descargarDocumento($id)
    {
        Log::info("=== DESCARGAR DOCUMENTO ID: {$id} ===");
        
        try {
            $documento = DB::table('documentos_empresas')
                ->where('id', $id)
                ->first();
            
            if (!$documento) {
                abort(404, 'Documento no encontrado');
            }
            
            // Obtener nombre del archivo
            $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            
            // Obtener ruta física del archivo
            $rutaFisica = $this->obtenerRutaFisica($documento);
            
            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para descarga ID: {$id}");
                abort(404, 'Archivo físico no encontrado');
            }
            
            $tamañoArchivo = filesize($rutaFisica);
            Log::info("Descargando: {$nombreArchivo}, tamaño: {$tamañoArchivo} bytes, ruta: {$rutaFisica}");
            
            // Descargar el archivo
            return response()->download($rutaFisica, $nombreArchivo, [
                'Content-Type' => 'application/octet-stream',
                'Content-Length' => $tamañoArchivo,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en descargarDocumento: " . $e->getMessage());
            abort(500, 'Error al descargar el documento');
        }
    }

    // Método para obtener MIME type basado en extensión
    private function obtenerMimeType($nombreArchivo)
    {
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Método para interpretar el nombre del certificado (se mantiene igual)
    private function interpretarCertificado($nombreArchivo)
    {
        Log::info("Interpretando certificado: {$nombreArchivo}");
        
        // Mapeo de prefijos a descripciones
        $prefijos = [
            'H' => 'Historia ocupacional, ingreso, egreso, periódico',
            'HING' => 'Historia ocupacional de ingreso en el examen ingreso/egreso',
            'HG' => 'Historia Medicina General',
            'HCV' => 'Historia Cardiovascular',
            'HNU' => 'Historia de Nutrición',
            'C' => 'Certificado de aptitud ocupacional',
            'CTA' => 'Certificado CENS o certificado de Alturas',
            'CMA' => 'Certificado de manipulación de alimentos',
            'V' => 'Vertigo',
            'OM' => 'Osteomuscular',
            'A' => 'Audiometría',
            'EV' => 'Examen de voz',
            'O' => 'Optometría',
            'VIS' => 'Visiometría',
            'E' => 'Espirometría',
            'RE' => 'Resultado Espirometría',
            'L' => 'Laboratorio Clínico',
            'S' => 'Psicología',
            'RT' => 'Rx Torax',
            'R' => 'RX Columna',
            'EKG' => 'Electrocardiograma',
            'REM' => 'Remisión a EPS',
            'RPYP' => 'Remisión a PYP',
            'CV' => 'Carnet de vacunas',
            'VF' => 'Valoración Fisioterapia',
            'CM' => 'Coordinación Motriz',
            'PS' => 'Psicosensometrica',
            'CI' => 'Certificado de ingreso',
            'TH' => 'Toxicología',
            'ARM' => 'Documento adicional',
            'J' => 'Documento general',
            'TESTPSICOLOGIA' => 'Test psicológico',
            'CILEGAL' => 'Certificado legal',
            'CILD' => 'Certificado legal documentado',
            'CIL' => 'Certificado legal intermedio',
        ];
        
        // Extraer nombre sin extensión
        $nombreLimpio = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreMayus = strtoupper($nombreLimpio);
        
        Log::info("Nombre limpio: {$nombreLimpio}, Mayúsculas: {$nombreMayus}");
        
        // Si el nombre es genérico, no intentar buscar prefijo
        if (in_array($nombreMayus, ['DOCUMENTO', 'DOC', 'FILE', 'ARCHIVO'])) {
            return [
                'prefijo' => '',
                'descripcion' => 'Documento empresarial',
                'fecha' => '',
                'nombre_original' => $nombreArchivo
            ];
        }
        
        // Buscar el prefijo más largo que coincida
        $prefijoEncontrado = '';
        $descripcion = 'Documento empresarial';
        
        foreach ($prefijos as $prefijo => $desc) {
            $prefijoMayus = strtoupper($prefijo);
            
            // Buscar el prefijo al inicio del nombre
            if (strpos($nombreMayus, $prefijoMayus) === 0) {
                if (strlen($prefijo) > strlen($prefijoEncontrado)) {
                    $prefijoEncontrado = $prefijo;
                    $descripcion = $desc;
                }
            }
        }
        
        Log::info("Prefijo encontrado: {$prefijoEncontrado}, Descripción: {$descripcion}");
        
        // Extraer fecha si existe
        $fecha = '';
        if ($prefijoEncontrado) {
            // Remover el prefijo y buscar fecha
            $resto = substr($nombreLimpio, strlen($prefijoEncontrado));
            Log::info("Resto después del prefijo: {$resto}");
            
            // Buscar fecha en varios formatos
            $patronesFecha = [
                '/^[_-]*(\d{8})/',  // YYYYMMDD
                '/^[_-]*(\d{6})/',  // YYMMDD
                '/^[_-]*(\d{4}-\d{2}-\d{2})/', // YYYY-MM-DD
                '/^[_-]*(\d{2}-\d{2}-\d{4})/', // DD-MM-YYYY
            ];
            
            foreach ($patronesFecha as $patron) {
                if (preg_match($patron, $resto, $matches)) {
                    $fechaStr = $matches[1];
                    
                    // Formatear fecha según el patrón
                    if (strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                        $fecha = substr($fechaStr, 0, 4) . '-' . 
                                 substr($fechaStr, 4, 2) . '-' . 
                                 substr($fechaStr, 6, 2);
                    } elseif (strlen($fechaStr) === 6 && is_numeric($fechaStr)) {
                        // Asumir año 2000+ para años de 2 dígitos
                        $anio = '20' . substr($fechaStr, 0, 2);
                        $fecha = $anio . '-' . 
                                 substr($fechaStr, 2, 2) . '-' . 
                                 substr($fechaStr, 4, 2);
                    } elseif (strpos($fechaStr, '-') !== false) {
                        // Ya tiene formato con guiones
                        $fecha = $fechaStr;
                    }
                    
                    if (!empty($fecha)) {
                        Log::info("Fecha extraída: {$fecha}");
                        break;
                    }
                }
            }
        }
        
        return [
            'prefijo' => $prefijoEncontrado,
            'descripcion' => $descripcion,
            'fecha' => $fecha,
            'nombre_original' => $nombreArchivo
        ];
    }
    
    // Método para debug
    public function debugEstructura()
    {
        echo "<h1>DEBUG ESTRUCTURA DE TABLA Y ARCHIVOS</h1>";
        
        try {
            // Verificar estructura de la tabla
            $columns = DB::select("SHOW COLUMNS FROM documentos_empresas");
            
            echo "<h2>Columnas de la tabla:</h2>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col->Field}</td>";
                echo "<td>{$col->Type}</td>";
                echo "<td>{$col->Null}</td>";
                echo "<td>{$col->Key}</td>";
                echo "<td>" . ($col->Default ?? 'NULL') . "</td>";
                echo "<td>{$col->Extra}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Ver algunos datos de ejemplo
            echo "<h2>Datos de ejemplo (primeros 5 registros):</h2>";
            $documentos = DB::table('documentos_empresas')->limit(5)->get();
            
            if ($documentos->count() > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr>";
                foreach ($documentos->first() as $key => $value) {
                    echo "<th>{$key}</th>";
                }
                echo "</tr>";
                
                foreach ($documentos as $doc) {
                    echo "<tr>";
                    foreach ($doc as $key => $value) {
                        if ($key === 'ruta_archivo') {
                            echo "<td style='background-color: #e6ffe6;'><strong>" . htmlspecialchars($value ?? 'NULL') . "</strong></td>";
                        } elseif ($key === 'filename' || $key === 'nombre_archivo') {
                            echo "<td><strong>" . htmlspecialchars($value ?? 'NULL') . "</strong></td>";
                        } else {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                    }
                    echo "</tr>";
                    
                    // Mostrar información del archivo físico
                    echo "<tr><td colspan='" . count((array)$doc) . "' style='background-color: #f0f0f0;'>";
                    $rutaFisica = $this->obtenerRutaFisica($doc);
                    if ($rutaFisica && file_exists($rutaFisica)) {
                        $tamaño = filesize($rutaFisica);
                        echo "✅ Archivo físico encontrado: " . htmlspecialchars($rutaFisica) . " (" . $tamaño . " bytes)";
                    } else {
                        echo "❌ Archivo físico NO encontrado";
                        if ($rutaFisica) {
                            echo " (Ruta buscada: " . htmlspecialchars($rutaFisica) . ")";
                        }
                    }
                    echo "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No hay registros en la tabla</p>";
            }
            
            // Verificar estructura de directorios
            echo "<h2>Estructura de directorios:</h2>";
            $rutas = [
                'public/storage/RESULTADOS' => public_path('storage/RESULTADOS'),
                'storage/app/public/RESULTADOS' => storage_path('app/public/RESULTADOS'),
                'C:\progresando\public\storage\RESULTADOS' => 'C:\progresando\public\storage\RESULTADOS',
            ];
            
            foreach ($rutas as $nombre => $ruta) {
                if (is_dir($ruta)) {
                    $subcarpetas = glob($ruta . '/*', GLOB_ONLYDIR);
                    echo "<p><strong>{$nombre}</strong>: ✅ Existe (" . count($subcarpetas) . " subcarpetas)</p>";
                } else {
                    echo "<p><strong>{$nombre}</strong>: ❌ No existe</p>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Método para probar un documento específico
    public function probarDocumento($id)
    {
        try {
            $documento = DB::table('documentos_empresas')->where('id', $id)->first();
            
            if (!$documento) {
                return "Documento no encontrado";
            }
            
            echo "<h1>Prueba Documento ID: {$id}</h1>";
            echo "<pre>";
            print_r($documento);
            echo "</pre>";
            
            echo "<h2>Rutas probadas:</h2>";
            $rutaFisica = $this->obtenerRutaFisica($documento);
            
            if ($rutaFisica && file_exists($rutaFisica)) {
                echo "<p style='color: green;'>✅ Archivo encontrado: {$rutaFisica}</p>";
                echo "<p>Tamaño: " . filesize($rutaFisica) . " bytes</p>";
                
                // Enlace para ver el documento
                echo "<p><a href='" . route('documento.ver', ['id' => $id]) . "' target='_blank'>Ver documento</a></p>";
                echo "<p><a href='" . route('documento.descargar', ['id' => $id]) . "'>Descargar documento</a></p>";
            } else {
                echo "<p style='color: red;'>❌ Archivo NO encontrado</p>";
                if ($rutaFisica) {
                    echo "<p>Ruta buscada: {$rutaFisica}</p>";
                }
            }
            
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}