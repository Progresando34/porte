<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteCertificadoController extends Controller
{
    public function index()
    {
        return view('certificados_e.cliente');
    }

   public function buscar(Request $request)
{
    $cedulasInput = $request->input('cedulas_multiple', []);
    $cedulaSimple = $request->input('cedula', '');
    $resultados = [];

    Log::info("=== BÚSQUEDA CLIENTE ===");
    Log::info("Usuario: " . (auth()->user() ? auth()->user()->email : 'No autenticado'));
    
    // Verificar que sea usuario autenticado
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
    }

    // OBTENER USUARIO Y SUS PREFIJOS
    $user = auth()->user();
    $prefijosUsuario = $user->obtenerPrefijosArray();
    $esAdmin = $user->esAdministrador();

    // ========== LIMPIAR CÉDULAS ==========
    $cedulasABuscar = [];
    
    if (!empty($cedulaSimple)) {
        $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
        if (!empty($cedulaLimpia)) {
            $cedulasABuscar[] = $cedulaLimpia;
        }
    }
    
    if (!empty($cedulasInput) && is_array($cedulasInput)) {
        foreach ($cedulasInput as $cedula) {
            if (!empty($cedula)) {
                $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedula));
                if (!empty($cedulaLimpia) && !in_array($cedulaLimpia, $cedulasABuscar)) {
                    $cedulasABuscar[] = $cedulaLimpia;
                }
            }
        }
    }
    
    if (empty($cedulasABuscar)) {
        return back()->with('mensaje', 'Ingrese al menos una cédula válida');
    }

    Log::info("Cédulas a buscar (cliente): " . json_encode($cedulasABuscar));

    // ========== BUSCAR DOCUMENTOS CON FILTRO DE PREFIJOS ==========
    foreach ($cedulasABuscar as $cedula) {
        Log::info("Buscando cédula (cliente): {$cedula}");
        
        $documentosDB = DB::table('documentos_empresas')
            ->where('cedula', $cedula)
            ->get();
        
        Log::info("Documentos encontrados para cédula {$cedula}: " . $documentosDB->count());
        
        if ($documentosDB->isNotEmpty()) {
            $resultados[$cedula] = [];
            foreach ($documentosDB as $doc) {
                $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
                
                // Interpretar certificado para obtener el prefijo
                $info = $this->interpretarCertificado($nombreArchivo);
                $prefijoDocumento = $info['prefijo'];
                
                // VERIFICAR ACCESO AL PREFIJO
                // 1. Si es admin, tiene acceso a todo
                // 2. Si el documento no tiene prefijo, permitir acceso
                // 3. Si el documento tiene prefijo, verificar si el usuario tiene acceso
                $tieneAcceso = false;
                
                if ($esAdmin) {
                    $tieneAcceso = true;
                } elseif (empty($prefijoDocumento)) {
                    // Documentos sin prefijo, permitir acceso
                    $tieneAcceso = true;
                } else {
                    // Verificar si el usuario tiene acceso a este prefijo específico
                    $tieneAcceso = in_array($prefijoDocumento, $prefijosUsuario);
                }
                
                if (!$tieneAcceso) {
                    Log::info("Usuario NO tiene acceso al prefijo: {$prefijoDocumento}");
                    continue; // Saltar este documento
                }
                
                // Obtener la ruta física
                $rutaArchivo = $this->obtenerRutaFisica($doc);
                
                if ($rutaArchivo && file_exists($rutaArchivo)) {
                    $resultados[$cedula][] = (object)[
                        'nombre_archivo' => $nombreArchivo,
                        'url' => route('documento.ver', $doc->id),
                        'descargar_url' => route('documento.descargar', $doc->id),
                        'descripcion' => $info['descripcion'],
                        'fecha' => $info['fecha'],
                        'tipo' => $info['prefijo'],
                        'id' => $doc->id,
                    ];
                }
            }
        }
    }

    // Verificar si hay resultados
    $hayResultados = false;
    foreach ($resultados as $docs) {
        if (!empty($docs)) {
            $hayResultados = true;
            break;
        }
    }
    
    if (!$hayResultados) {
        return back()->with('mensaje', 'Personal sin Certificado De aptitud Ocupacional');
    }

    return view('certificados_e.resultados', compact('resultados'));
}
        private function interpretarCertificado($nombreArchivo)
    {
        Log::info("Interpretando certificado (cliente): {$nombreArchivo}");
        
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
        
        // Extraer fecha si existe
        $fecha = '';
        if ($prefijoEncontrado) {
            // Remover el prefijo y buscar fecha
            $resto = substr($nombreLimpio, strlen($prefijoEncontrado));
            
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
    

    private function obtenerRutaFisica($documento)
    {
        // Copia exactamente el mismo método de CertificadoEController
        if (isset($documento->ruta_archivo) && !empty($documento->ruta_archivo)) {
            $rutaRelativa = $documento->ruta_archivo;
            $rutaPublic = str_replace('storage/', 'public/', $rutaRelativa);
            $rutaAbsoluta = storage_path('app/' . $rutaPublic);
            
            if (file_exists($rutaAbsoluta)) {
                return $rutaAbsoluta;
            }
            
            $rutaAlternativa = public_path($rutaRelativa);
            if (file_exists($rutaAlternativa)) {
                return $rutaAlternativa;
            }
        }
        
        $cedula = $documento->cedula ?? '';
        $filename = $documento->filename ?? '';
        
        if (!empty($cedula) && !empty($filename)) {
            $rutasPosibles = [
                storage_path('app/public/storage/RESULTADOS/' . $cedula . '/' . $filename),
                public_path('storage/RESULTADOS/' . $cedula . '/' . $filename),
                base_path('storage/app/public/RESULTADOS/' . $cedula . '/' . $filename),
                'C:\progresando\public\storage\RESULTADOS\\' . $cedula . '\\' . $filename,
            ];
            
            foreach ($rutasPosibles as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }
        
        return null;
    }
}