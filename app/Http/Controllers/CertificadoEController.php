<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CertificadoEController extends Controller
{
    public function index()
    {
        Log::info('=== Vista cliente (sin restricciones) ===');
        return view('certificados_e.cliente');
    }

    public function buscar(Request $request)
    {
        $cedulasInput = $request->input('cedulas_multiple', []);
        $cedulaSimple = $request->input('cedula', '');
        $resultados = [];

        Log::info("=== INICIO BÚSQUEDA CERTIFICADOS (UNIFICADA) ===");
        Log::info("Cédula simple: {$cedulaSimple}");
        Log::info("Cédulas múltiples: " . json_encode($cedulasInput));

        // ========== VALIDACIÓN DE USUARIO Y PERMISOS ==========
        $usuario = auth()->user();
        $trabajadorAutenticado = false;
        $filtrarPrefijos = true;
        $prefijosUsuario = [];
        $prefijosCadenas = [];
        $trabajadorCedula = '';

        if (session('trabajador_autenticado')) {
            $trabajadorAutenticado = true;
            $trabajadorId = session('trabajador_id');
            $trabajadorCedula = session('trabajador_cedula') ?? '';

            Log::info("Usuario autenticado como TRABAJADOR, ID: {$trabajadorId}, Cédula: {$trabajadorCedula}");

            $trabajador = \App\Models\Trabajador::find($trabajadorId);
            $prefijosUsuario = $trabajador ? $trabajador->obtenerPrefijosIds() : [];
            $filtrarPrefijos = true;

        } elseif ($usuario) {
            Log::info("Usuario autenticado como USUARIO, ID: {$usuario->id}, Perfil: {$usuario->profile_id}");
            $prefijosUsuario = $usuario->obtenerPrefijosArray();
            $filtrarPrefijos = true;
            if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
                $filtrarPrefijos = false;
                Log::info("Usuario es administrador, sin restricciones de prefijos");
            }
        } else {
            Log::warning("Usuario no autenticado");
            return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
        }

        // Obtener los strings de los prefijos si es necesario filtrar
        if ($filtrarPrefijos && !empty($prefijosUsuario)) {
            $prefijosCadenas = \App\Models\Prefijo::whereIn('id', $prefijosUsuario)
                ->where('activo', true)
                ->pluck('prefijo')
                ->toArray();
            Log::info("Prefijos activos del usuario: " . json_encode($prefijosCadenas));
        }

        // ========== LIMPIAR CÉDULAS ==========
        $cedulasABuscar = [];
        if (!empty($cedulaSimple)) {
            $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
            if (!empty($cedulaLimpia)) $cedulasABuscar[] = $cedulaLimpia;
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

        // ========== RESTRICCIÓN PARA TRABAJADORES ==========
        if ($trabajadorAutenticado && !empty($trabajadorCedula)) {
            $cedulasPermitidas = [$trabajadorCedula];
            $cedulasABuscar = array_intersect($cedulasABuscar, $cedulasPermitidas);
            Log::info("Trabajador restringido a buscar solo su cédula: {$trabajadorCedula}");
            if (empty($cedulasABuscar)) {
                return back()->with('mensaje', 'Solo puedes buscar tu propia cédula: ' . $trabajadorCedula);
            }
        }

        Log::info("Cédulas a buscar después de validaciones: " . json_encode($cedulasABuscar));
        if (empty($cedulasABuscar)) {
            return back()->with('mensaje', 'Ingrese al menos una cédula válida');
        }

        // ========== BÚSQUEDA EN AMBAS TABLAS ==========
        foreach ($cedulasABuscar as $cedula) {
            Log::info("===== Procesando cédula: {$cedula} =====");
            $documentosCombinados = [];

            // 1. Buscar en documentos_empresas
            try {
                $queryEmpresas = DB::table('documentos_empresas')->where('cedula', $cedula);
                $documentosEmpresas = $this->aplicarFiltroPrefijos($queryEmpresas, 'filename', $filtrarPrefijos, $prefijosCadenas)->get();
                Log::info("Encontrados " . $documentosEmpresas->count() . " docs en documentos_empresas");

                foreach ($documentosEmpresas as $doc) {
                    $procesado = $this->procesarDocumento($doc, 'documentos_empresas', $filtrarPrefijos, $prefijosCadenas);
                    if ($procesado) {
                        $documentosCombinados[] = $procesado;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error en documentos_empresas para cédula {$cedula}: " . $e->getMessage());
            }

            // 2. Buscar en rayosxod
            try {
                $queryRayos = DB::table('rayosxod')->where('cedula', $cedula);
                $documentosRayos = $this->aplicarFiltroPrefijos($queryRayos, 'nombre_archivo', $filtrarPrefijos, $prefijosCadenas)->get();
                Log::info("Encontrados " . $documentosRayos->count() . " docs en rayosxod");

                foreach ($documentosRayos as $doc) {
                    $procesado = $this->procesarDocumento($doc, 'rayosxod', $filtrarPrefijos, $prefijosCadenas);
                    if ($procesado) {
                        $documentosCombinados[] = $procesado;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error en rayosxod para cédula {$cedula}: " . $e->getMessage());
            }

            $resultados[$cedula] = $documentosCombinados;
        }

        // ========== VERIFICAR RESULTADOS FINALES ==========
        $hayResultadosReales = false;
        foreach ($resultados as $cedula => $docs) {
            if (!empty($docs)) {
                $hayResultadosReales = true;
                break;
            }
        }

        if (!$hayResultadosReales) {
            Log::warning("No se encontraron resultados en ninguna tabla.");
            return back()->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas o no tienes permisos para verlos.');
        }

        Log::info("=== FIN BÚSQUEDA UNIFICADA ===");
        return view('certificados_e.resultados', compact('resultados'));
    }

    /**
     * Aplica el filtro de prefijos a una query si es necesario.
     */
    private function aplicarFiltroPrefijos($query, $campoNombre, $filtrarPrefijos, $prefijosCadenas)
    {
        if ($filtrarPrefijos && !empty($prefijosCadenas)) {
            $query->where(function($q) use ($campoNombre, $prefijosCadenas) {
                foreach ($prefijosCadenas as $prefijo) {
                    $q->orWhere($campoNombre, 'LIKE', $prefijo . '%');
                }
            });
        } elseif ($filtrarPrefijos && empty($prefijosCadenas)) {
            $query->whereRaw('1 = 0');
        }
        return $query;
    }

    /**
     * Procesa un documento de cualquier tabla para unificarlo en un objeto estándar.
     */
    private function procesarDocumento($doc, $origen, $filtrarPrefijos, $prefijosCadenas)
    {
        $nombreArchivo = '';
        $rutaArchivoBD = null;

        if ($origen === 'documentos_empresas') {
            $nombreArchivo = $doc->filename ?? 'documento_' . $doc->id . '.pdf';
            $rutaArchivoBD = $doc->ruta_archivo ?? null;
        } elseif ($origen === 'rayosxod') {
            $nombreArchivo = $doc->nombre_archivo ?? 'rx_' . $doc->id . '.pdf';
            $rutaArchivoBD = $doc->ruta ?? null;
        }

        $info = $this->interpretarCertificado($nombreArchivo);
        $prefijoArchivo = $info['prefijo'];

        if ($filtrarPrefijos && !empty($prefijosCadenas) && !empty($prefijoArchivo)) {
            if (!in_array($prefijoArchivo, $prefijosCadenas)) {
                Log::info("Documento ID {$doc->id} (origen: {$origen}) con prefijo '{$prefijoArchivo}' no permitido. Saltando.");
                return null;
            }
        }

        $rutaFisica = $this->obtenerRutaFisica($doc, $origen);
        $archivoExiste = $rutaFisica && file_exists($rutaFisica);

        if (!$archivoExiste) {
            Log::warning("Archivo físico NO encontrado para documento ID {$doc->id} (origen: {$origen}). Ruta: " . ($rutaFisica ?? 'N/A'));
            return null;
        }

        return (object)[
            'id' => $doc->id,
            'origen' => $origen,
            'nombre_archivo' => $nombreArchivo,
            'descripcion' => $info['descripcion'],
            'fecha' => $info['fecha'],
            'tipo' => $prefijoArchivo,
            'fecha_creacion' => $doc->created_at ?? null,
            'ruta_archivo' => $rutaArchivoBD,
            'ruta_fisica' => $rutaFisica,
            'archivo_existe' => $archivoExiste,
        ];
    }

    /**
     * Obtiene la ruta física del archivo
     */
    private function obtenerRutaFisica($documento, $origen)
    {
        $cedula = $documento->cedula ?? '';
        $filename = '';

        if ($origen === 'documentos_empresas') {
            $filename = $documento->filename ?? '';
            if (isset($documento->ruta_archivo) && !empty($documento->ruta_archivo)) {
                $rutaRelativa = $documento->ruta_archivo;
                $rutaPublic = str_replace('storage/', 'public/', $rutaRelativa);
                $rutaAbsoluta = storage_path('app/' . $rutaPublic);
                $rutaAlternativa = public_path($rutaRelativa);
                if (file_exists($rutaAbsoluta)) return $rutaAbsoluta;
                if (file_exists($rutaAlternativa)) return $rutaAlternativa;
            }
        } elseif ($origen === 'rayosxod') {
            $filename = $documento->nombre_archivo ?? '';
            if (isset($documento->ruta) && !empty($documento->ruta)) {
                $rutaEnBD = $documento->ruta;
                if (file_exists($rutaEnBD)) {
                    return $rutaEnBD;
                }
                $rutasPosibles = [
                    storage_path('app/public/' . $rutaEnBD),
                    public_path('storage/' . $rutaEnBD),
                    base_path('storage/app/public/' . $rutaEnBD),
                ];
                foreach ($rutasPosibles as $ruta) {
                    if (file_exists($ruta)) return $ruta;
                }
            }
        }

        if ($origen === 'documentos_empresas' && !empty($cedula) && !empty($filename)) {
            $rutasPosibles = [
                storage_path('app/public/storage/RESULTADOS/' . $cedula . '/' . $filename),
                public_path('storage/RESULTADOS/' . $cedula . '/' . $filename),
                base_path('storage/app/public/RESULTADOS/' . $cedula . '/' . $filename),
            ];
            foreach ($rutasPosibles as $ruta) {
                if (file_exists($ruta)) return $ruta;
            }
        }

        Log::warning("No se pudo determinar la ruta física. Origen: {$origen}, ID: " . ($documento->id ?? 'N/A'));
        return null;
    }

    /**
     * Visualizar documento
     */
    public function verDocumento($id, Request $request)
    {
        $origen = $request->get('origen', 'documentos_empresas');

        Log::info("=== VER DOCUMENTO ID: {$id}, Origen: {$origen} ===");

        try {
            $documento = null;
            if ($origen === 'documentos_empresas') {
                $documento = DB::table('documentos_empresas')->where('id', $id)->first();
            } elseif ($origen === 'rayosxod') {
                $documento = DB::table('rayosxod')->where('id', $id)->first();
            }

            if (!$documento) {
                Log::error("Documento no encontrado ID: {$id} en origen: {$origen}");
                abort(404, 'Documento no encontrado');
            }

            $nombreArchivo = '';
            if ($origen === 'documentos_empresas') {
                $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            } else {
                $nombreArchivo = $documento->nombre_archivo ?? 'rx_' . $id . '.pdf';
            }

            $rutaFisica = $this->obtenerRutaFisica($documento, $origen);

            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para ID: {$id}, Origen: {$origen}");
                abort(404, 'Archivo físico no encontrado');
            }

            $mimeType = $this->obtenerMimeType($nombreArchivo);
            return response()->file($rutaFisica, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
            ]);

        } catch (\Exception $e) {
            Log::error("Error en verDocumento: " . $e->getMessage());
            abort(500, 'Error al cargar el documento');
        }
    }

    /**
     * Descargar documento individual
     */
    public function descargarDocumento($id, Request $request)
    {
        $origen = $request->get('origen', 'documentos_empresas');
        Log::info("=== DESCARGAR DOCUMENTO ID: {$id}, Origen: {$origen} ===");

        try {
            $documento = null;
            if ($origen === 'documentos_empresas') {
                $documento = DB::table('documentos_empresas')->where('id', $id)->first();
            } elseif ($origen === 'rayosxod') {
                $documento = DB::table('rayosxod')->where('id', $id)->first();
            }

            if (!$documento) {
                abort(404, 'Documento no encontrado');
            }

            $nombreArchivo = '';
            if ($origen === 'documentos_empresas') {
                $nombreArchivo = $documento->filename ?? 'documento_' . $id . '.pdf';
            } else {
                $nombreArchivo = $documento->nombre_archivo ?? 'rx_' . $id . '.pdf';
            }

            $rutaFisica = $this->obtenerRutaFisica($documento, $origen);

            if (!$rutaFisica || !file_exists($rutaFisica)) {
                Log::error("Archivo físico no encontrado para descarga ID: {$id}");
                abort(404, 'Archivo físico no encontrado');
            }

            return response()->download($rutaFisica, $nombreArchivo, [
                'Content-Type' => 'application/octet-stream',
            ]);

        } catch (\Exception $e) {
            Log::error("Error en descargarDocumento: " . $e->getMessage());
            abort(500, 'Error al descargar el documento');
        }
    }

    /**
     * Descargar múltiples documentos en ZIP
     */
    public function descargarMultiples(Request $request)
    {
        try {
            Log::info("=== INICIO DESCARGA MÚLTIPLE UNIFICADA ===");
            $cedula = $request->input('cedula', '');

            if (empty($cedula)) {
                return back()->with('mensaje', 'No se especificó la cédula para descargar');
            }

            // ========== VALIDACIONES DE SEGURIDAD ==========
            $usuario = auth()->user();
            $trabajadorAutenticado = false;
            $filtrarPrefijos = true;
            $prefijosUsuario = [];
            $prefijosCadenas = [];
            $trabajadorCedula = '';

            if (session('trabajador_autenticado')) {
                $trabajadorAutenticado = true;
                $trabajadorId = session('trabajador_id');
                $trabajadorCedula = session('trabajador_cedula') ?? '';
                if ($cedula !== $trabajadorCedula) {
                    return back()->with('mensaje', 'Solo puedes descargar documentos de tu propia cédula: ' . $trabajadorCedula);
                }
                $trabajador = \App\Models\Trabajador::find($trabajadorId);
                $prefijosUsuario = $trabajador ? $trabajador->obtenerPrefijosIds() : [];
                $filtrarPrefijos = true;
            } elseif ($usuario) {
                $prefijosUsuario = $usuario->obtenerPrefijosArray();
                $filtrarPrefijos = true;
                if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
                    $filtrarPrefijos = false;
                }
            } else {
                return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
            }

            if ($filtrarPrefijos && !empty($prefijosUsuario)) {
                $prefijosCadenas = \App\Models\Prefijo::whereIn('id', $prefijosUsuario)
                    ->where('activo', true)
                    ->pluck('prefijo')
                    ->toArray();
            }

            // ========== BUSCAR DOCUMENTOS EN AMBAS TABLAS ==========
            $documentosFiltrados = [];

            $queryEmpresas = DB::table('documentos_empresas')->where('cedula', $cedula);
            $docsEmpresas = $this->aplicarFiltroPrefijos($queryEmpresas, 'filename', $filtrarPrefijos, $prefijosCadenas)->get();
            foreach ($docsEmpresas as $doc) {
                $procesado = $this->procesarDocumento($doc, 'documentos_empresas', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosFiltrados[] = $procesado;
                }
            }

            $queryRayos = DB::table('rayosxod')->where('cedula', $cedula);
            $docsRayos = $this->aplicarFiltroPrefijos($queryRayos, 'nombre_archivo', $filtrarPrefijos, $prefijosCadenas)->get();
            foreach ($docsRayos as $doc) {
                $procesado = $this->procesarDocumento($doc, 'rayosxod', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosFiltrados[] = $procesado;
                }
            }

            Log::info("Total documentos para descarga múltiple: " . count($documentosFiltrados));

            if (empty($documentosFiltrados)) {
                return back()->with('mensaje', 'No hay documentos disponibles para descargar');
            }

            if (count($documentosFiltrados) === 1) {
                $doc = $documentosFiltrados[0];
                Log::info("Solo un documento, redirigiendo a descarga individual. Origen: {$doc->origen}, ID: {$doc->id}");
                return redirect()->route('documento.descargar', ['id' => $doc->id, 'origen' => $doc->origen]);
            }

            $zipFileName = 'certificados_' . $cedula . '_' . date('Ymd_His') . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
                Log::error("No se pudo crear el archivo ZIP");
                return back()->with('mensaje', 'Error al crear el archivo comprimido');
            }

            $archivosAgregados = 0;
            foreach ($documentosFiltrados as $doc) {
                if ($doc->archivo_existe && isset($doc->ruta_fisica) && file_exists($doc->ruta_fisica)) {
                    $zip->addFile($doc->ruta_fisica, $doc->nombre_archivo);
                    $archivosAgregados++;
                    Log::info("Agregado al ZIP: {$doc->nombre_archivo}");
                }
            }
            $zip->close();

            if ($archivosAgregados === 0) {
                return back()->with('mensaje', 'No se encontraron archivos físicos para descargar');
            }

            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error("Error en descargarMultiples: " . $e->getMessage());
            return back()->with('mensaje', 'Error al procesar la descarga: ' . $e->getMessage());
        }
    }

    /**
     * Obtener MIME type basado en extensión
     */
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

    /**
     * Interpretar el nombre del certificado
     */
    private function interpretarCertificado($nombreArchivo)
    {
        Log::info("Interpretando certificado: {$nombreArchivo}");

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
            'dx' => 'RX ODONTOLOGIA',
            'J' => 'Documento general',
            'TESTPSICOLOGIA' => 'Test psicológico',
            'CILEGAL' => 'Certificado legal',
            'CILD' => 'Certificado legal documentado',
            'CIL' => 'Certificado legal intermedio',
        ];

        $nombreLimpio = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreMayus = strtoupper($nombreLimpio);

        if (in_array($nombreMayus, ['DOCUMENTO', 'DOC', 'FILE', 'ARCHIVO'])) {
            return ['prefijo' => '', 'descripcion' => 'Documento empresarial', 'fecha' => '', 'nombre_original' => $nombreArchivo];
        }

        $prefijoEncontrado = '';
        $descripcion = 'Documento empresarial';

        foreach ($prefijos as $prefijo => $desc) {
            $prefijoMayus = strtoupper($prefijo);
            if (strpos($nombreMayus, $prefijoMayus) === 0) {
                if (strlen($prefijo) > strlen($prefijoEncontrado)) {
                    $prefijoEncontrado = $prefijo;
                    $descripcion = $desc;
                }
            }
        }

        $fecha = '';
        if ($prefijoEncontrado) {
            $resto = substr($nombreLimpio, strlen($prefijoEncontrado));
            $patronesFecha = [
                '/^[_-]*(\d{8})/',
                '/^[_-]*(\d{6})/',
                '/^[_-]*(\d{4}-\d{2}-\d{2})/',
                '/^[_-]*(\d{2}-\d{2}-\d{4})/',
            ];
            foreach ($patronesFecha as $patron) {
                if (preg_match($patron, $resto, $matches)) {
                    $fechaStr = $matches[1];
                    if (strlen($fechaStr) === 8 && is_numeric($fechaStr)) {
                        $fecha = substr($fechaStr, 0, 4) . '-' . substr($fechaStr, 4, 2) . '-' . substr($fechaStr, 6, 2);
                    } elseif (strlen($fechaStr) === 6 && is_numeric($fechaStr)) {
                        $anio = '20' . substr($fechaStr, 0, 2);
                        $fecha = $anio . '-' . substr($fechaStr, 2, 2) . '-' . substr($fechaStr, 4, 2);
                    } elseif (strpos($fechaStr, '-') !== false) {
                        $fecha = $fechaStr;
                    }
                    if (!empty($fecha)) break;
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

    public function indexTrabajador()
    {
        Log::info('=== Vista trabajador ===');
        if (!session()->has('trabajador_autenticado')) {
            return redirect()->route('login.form')->with('error', 'Debe iniciar sesión como trabajador.');
        }
        return view('certificados_e.trabajador', [
            'nombre' => session('trabajador_nombre'),
            'cedula' => session('trabajador_cedula'),
            'usuario' => session('trabajador_usuario'),
        ]);
    }
}