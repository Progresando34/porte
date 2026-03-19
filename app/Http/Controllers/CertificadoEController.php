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

    Log::info("========== INICIO BÚSQUEDA CERTIFICADOS (DEBUG) ==========");
    Log::info("Cédula simple: " . ($cedulaSimple ?: 'vacía'));
    Log::info("Cédulas múltiples: " . json_encode($cedulasInput));

    // ========== VALIDACIÓN DE USUARIO Y PERMISOS ==========
    $usuario = auth()->user();
    $trabajadorAutenticado = false;
    $filtrarPrefijos = true;
    $prefijosUsuario = [];
    $prefijosCadenas = [];
    $trabajadorCedula = '';

    Log::info("=== Validando autenticación ===");
    
    if (session('trabajador_autenticado')) {
        $trabajadorAutenticado = true;
        $trabajadorId = session('trabajador_id');
        $trabajadorCedula = session('trabajador_cedula') ?? '';
        Log::info("✅ Usuario autenticado como TRABAJADOR", [
            'trabajador_id' => $trabajadorId,
            'trabajador_cedula' => $trabajadorCedula
        ]);

        $trabajador = \App\Models\Trabajador::find($trabajadorId);
        $prefijosUsuario = $trabajador ? $trabajador->obtenerPrefijosIds() : [];
        $filtrarPrefijos = true;
        Log::info("Prefijos del trabajador (IDs): " . json_encode($prefijosUsuario));

    } elseif ($usuario) {
        Log::info("✅ Usuario autenticado como USUARIO", [
            'user_id' => $usuario->id,
            'profile_id' => $usuario->profile_id,
            'es_admin' => $usuario->esAdministrador() ? 'sí' : 'no'
        ]);
        
        $prefijosUsuario = $usuario->obtenerPrefijosArray();
        $filtrarPrefijos = true;
        
        if ($usuario->esAdministrador() || $usuario->profile_id == 1) {
            $filtrarPrefijos = false;
            Log::info("👑 Usuario es administrador - SIN FILTROS DE PREFIJOS");
        } else {
            Log::info("Prefijos del usuario (IDs): " . json_encode($prefijosUsuario));
        }
    } else {
        Log::error("❌ Usuario NO autenticado");
        return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
    }

    // Obtener los strings de los prefijos si es necesario filtrar
    if ($filtrarPrefijos && !empty($prefijosUsuario)) {
        $prefijosCadenas = \App\Models\Prefijo::whereIn('id', $prefijosUsuario)
            ->where('activo', true)
            ->pluck('prefijo')
            ->toArray();
        Log::info("🔍 Prefijos ACTIVOS (strings): " . json_encode($prefijosCadenas));
    } else {
        Log::info("ℹ️ No se aplicarán filtros de prefijos o usuario sin prefijos");
    }

    Log::info("¿Filtrar por prefijos?: " . ($filtrarPrefijos ? 'SÍ' : 'NO'));

    // ========== LIMPIAR CÉDULAS ==========
    $cedulasABuscar = [];
    if (!empty($cedulaSimple)) {
        $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedulaSimple));
        if (!empty($cedulaLimpia)) {
            $cedulasABuscar[] = $cedulaLimpia;
            Log::info("Cédula simple limpia: {$cedulaLimpia}");
        }
    }
    if (!empty($cedulasInput) && is_array($cedulasInput)) {
        foreach ($cedulasInput as $cedula) {
            if (!empty($cedula)) {
                $cedulaLimpia = preg_replace('/[^0-9]/', '', trim($cedula));
                if (!empty($cedulaLimpia) && !in_array($cedulaLimpia, $cedulasABuscar)) {
                    $cedulasABuscar[] = $cedulaLimpia;
                    Log::info("Cédula múltiple limpia: {$cedulaLimpia}");
                }
            }
        }
    }

    // ========== RESTRICCIÓN PARA TRABAJADORES ==========
    if ($trabajadorAutenticado && !empty($trabajadorCedula)) {
        $cedulasPermitidas = [$trabajadorCedula];
        $cedulasABuscar = array_intersect($cedulasABuscar, $cedulasPermitidas);
        Log::info("🔒 Trabajador restringido - Cédulas permitidas: " . json_encode($cedulasPermitidas));
        Log::info("Cédulas después de filtro: " . json_encode($cedulasABuscar));
        
        if (empty($cedulasABuscar)) {
            Log::warning("⚠️ Trabajador intentó buscar cédulas no permitidas");
            return back()->with('mensaje', 'Solo puedes buscar tu propia cédula: ' . $trabajadorCedula);
        }
    }

    Log::info("✅ Cédulas a buscar FINALES: " . json_encode($cedulasABuscar));
    
    if (empty($cedulasABuscar)) {
        Log::warning("⚠️ No hay cédulas válidas para buscar");
        return back()->with('mensaje', 'Ingrese al menos una cédula válida');
    }

    // ========== BÚSQUEDA EN AMBAS TABLAS ==========
    foreach ($cedulasABuscar as $cedula) {
        Log::info("========== PROCESANDO CÉDULA: {$cedula} ==========");
        $documentosCombinados = [];

        // 1. Buscar en documentos_empresas
        try {
            Log::info("🔍 Buscando en documentos_empresas para cédula: {$cedula}");
            $queryEmpresas = DB::table('documentos_empresas')->where('cedula', $cedula);
            
            // Verificar cuántos registros hay SIN filtros
            $totalSinFiltro = $queryEmpresas->count();
            Log::info("📊 documentos_empresas - Total sin filtros: {$totalSinFiltro}");
            
            // Aplicar filtros
            $queryConFiltro = $this->aplicarFiltroPrefijos(clone $queryEmpresas, 'filename', $filtrarPrefijos, $prefijosCadenas);
            $documentosEmpresas = $queryConFiltro->get();
            
            Log::info("📄 documentos_empresas - Encontrados con filtros: " . $documentosEmpresas->count());
            
            foreach ($documentosEmpresas as $index => $doc) {
                Log::info("Procesando doc empresas #{$index}", [
                    'id' => $doc->id,
                    'filename' => $doc->filename ?? 'NULL',
                    'ruta_archivo' => $doc->ruta_archivo ?? 'NULL'
                ]);
                
                $procesado = $this->procesarDocumento($doc, 'documentos_empresas', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosCombinados[] = $procesado;
                    Log::info("✅ Documento AGREGADO: {$procesado->nombre_archivo}");
                } else {
                    Log::warning("❌ Documento RECHAZADO ID: {$doc->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("💥 Error en documentos_empresas: " . $e->getMessage());
        }

        // 2. Buscar en rayosxod
        try {
            Log::info("🔍 Buscando en rayosxod para cédula: {$cedula}");
            $queryRayos = DB::table('rayosxod')->where('cedula', $cedula);
            
            // Verificar cuántos registros hay SIN filtros
            $totalSinFiltro = $queryRayos->count();
            Log::info("📊 rayosxod - Total sin filtros: {$totalSinFiltro}");
            
            // Mostrar todos los registros sin filtrar para depuración
            $todosLosRayos = $queryRayos->get();
            foreach ($todosLosRayos as $rx) {
                Log::info("📄 REGISTRO EN rayosxod (sin filtrar)", [
                    'id' => $rx->id,
                    'nombre' => $rx->nombre,
                    'cedula' => $rx->cedula,
                    'fecha_rx' => $rx->fecha_rx,
                    'nombre_archivo' => $rx->nombre_archivo,
                    'ruta' => $rx->ruta
                ]);
            }
            
            // Aplicar filtros
            $queryConFiltro = $this->aplicarFiltroPrefijos(clone $queryRayos, 'nombre_archivo', $filtrarPrefijos, $prefijosCadenas);
            $documentosRayos = $queryConFiltro->get();
            
            Log::info("📄 rayosxod - Encontrados con filtros: " . $documentosRayos->count());
            
            foreach ($documentosRayos as $index => $doc) {
                Log::info("Procesando doc rayos #{$index}", [
                    'id' => $doc->id,
                    'nombre_archivo' => $doc->nombre_archivo ?? 'NULL',
                    'ruta' => $doc->ruta ?? 'NULL'
                ]);
                
                $procesado = $this->procesarDocumento($doc, 'rayosxod', $filtrarPrefijos, $prefijosCadenas);
                if ($procesado) {
                    $documentosCombinados[] = $procesado;
                    Log::info("✅ Documento AGREGADO: {$procesado->nombre_archivo}");
                } else {
                    Log::warning("❌ Documento RECHAZADO ID: {$doc->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("💥 Error en rayosxod: " . $e->getMessage());
        }

        Log::info("📊 TOTAL documentos para cédula {$cedula}: " . count($documentosCombinados));
        $resultados[$cedula] = $documentosCombinados;
    }

    // ========== VERIFICAR RESULTADOS FINALES ==========
    $hayResultadosReales = false;
    foreach ($resultados as $cedula => $docs) {
        if (!empty($docs)) {
            $hayResultadosReales = true;
            Log::info("✅ Cédula {$cedula} tiene " . count($docs) . " documentos");
        } else {
            Log::warning("⚠️ Cédula {$cedula} NO tiene documentos después del filtrado");
        }
    }

    Log::info("========== FIN BÚSQUEDA ==========");
    Log::info("¿Hay resultados reales?: " . ($hayResultadosReales ? 'SÍ' : 'NO'));

    if (!$hayResultadosReales) {
        Log::warning("❌ NO se encontraron resultados que pasen los filtros");
        return back()->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas o no tienes permisos para verlos.');
    }

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
        $nombreArchivo = $doc->nombre_archivo ?? 'rx_' . $doc->id . '.jpg'; // Por defecto .jpg
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
        
        // AGREGAR DEBUG PARA VER QUÉ RUTAS SE ESTÁN PROBANDO
        Log::debug("Rutas probadas para documento ID {$doc->id}:");
        if ($origen === 'rayosxod') {
            Log::debug("- Ruta en BD: " . ($doc->ruta ?? 'NULL'));
            Log::debug("- Nombre archivo: " . ($doc->nombre_archivo ?? 'NULL'));
        }
        
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

    Log::info("=== Buscando ruta física ===", [
        'origen' => $origen,
        'id' => $documento->id ?? 'N/A',
        'cedula' => $cedula
    ]);

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
        
        Log::info("Buscando archivo rayosxod:", [
            'id' => $documento->id,
            'cedula' => $cedula,
            'nombre_archivo' => $filename,
            'ruta_bd' => $documento->ruta ?? 'NULL'
        ]);
        
        // ===== RUTA CORRECTA SEGÚN EL DEBUG =====
        // El archivo está en: storage/app/public/RESULTADOS/1091657847/dxodo20250514.jpeg
        
        // Construir la ruta correcta
        if (!empty($cedula) && !empty($filename)) {
            // Ruta 1: storage/app/public/RESULTADOS/[cedula]/[archivo]
            $ruta1 = storage_path('app/public/RESULTADOS/' . $cedula . '/' . $filename);
            
            // Ruta 2: public/storage/RESULTADOS/[cedula]/[archivo] (symlink)
            $ruta2 = public_path('storage/RESULTADOS/' . $cedula . '/' . $filename);
            
            // Ruta 3: Si la ruta en BD es relativa a public/storage
            if (!empty($documento->ruta)) {
                $ruta3 = public_path('storage/' . $documento->ruta);
                $ruta4 = storage_path('app/public/' . $documento->ruta);
                
                if (file_exists($ruta3)) {
                    Log::info("✅ Archivo encontrado en ruta3: {$ruta3}");
                    return $ruta3;
                }
                if (file_exists($ruta4)) {
                    Log::info("✅ Archivo encontrado en ruta4: {$ruta4}");
                    return $ruta4;
                }
            }
            
            // Verificar ruta1
            if (file_exists($ruta1)) {
                Log::info("✅ Archivo encontrado en ruta1: {$ruta1}");
                return $ruta1;
            }
            
            // Verificar ruta2
            if (file_exists($ruta2)) {
                Log::info("✅ Archivo encontrado en ruta2: {$ruta2}");
                return $ruta2;
            }
            
            // Si no se encontró, listar las rutas probadas
            Log::warning("❌ Archivo NO encontrado. Rutas probadas:");
            Log::warning("   - ruta1: {$ruta1}");
            Log::warning("   - ruta2: {$ruta2}");
            if (!empty($documento->ruta)) {
                Log::warning("   - ruta3: {$ruta3}");
                Log::warning("   - ruta4: {$ruta4}");
            }
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
        // PDF
        'pdf' => 'application/pdf',
        
        // Imágenes
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        
        // Documentos
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        
        // Otros
        'txt' => 'text/plain',
        'csv' => 'text/csv',
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

    public function debugDirecto()
{
    $cedula = '1091657847';
    
    echo "<h1>DEBUG DIRECTO - Cédula: {$cedula}</h1>";
    
    try {
        // 1. Verificar conexión a BD
        echo "<h2>1. Conexión a Base de Datos</h2>";
        try {
            DB::connection()->getPdo();
            echo "✅ Conexión exitosa a: " . DB::connection()->getDatabaseName() . "<br>";
        } catch (\Exception $e) {
            echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
        }
        
        // 2. Verificar que la tabla existe
        echo "<h2>2. Verificando tabla rayosxod</h2>";
        $tables = DB::select('SHOW TABLES');
        $tablaExiste = false;
        foreach ($tables as $table) {
            $tableName = current($table);
            if ($tableName == 'rayosxod') {
                $tablaExiste = true;
                echo "✅ Tabla 'rayosxod' encontrada<br>";
            }
        }
        if (!$tablaExiste) {
            echo "❌ Tabla 'rayosxod' NO existe<br>";
        }
        
        // 3. Buscar registros
        echo "<h2>3. Buscando registros para cédula {$cedula}</h2>";
        $registros = DB::table('rayosxod')
            ->where('cedula', $cedula)
            ->get();
        
        echo "Registros encontrados: " . $registros->count() . "<br>";
        
        if ($registros->count() > 0) {
            echo "<h3>Registros:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Fecha</th><th>Archivo</th><th>Ruta</th></tr>";
            
            foreach ($registros as $r) {
                echo "<tr>";
                echo "<td>{$r->id}</td>";
                echo "<td>{$r->nombre}</td>";
                echo "<td>{$r->cedula}</td>";
                echo "<td>{$r->fecha_rx}</td>";
                echo "<td>{$r->nombre_archivo}</td>";
                echo "<td>{$r->ruta}</td>";
                echo "</tr>";
                
                // 4. Verificar archivo físico
                echo "<tr><td colspan='6' style='background:#f0f0f0;'>";
                
                // Probar diferentes rutas
                $rutasProbadas = [
                    'Ruta directa' => $r->ruta,
                    'Public storage' => public_path('storage/' . $r->ruta),
                    'App public' => storage_path('app/public/' . $r->ruta),
                    'Base path' => base_path($r->ruta),
                    'Con carpeta rayosxod' => public_path('storage/rayosxod/' . $r->cedula . '/' . $r->nombre_archivo),
                    'Con carpeta RESULTADOS' => public_path('storage/RESULTADOS/' . $r->cedula . '/' . $r->nombre_archivo),
                ];
                
                foreach ($rutasProbadas as $nombre => $ruta) {
                    if (file_exists($ruta)) {
                        $tamano = filesize($ruta);
                        echo "✅ {$nombre}: Archivo encontrado en:<br> &nbsp;&nbsp; {$ruta}<br>";
                        echo "&nbsp;&nbsp; Tamaño: " . round($tamano/1024, 2) . " KB<br>";
                        
                        // Mostrar imagen si es JPEG
                        $ext = strtolower(pathinfo($r->nombre_archivo, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $url = asset('storage/' . $r->ruta);
                            echo "&nbsp;&nbsp; <img src='{$url}' style='max-width:200px; max-height:200px; border:1px solid #ccc;'><br>";
                        }
                    }
                }
                
                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            // Mostrar algunos registros de ejemplo de la tabla
            echo "<h3>Últimos 5 registros en rayosxod (cualquier cédula):</h3>";
            $ultimos = DB::table('rayosxod')->limit(5)->get();
            if ($ultimos->count() > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Fecha</th><th>Archivo</th><th>Ruta</th></tr>";
                foreach ($ultimos as $u) {
                    echo "<tr>";
                    echo "<td>{$u->id}</td>";
                    echo "<td>{$u->nombre}</td>";
                    echo "<td>{$u->cedula}</td>";
                    echo "<td>{$u->fecha_rx}</td>";
                    echo "<td>{$u->nombre_archivo}</td>";
                    echo "<td>{$u->ruta}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "❌ No hay ningún registro en la tabla rayosxod<br>";
            }
        }
        
        // 5. Verificar usuario actual
        echo "<h2>4. Usuario actual</h2>";
        if (auth()->check()) {
            $user = auth()->user();
            echo "✅ Usuario autenticado: {$user->name}<br>";
            echo "Email: {$user->email}<br>";
            echo "Profile ID: {$user->profile_id}<br>";
            echo "Es admin: " . ($user->esAdministrador() ? 'Sí' : 'No') . "<br>";
            
            // Prefijos del usuario
            $prefijos = $user->obtenerPrefijosArray();
            echo "Prefijos del usuario (IDs): " . json_encode($prefijos) . "<br>";
            
            if (!empty($prefijos)) {
                $prefijosNombres = \App\Models\Prefijo::whereIn('id', $prefijos)->pluck('prefijo')->toArray();
                echo "Prefijos (nombres): " . json_encode($prefijosNombres) . "<br>";
            }
        } else {
            echo "❌ Usuario NO autenticado<br>";
        }
        
    } catch (\Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
}