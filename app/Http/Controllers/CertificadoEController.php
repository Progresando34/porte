<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

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
                
                // ✅ BUSCAR SOLO EN LA BASE DE DATOS - BLOB
                $documentosDB = DB::table('documentos_empresas')
                    ->where('cedula', $cedula)
                    ->get();
                
                if ($documentosDB->isNotEmpty()) {
                    $resultados[$cedula] = [];
                    foreach ($documentosDB as $doc) {
                        // Interpretar el nombre del archivo para obtener descripción y fecha
                        $info = $this->interpretarCertificado($doc->nombre_archivo ?? 'documento.pdf');
                        
                        $resultados[$cedula][] = (object)[
                            'nombre_archivo' => $doc->nombre_archivo ?? 'documento.pdf',
                            'url' => route('documento.ver', ['id' => $doc->id]), // Ruta para ver el BLOB
                            'descargar_url' => route('documento.descargar', ['id' => $doc->id]), // Ruta para descargar
                            'descripcion' => $info['descripcion'],
                            'fecha' => $info['fecha'],
                            'tipo' => $info['prefijo'],
                            'fecha_creacion' => $doc->created_at ? (is_string($doc->created_at) ? $doc->created_at : $doc->created_at->format('Y-m-d H:i:s')) : '',
                            'origen' => 'base_datos',
                            'id' => $doc->id,
                            'datos_db' => $doc
                        ];
                    }
                }
            }
        }
        // ✅ Procesar una sola cédula
        elseif (!empty($cedulaSimple)) {
            $cedula = preg_replace('/[^0-9]/', '', $cedulaSimple);
            
            // ✅ BUSCAR SOLO EN LA BASE DE DATOS - BLOB
            $documentosDB = DB::table('documentos_empresas')
                ->where('cedula', $cedula)
                ->get();
            
            if ($documentosDB->isNotEmpty()) {
                $resultados[$cedula] = [];
                foreach ($documentosDB as $doc) {
                    // Interpretar el nombre del archivo
                    $info = $this->interpretarCertificado($doc->nombre_archivo ?? 'documento.pdf');
                    
                    $resultados[$cedula][] = (object)[
                        'nombre_archivo' => $doc->nombre_archivo ?? 'documento.pdf',
                        'url' => route('documento.ver', ['id' => $doc->id]),
                        'descargar_url' => route('documento.descargar', ['id' => $doc->id]),
                        'descripcion' => $info['descripcion'],
                        'fecha' => $info['fecha'],
                        'tipo' => $info['prefijo'],
                        'fecha_creacion' => $doc->created_at ? (is_string($doc->created_at) ? $doc->created_at : $doc->created_at->format('Y-m-d H:i:s')) : '',
                        'origen' => 'base_datos',
                        'id' => $doc->id,
                        'datos_db' => $doc
                    ];
                }
            }
        }

        if (empty($resultados)) {
            return back()->with('mensaje', 'No se encontraron documentos para la(s) cédula(s) ingresada(s).');
        }

        // ✅ DEBUG (opcional)
        /*
        echo "<div style='background: #ff0000; color: white; padding: 20px; margin: 20px; border: 3px solid yellow;'>";
        echo "<h2>DEBUG - CONTROLADOR CertificadoEController</h2>";
        echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p>Total cédulas: " . count($resultados) . "</p>";
        
        foreach ($resultados as $cedula => $archivos) {
            echo "<h3>Cédula: $cedula</h3>";
            foreach ($archivos as $index => $archivo) {
                echo "<div style='background: #333; padding: 10px; margin: 5px;'>";
                echo "<p>Archivo #" . ($index+1) . ": " . $archivo->nombre_archivo . "</p>";
                echo "<p>ID: " . $archivo->id . "</p>";
                echo "<p>Descripción: " . $archivo->descripcion . "</p>";
                echo "<p>Tipo: " . $archivo->tipo . "</p>";
                echo "<p>Fecha documento: " . $archivo->fecha . "</p>";
                echo "<p>URL: " . $archivo->url . "</p>";
                echo "</div>";
            }
        }
        echo "</div>";
        */

        return view('certificados_e.resultados', compact('resultados'));
    }

    // ✅ Método para visualizar documento desde BLOB
    public function verDocumento($id)
    {
        $documento = DB::table('documentos_empresas')
            ->where('id', $id)
            ->first();
        
        if (!$documento || !isset($documento->file_data)) {
            abort(404, 'Documento no encontrado');
        }

        // Determinar el tipo MIME
        $mimeType = 'application/pdf'; // Por defecto PDF
        $nombreArchivo = $documento->nombre_archivo ?? 'documento.pdf';
        
        // Verificar extensión para tipo MIME
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        if (isset($mimeTypes[$extension])) {
            $mimeType = $mimeTypes[$extension];
        }

        return Response::make($documento->file_data, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"'
        ]);
    }

    // ✅ Método para descargar documento desde BLOB
    public function descargarDocumento($id)
    {
        $documento = DB::table('documentos_empresas')
            ->where('id', $id)
            ->first();
        
        if (!$documento || !isset($documento->file_data)) {
            abort(404, 'Documento no encontrado');
        }

        $nombreArchivo = $documento->nombre_archivo ?? 'documento.pdf';
        
        return Response::make($documento->file_data, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"'
        ]);
    }

    // ✅ Método para descargar múltiples documentos
    public function descargarMultiples(Request $request)
    {
        $cedulas = $request->input('cedulas', []);
        if (empty($cedulas)) {
            return redirect()->back()->with('mensaje', 'No se recibieron cédulas para descargar.');
        }

        // Obtener todos los documentos de las cédulas
        $documentos = DB::table('documentos_empresas')
            ->whereIn('cedula', $cedulas)
            ->get();

        if ($documentos->isEmpty()) {
            return redirect()->back()->with('mensaje', 'No se encontraron documentos para descargar.');
        }

        // Si solo hay un documento, descargarlo directamente
        if ($documentos->count() === 1) {
            $doc = $documentos->first();
            return Response::make($doc->file_data, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . ($doc->nombre_archivo ?? 'documento.pdf') . '"'
            ]);
        }

        // Si hay múltiples documentos, crear ZIP
        $zip = new \ZipArchive;
        $zipFileName = 'documentos_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($documentos as $doc) {
                $nombreArchivo = $doc->nombre_archivo ?? 'documento_' . $doc->id . '.pdf';
                $nombreEnZip = $doc->cedula . '/' . $nombreArchivo;
                
                // Agregar el archivo BLOB al ZIP
                $zip->addFromString($nombreEnZip, $doc->file_data);
            }
            $zip->close();
        } else {
            return redirect()->back()->with('mensaje', 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // ✅ Método para interpretar el nombre del certificado
    private function interpretarCertificado($nombreArchivo)
    {
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
        
        $nombreLimpio = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreMayus = strtoupper($nombreLimpio);
        
        // Buscar el prefijo más largo que coincida
        $prefijoEncontrado = '';
        $descripcion = 'Documento empresarial';
        
        foreach ($prefijos as $prefijo => $desc) {
            $prefijoMayus = strtoupper($prefijo);
            
            if (strpos($nombreMayus, $prefijoMayus) === 0) {
                // Tomar el prefijo más largo
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
}