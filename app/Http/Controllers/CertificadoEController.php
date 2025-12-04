<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB; // ✅ AÑADE ESTA LÍNEA

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

    // ✅ Procesar múltiples cédulas
    if (is_array($cedulasInput) && count(array_filter($cedulasInput)) > 0) {
        foreach ($cedulasInput as $cedulaRaw) {
            $cedula = preg_replace('/[^0-9]/', '', trim($cedulaRaw));
            
            // BUSCAR EN EL SISTEMA DE ARCHIVOS (MANTENER LO EXISTENTE)
            $path = public_path("storage/RESULTADOS/{$cedula}");
            if (is_dir($path)) {
                $archivos = glob($path . '/*.pdf');
                if (!empty($archivos)) {
                    $resultados[$cedula] = $resultados[$cedula] ?? [];
                    foreach ($archivos as $archivo) {
                        $nombre = basename($archivo);
                        $info = $this->interpretarCertificado($nombre);
                        
                        $resultados[$cedula][] = (object)[
                            'nombre_archivo' => $nombre,
                            'url' => asset("storage/RESULTADOS/{$cedula}/{$nombre}"),
                            'descripcion' => $info['descripcion'],
                            'fecha' => $info['fecha'],
                            'tipo' => $info['prefijo'],
                            'origen' => 'archivos'
                        ];
                    }
                }
            }
            
            // ✅ BUSCAR EN LA BASE DE DATOS (NUEVO)
           $documentosDB = DB::table('documentos_empresas')

                ->where('cedula', $cedula)
                ->get();
            
            if ($documentosDB->isNotEmpty()) {
                $resultados[$cedula] = $resultados[$cedula] ?? [];
                foreach ($documentosDB as $doc) {
                    $resultados[$cedula][] = (object)[
                        'nombre_archivo' => $doc->nombre_archivo ?? basename($doc->ruta),
                        'url' => $this->obtenerUrlDocumento($doc),
                        'descripcion' => $doc->descripcion ?? 'Documento empresarial',
                        'fecha' => $doc->created_at ? $doc->created_at->format('Y-m-d') : '',
                        'tipo' => $doc->tipo_documento ?? 'EMP',
                        'origen' => 'base_datos',
                        'datos_db' => $doc // Guardar el objeto completo por si necesitas más datos
                    ];
                }
            }
        }
    }

    // ✅ Procesar una sola cédula
    elseif (!empty($cedulaSimple)) {
        $cedula = preg_replace('/[^0-9]/', '', $cedulaSimple);
        
        // BUSCAR EN EL SISTEMA DE ARCHIVOS
        $path = public_path("storage/RESULTADOS/{$cedula}");
        if (is_dir($path)) {
            $archivos = glob($path . '/*.pdf');
            if (!empty($archivos)) {
                $resultados[$cedula] = [];
                foreach ($archivos as $archivo) {
                    $nombre = basename($archivo);
                    $info = $this->interpretarCertificado($nombre);
                    
                    $resultados[$cedula][] = (object)[
                        'nombre_archivo' => $nombre,
                        'url' => asset("storage/RESULTADOS/{$cedula}/{$nombre}"),
                        'descripcion' => $info['descripcion'],
                        'fecha' => $info['fecha'],
                        'tipo' => $info['prefijo'],
                        'origen' => 'archivos'
                    ];
                }
            }
        }
        
        // ✅ BUSCAR EN LA BASE DE DATOS (NUEVO)
      $documentosDB = DB::table('documentos_empresas')

            ->where('cedula', $cedula)
            ->get();
        
        if ($documentosDB->isNotEmpty()) {
            $resultados[$cedula] = $resultados[$cedula] ?? [];
            foreach ($documentosDB as $doc) {
                $resultados[$cedula][] = (object)[
                    'nombre_archivo' => $doc->nombre_archivo ?? basename($doc->ruta),
                    'url' => $this->obtenerUrlDocumento($doc),
                    'descripcion' => $doc->descripcion ?? 'Documento empresarial',
                    'fecha' => $doc->created_at ? $doc->created_at->format('Y-m-d') : '',
                    'tipo' => $doc->tipo_documento ?? 'EMP',
                    'origen' => 'base_datos',
                    'datos_db' => $doc
                ];
            }
        }
    }

    if (empty($resultados)) {
        return back()->with('mensaje', 'No se encontraron certificados para la(s) cédula(s) ingresada(s).');
    }

    // ✅ DEBUG (opcional, puedes comentarlo en producción)
    echo "<div style='background: #ff0000; color: white; padding: 20px; margin: 20px; border: 3px solid yellow;'>";
    echo "<h2>DEBUG - CONTROLADOR CertificadoEController</h2>";
    echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
    echo "<p>Total cédulas: " . count($resultados) . "</p>";
    
    foreach ($resultados as $cedula => $archivos) {
        echo "<h3>Cédula: $cedula</h3>";
        foreach ($archivos as $index => $archivo) {
            echo "<div style='background: #333; padding: 10px; margin: 5px;'>";
            echo "<p>Archivo #" . ($index+1) . ": " . $archivo->nombre_archivo . "</p>";
            echo "<p>Origen: " . ($archivo->origen ?? 'desconocido') . "</p>";
            echo "<p>¿Tiene descripción?: " . (isset($archivo->descripcion) ? 'SÍ' : 'NO') . "</p>";
            if (isset($archivo->descripcion)) {
                echo "<p>Descripción: " . $archivo->descripcion . "</p>";
                echo "<p>Tipo: " . $archivo->tipo . "</p>";
                echo "<p>Fecha: " . $archivo->fecha . "</p>";
            }
            echo "</div>";
        }
    }
    echo "</div>";

    return view('certificados_e.resultados', compact('resultados'));
}

// ✅ Método auxiliar para obtener la URL del documento
private function obtenerUrlDocumento($documento)
{
    // Si ya tiene una URL completa
    if (isset($documento->url) && filter_var($documento->url, FILTER_VALIDATE_URL)) {
        return $documento->url;
    }
    
    // Si tiene una ruta relativa
    if (isset($documento->ruta)) {
        // Ajusta según cómo almacenas los documentos
        if (strpos($documento->ruta, 'storage/') === 0) {
            return asset($documento->ruta);
        } elseif (strpos($documento->ruta, 'public/') === 0) {
            return asset(str_replace('public/', 'storage/', $documento->ruta));
        } else {
            return asset('storage/' . $documento->ruta);
        }
    }
    
    // Si no hay ruta, puedes generar una ruta a una vista o controlador
    return route('documento.ver', ['id' => $documento->id]);
}


   private function interpretarCertificado($nombreArchivo)
    {
        // Mapeo de prefijos a descripciones (del archivo que proporcionaste)
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
        ];
        
        $nombreLimpio = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreMayus = strtoupper($nombreLimpio);
        
        // Buscar el prefijo más largo que coincida
        $prefijoEncontrado = '';
        $descripcion = 'Certificado';
        
        foreach ($prefijos as $prefijo => $desc) {
            $prefijoMayus = strtoupper($prefijo);
            
            if (strpos($nombreMayus, $prefijoMayus) === 0) {
                // Tomar el prefijo más largo (ej: "HING" en lugar de solo "H")
                if (strlen($prefijo) > strlen($prefijoEncontrado)) {
                    $prefijoEncontrado = $prefijo;
                    $descripcion = $desc;
                }
            }
        }
        
        // Extraer la fecha (si existe después del prefijo)
        $fecha = '';
        $resto = substr($nombreLimpio, strlen($prefijoEncontrado));
        
        // Buscar un patrón de fecha YYYYMMDD
        if (preg_match('/^(\d{8})/', $resto, $matches)) {
            $fechaStr = $matches[1];
            // Formatear fecha como YYYY-MM-DD
            $fecha = substr($fechaStr, 0, 4) . '-' . 
                     substr($fechaStr, 4, 2) . '-' . 
                     substr($fechaStr, 6, 2);
        }
        
        return [
            'prefijo' => $prefijoEncontrado,
            'descripcion' => $descripcion,
            'fecha' => $fecha,
            'nombre_original' => $nombreArchivo
        ];
    }
    
   


    public function descargarMultiples(Request $request)
    {
        $cedulas = $request->input('cedulas', []);
        if (empty($cedulas)) {
            return redirect()->back()->with('mensaje', 'No se recibieron cédulas para descargar.');
        }

        $zip = new ZipArchive;
        $zipFileName = 'certificados_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($cedulas as $cedulaRaw) {
                $cedula = preg_replace('/[^0-9]/', '', trim($cedulaRaw));
                $path = public_path("storage/RESULTADOS/{$cedula}");
                if (is_dir($path)) {
                    $archivos = glob($path . '/*.pdf');
                    foreach ($archivos as $archivo) {
                        $zip->addFile($archivo, "{$cedula}/" . basename($archivo));
                    }
                }
            }
            $zip->close();
        } else {
            return redirect()->back()->with('mensaje', 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
