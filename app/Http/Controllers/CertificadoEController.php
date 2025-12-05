<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

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

        // Procesar una sola cédula
        if (!empty($cedulaSimple)) {
            $cedula = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
            
            if (empty($cedula)) {
                Log::warning("Cédula vacía después de limpiar");
                return back()->with('mensaje', 'Cédula inválida');
            }
            
            Log::info("Buscando cédula: {$cedula}");
            
            // BUSCAR EN LA BASE DE DATOS
            try {
                // Primero intentar con el nombre correcto de columnas
                $documentosDB = DB::table('documentos_empresas')
                    ->where('cedula', $cedula)
                    ->get();
                
                Log::info("Documentos encontrados: " . $documentosDB->count());
                
                // DEBUG: Ver la estructura del primer documento
                if ($documentosDB->count() > 0) {
                    $firstDoc = $documentosDB->first();
                    Log::info("Estructura del primer documento:");
                    foreach ($firstDoc as $key => $value) {
                        $valueType = gettype($value);
                        $valuePreview = is_string($value) ? substr($value, 0, 50) : $valueType;
                        Log::info("  {$key}: {$valueType} = {$valuePreview}");
                    }
                }
                
                if ($documentosDB->isNotEmpty()) {
                    $resultados[$cedula] = [];
                    foreach ($documentosDB as $doc) {
                        // OBTENER EL NOMBRE DEL ARCHIVO - IMPORTANTE: usar filename
                        // Primero intentar con filename, luego con nombre_archivo, luego valor por defecto
                        $nombreArchivo = $doc->filename ?? 
                                        ($doc->nombre_archivo ?? 
                                         ($doc->file_name ?? 
                                          ($doc->archivo ?? 
                                           ($doc->name ?? 
                                            'documento_' . $doc->id . '.pdf'))));
                        
                        Log::info("Procesando documento ID: {$doc->id}");
                        Log::info("Nombre del archivo detectado: {$nombreArchivo}");
                        
                        // Interpretar el nombre del archivo
                        $info = $this->interpretarCertificado($nombreArchivo);
                        
                        // Generar URLs
                        $urlVer = route('documento.ver', ['id' => $doc->id]);
                        $urlDescargar = route('documento.descargar', ['id' => $doc->id]);
                        
                        Log::info("URLs generadas - Ver: {$urlVer}, Descargar: {$urlDescargar}");
                        
                        // Verificar si hay datos en el BLOB
                        $hasFileData = false;
                        $blobField = null;
                        
                        // Buscar el campo que contiene el BLOB
                        $possibleBlobFields = ['file_data', 'filedata', 'data', 'contenido', 'archivo', 'file'];
                        foreach ($possibleBlobFields as $field) {
                            if (isset($doc->$field) && !empty($doc->$field)) {
                                $hasFileData = true;
                                $blobField = $field;
                                break;
                            }
                        }
                        
                        $resultados[$cedula][] = (object)[
                            'nombre_archivo' => $nombreArchivo,
                            'url' => $urlVer,
                            'descargar_url' => $urlDescargar,
                            'descripcion' => $info['descripcion'],
                            'fecha' => $info['fecha'],
                            'tipo' => $info['prefijo'],
                            'fecha_creacion' => $doc->created_at ? 
                                (is_string($doc->created_at) ? $doc->created_at : $doc->created_at->format('Y-m-d H:i:s')) : '',
                            'origen' => 'base_datos',
                            'id' => $doc->id,
                            'tiene_file_data' => $hasFileData,
                            'blob_field' => $blobField,
                            'datos_completos' => $doc // Para debug
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
            return back()->with('mensaje', 'No se encontraron documentos para la cédula ingresada.');
        }

        Log::info("Total resultados: " . count($resultados));
        Log::info("=== FIN BÚSQUEDA ===");

        return view('certificados_e.resultados', compact('resultados'));
    }

    // Método para visualizar documento desde BLOB
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
            $nombreArchivo = $documento->filename ?? 
                            ($documento->nombre_archivo ?? 
                             ($documento->file_name ?? 
                              ($documento->archivo ?? 
                               ($documento->name ?? 
                                'documento_' . $id . '.pdf'))));
            
            Log::info("Documento encontrado: ID={$id}, Nombre={$nombreArchivo}");
            
            // Buscar el campo BLOB
            $blobData = null;
            $possibleBlobFields = ['file_data', 'filedata', 'data', 'contenido', 'archivo', 'file', 'blob_data', 'documento'];
            
            foreach ($possibleBlobFields as $field) {
                if (isset($documento->$field) && !empty($documento->$field)) {
                    $blobData = $documento->$field;
                    Log::info("BLOB encontrado en campo: {$field}, tamaño: " . strlen($blobData) . " bytes");
                    break;
                }
            }
            
            if (!$blobData) {
                Log::error("No se encontró BLOB para ID: {$id}");
                
                // Debug: mostrar todos los campos disponibles
                Log::info("Campos disponibles en el documento:");
                foreach ($documento as $key => $value) {
                    $type = gettype($value);
                    $size = is_string($value) ? strlen($value) . " bytes" : $type;
                    Log::info("  {$key}: {$size}");
                }
                
                abort(404, 'Documento no tiene contenido');
            }
            
            // Verificar si el BLOB tiene datos
            $blobSize = strlen($blobData);
            Log::info("Tamaño del BLOB: {$blobSize} bytes");
            
            if ($blobSize === 0) {
                Log::error("BLOB vacío para ID: {$id}");
                abort(404, 'Documento vacío');
            }

            // Determinar el tipo MIME basado en la extensión
            $mimeType = 'application/pdf';
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            
            // Si no tiene extensión, asumir PDF
            if (empty($extension)) {
                $nombreArchivo .= '.pdf';
                $extension = 'pdf';
            }
            
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
            
            if (isset($mimeTypes[$extension])) {
                $mimeType = $mimeTypes[$extension];
            }

            Log::info("Sirviendo documento: {$nombreArchivo}, tipo: {$mimeType}, tamaño: {$blobSize} bytes");
            
            return Response::make($blobData, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
                'Content-Length' => $blobSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en verDocumento: " . $e->getMessage());
            abort(500, 'Error al cargar el documento: ' . $e->getMessage());
        }
    }

    // Método para descargar documento desde BLOB
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
            $nombreArchivo = $documento->filename ?? 
                            ($documento->nombre_archivo ?? 
                             ($documento->file_name ?? 
                              ($documento->archivo ?? 
                               ($documento->name ?? 
                                'documento_' . $id . '.pdf'))));
            
            // Buscar el campo BLOB
            $blobData = null;
            $possibleBlobFields = ['file_data', 'filedata', 'data', 'contenido', 'archivo', 'file', 'blob_data', 'documento'];
            
            foreach ($possibleBlobFields as $field) {
                if (isset($documento->$field) && !empty($documento->$field)) {
                    $blobData = $documento->$field;
                    break;
                }
            }
            
            if (!$blobData) {
                abort(404, 'Documento no tiene contenido');
            }
            
            $blobSize = strlen($blobData);
            Log::info("Descargando: {$nombreArchivo}, tamaño: {$blobSize} bytes");
            
            return Response::make($blobData, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
                'Content-Length' => $blobSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en descargarDocumento: " . $e->getMessage());
            abort(500, 'Error al descargar el documento');
        }
    }

    // Método para interpretar el nombre del certificado
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
        echo "<h1>DEBUG ESTRUCTURA DE TABLA</h1>";
        
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
                        if ($key === 'filename' || $key === 'nombre_archivo' || $key === 'file_name') {
                            echo "<td><strong>" . htmlspecialchars($value ?? 'NULL') . "</strong></td>";
                        } elseif (is_string($value) && strlen($value) > 100) {
                            echo "<td>BLOB (" . strlen($value) . " bytes)</td>";
                        } else {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No hay registros en la tabla</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}