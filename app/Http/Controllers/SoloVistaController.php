<?php
// app/Http/Controllers/SoloVistaController.php

namespace App\Http\Controllers;

use App\Models\CitaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SoloVistaController extends Controller
{
    private function getUserAllowedPrefixes()
    {
        $user = auth()->user();
        
        if (!$user) {
            Log::warning('No hay usuario autenticado');
            return [];
        }
        
        $prefijos = $user->prefijos()->where('activo', true)->pluck('prefijo')->toArray();
        
        Log::info('Usuario ' . $user->name . ' tiene prefijos: ' . implode(', ', $prefijos));
        
        return $prefijos;
    }
    
    private function extraerPrefijo($nombreArchivo)
    {
        preg_match('/^([A-Za-z]+)/', $nombreArchivo, $matches);
        return isset($matches[1]) ? strtoupper($matches[1]) : '';
    }
    
    public function index()
    {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.index', compact('prefijosPermitidos'));
    }

public function buscar(Request $request)
{
    $cedula = $request->cedula;
    $cedulasMultiple = $request->cedulas_multiple;
    
    // Si hay cédulas múltiples, procesarlas
    if (!empty($cedulasMultiple)) {
        // Separar por saltos de línea y limpiar
        $cedulas = array_filter(array_map('trim', explode("\n", $cedulasMultiple)));
        
        if (empty($cedulas)) {
            return redirect()->route('solo_vista.index')->with('mensaje', 'No se ingresaron cédulas válidas');
        }
        
        $resultados = [];
        $totalEncontrados = 0;
        
        foreach ($cedulas as $ced) {
            $docs = CitaRecibida::where('cedula', 'LIKE', "%{$ced}%")->get();
            if ($docs->isNotEmpty()) {
                $resultados[$ced] = $docs;
                $totalEncontrados += $docs->count();
            }
        }
        
        if (empty($resultados)) {
            return redirect()->route('solo_vista.index')->with('mensaje', 'No se encontraron documentos para las cédulas ingresadas');
        }
        
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
    }
    
    // Búsqueda individual (funcionamiento original)
    if (empty($cedula)) {
        return redirect()->route('solo_vista.index')->with('mensaje', 'Por favor ingrese una cédula');
    }
    
    $resultados = CitaRecibida::where('cedula', 'LIKE', "%{$cedula}%")->get();
    
    if ($resultados->isEmpty()) {
        return redirect()->route('solo_vista.index')->with('mensaje', "No se encontraron documentos para la cédula: {$cedula}");
    }
    
    $resultados = [$cedula => $resultados];
    $prefijosPermitidos = $this->getUserAllowedPrefixes();
    
    return view('certificados_e.solo_vista.index', compact('resultados', 'prefijosPermitidos'));
}
    
    //  MÉTODO DE DEPURACIÓN - REEMPLAZA EL ANTERIOR

    public function verDocumentos($cedula)
{
    try {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        
        if (empty($prefijosPermitidos)) {
            return back()->with('mensaje', 'No tiene prefijos asignados para visualizar documentos');
        }
        
        $descripcionPrefijos = \App\Models\Prefijo::whereIn('prefijo', $prefijosPermitidos)
            ->pluck('descripcion', 'prefijo')
            ->toArray();
        
        // 🔥 NUEVO: Sobrescribir la descripción para el prefijo VF
        if (isset($descripcionPrefijos['VF'])) {
            $descripcionPrefijos['VF'] = 'Psicología';
        }
        
        $cita = CitaRecibida::where('cedula', $cedula)->first();
        
        if (!$cita) {
            return back()->with('mensaje', 'No se encontró registro para esta cédula');
        }
        
        // Ruta correcta donde están los archivos
        $carpeta = storage_path('app/public/RESULTADOS/' . $cedula);
        $pdfs = [];
        
        if (is_dir($carpeta)) {
            $archivos = glob($carpeta . '/*.pdf');
            $archivos = array_merge($archivos, glob($carpeta . '/*.PDF'));
            sort($archivos);
            
            foreach ($archivos as $archivo) {
                $nombreArchivo = basename($archivo);
                $prefijo = $this->extraerPrefijo($nombreArchivo);
                $prefijo = strtoupper($prefijo);
                
                if (!empty($prefijo) && in_array($prefijo, $prefijosPermitidos)) {
                    $descripcion = $descripcionPrefijos[$prefijo] ?? 'Sin descripción';
                    
                    // 🔥 NUEVO: Si el prefijo es VF, forzar descripción a "Psicología"
                    if ($prefijo === 'VF') {
                        $descripcion = 'Psicología';
                    }
                    
                    $pdfs[] = [
                        'nombre' => $nombreArchivo,
                        'prefijo' => $prefijo,
                        'descripcion' => $descripcion,
                        'ruta' => $archivo,
                        'fecha' => $cita->fecha,
                        'mision' => $cita->mision,
                        'empresa' => $cita->nombre_empresa
                    ];
                }
            }
        }
        
        if (empty($pdfs)) {
            $prefijosTexto = implode(', ', $prefijosPermitidos);
            return back()->with('mensaje', "No se encontraron archivos PDF con los prefijos permitidos ({$prefijosTexto}) para esta cédula");
        }
        
        return view('certificados_e.solo_vista.ver-documentos', compact('cita', 'pdfs', 'cedula'));
        
    } catch (\Exception $e) {
        Log::error('Error al ver documentos: ' . $e->getMessage());
        return back()->with('mensaje', 'Error al cargar los documentos: ' . $e->getMessage());
    }
}





    public function verPdf($id, Request $request)
    {
        try {
            $cita = CitaRecibida::findOrFail($id);
            $nombreArchivo = $request->get('archivo');
            
            if (!$nombreArchivo) {
                abort(404, 'No se especificó el archivo');
            }
            
            $prefijoArchivo = strtoupper($this->extraerPrefijo($nombreArchivo));
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            if (!in_array($prefijoArchivo, $prefijosPermitidos)) {
                abort(403, 'No tiene permiso para acceder a este documento (prefijo: ' . $prefijoArchivo . ')');
            }
            
            $path = storage_path('app/public/RESULTADOS/' . $cita->cedula . '/' . $nombreArchivo);
            
            if (!file_exists($path)) {
                abort(404, 'El archivo PDF no existe: ' . $path);
            }
            
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al ver PDF: ' . $e->getMessage());
            abort(404, 'Error al cargar el PDF');
        }
    }
    
    public function verFusionados($cedula)
    {
        try {
            $documentos = CitaRecibida::where('cedula', 'LIKE', "%{$cedula}%")
                ->orderBy('fecha', 'desc')
                ->get();
            
            if ($documentos->isEmpty()) {
                return back()->with('mensaje', 'No se encontraron documentos para esta cédula');
            }
            
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            return view('certificados_e.solo_vista.fusionados', compact('documentos', 'cedula', 'prefijosPermitidos'));
            
        } catch (\Exception $e) {
            Log::error('Error al ver documentos fusionados: ' . $e->getMessage());
            return back()->with('mensaje', 'Error al cargar los documentos fusionados');
        }
    }

        /**
     * Vista para Carpeta Completa (NUEVA VISTA INDEPENDIENTE)
     */
    public function carpetaCompleta()
    {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.carpeta_completa', compact('prefijosPermitidos'));
    }



//cambio temporal 

/**
 * Consulta para la vista de Carpeta Completa
 *
 * REGLA:
 * Solo mostrar PDFs:
 * - A...
 * - EV...
 *
 * Y siempre tomados EXCLUSIVAMENTE de:
 *
 * RESULTADOS/{CEDULA}
 */
public function consultarCarpeta(Request $request)
{
    try {

        $cedulasTexto = $request->input('cedulas_multiple', '');

        /*
         * Las cédulas vienen una por línea.
         */
        $cedulas = array_filter(
            array_map('trim', explode("\n", $cedulasTexto))
        );

        /*
         * Eliminar duplicados.
         */
        $cedulas = array_values(array_unique($cedulas));

        if (empty($cedulas)) {
            return response()->json([
                'success' => false,
                'message' => 'No se ingresaron cédulas válidas'
            ], 400);
        }

        $resultados = [];

        foreach ($cedulas as $cedula) {

            /*
             * ========================================================
             * VALIDAR CÉDULA
             * ========================================================
             *
             * Evitamos valores que puedan utilizarse para manipular
             * rutas del servidor.
             */
            if (!preg_match('/^\d+$/', $cedula)) {

                $resultados[$cedula] = [
                    'encontrado' => false,
                    'mensaje' => 'La cédula no tiene un formato válido'
                ];

                continue;
            }

            /*
             * ========================================================
             * BUSQUEDA EXACTA EN BASE DE DATOS
             * ========================================================
             *
             * IMPORTANTE:
             * NO usamos LIKE.
             *
             * Esto garantiza que 12345 no encuentre 123456.
             */
            $cita = CitaRecibida::where('cedula', $cedula)->first();

            if (!$cita) {

                $resultados[$cedula] = [
                    'encontrado' => false,
                    'mensaje' => 'No se encontró registro para esta cédula'
                ];

                continue;
            }

            /*
             * ========================================================
             * CARPETA EXCLUSIVA DE ESTA CÉDULA
             * ========================================================
             *
             * Ejemplo:
             *
             * RESULTADOS/123456789/
             */
            $carpeta = storage_path(
                'app/public/RESULTADOS/' . $cedula
            );

            $archivos = [];

            if (is_dir($carpeta)) {

                $archivosLista = scandir($carpeta);

                foreach ($archivosLista as $archivo) {

                    if ($archivo === '.' || $archivo === '..') {
                        continue;
                    }

                    $rutaCompleta =
                        $carpeta . DIRECTORY_SEPARATOR . $archivo;

                    /*
                     * Solo archivos.
                     * No permitir subcarpetas.
                     */
                    if (!is_file($rutaCompleta)) {
                        continue;
                    }

                    /*
                     * SOLO PDF.
                     */
                    $extension = strtolower(
                        pathinfo($archivo, PATHINFO_EXTENSION)
                    );

                    if ($extension !== 'pdf') {
                        continue;
                    }

                    /*
                     * ====================================================
                     * SOLO PREFIJOS A Y EV
                     * ====================================================
                     */
                    $prefijo = strtoupper(
                        $this->extraerPrefijo($archivo)
                    );

                    if ($prefijo !== 'A' && $prefijo !== 'EV') {
                        continue;
                    }

                    /*
                     * El archivo fue encontrado dentro de:
                     *
                     * RESULTADOS/{CEDULA}
                     *
                     * por lo tanto pertenece a esta consulta.
                     */
                    $archivos[] = [
                        'nombre' => $archivo,
                        'prefijo' => $prefijo
                    ];
                }

                /*
                 * Orden natural por nombre.
                 */
                usort($archivos, function ($a, $b) {
                    return strnatcasecmp(
                        $a['nombre'],
                        $b['nombre']
                    );
                });
            }

            /*
             * ========================================================
             * RESULTADO DE ESTA CÉDULA
             * ========================================================
             */
            $resultados[$cedula] = [
                'encontrado' => true,
                'cedula' => $cedula,
                'nombre' => $cita->nombre ?? 'N/A',
                'fecha' => $cita->fecha
                    ? date('d/m/Y', strtotime($cita->fecha))
                    : 'N/A',
                'nit_empresa' => $cita->nit_empresa ?? 'N/A',
                'nombre_empresa' => $cita->nombre_empresa ?? 'N/A',
                'total_archivos' => count($archivos),
                'examenes' => $archivos
            ];
        }

        return response()->json([
            'success' => true,
            'resultados' => $resultados
        ]);

    } catch (\Exception $e) {

        Log::error(
            'Error en consultarCarpeta: ' .
            $e->getMessage()
        );

        return response()->json([
            'success' => false,
            'message' => 'Error al consultar: ' . $e->getMessage()
        ], 500);
    }
}






//2do cambio temporal
/**
 * Descarga la carpeta completa.
 *
 * ESTRUCTURA FINAL:
 *
 * Carpeta_Completa.zip
 * │
 * ├── 123456789/
 * │   ├── A_123456789.pdf
 * │   └── EV_123456789.pdf
 * │
 * ├── 987654321/
 * │   ├── A_987654321.pdf
 * │   └── EV_987654321.pdf
 * │
 * └── 555555555/
 *     └── A_555555555.pdf
 *
 *
 * REGLA DE ORO:
 *
 * Los archivos de una cédula SOLO pueden salir de:
 *
 * RESULTADOS/{CEDULA}
 *
 * y SOLO pueden ser:
 *
 * A*.pdf
 * EV*.pdf
 *
 * Nunca se mezclan documentos entre cédulas.
 */
public function descargarCarpetaCompleta(Request $request)
{
    try {

        /*
         * ============================================================
         * 1. RECIBIR LAS CÉDULAS
         * ============================================================
         *
         * El frontend actualmente envía:
         *
         * 123456789,987654321,555555555
         */
        $cedulasTexto = $request->input('cedulas', '');

        $cedulas = array_filter(
            array_map('trim', explode(',', $cedulasTexto))
        );

        /*
         * Eliminar cédulas repetidas.
         */
        $cedulas = array_values(array_unique($cedulas));

        if (empty($cedulas)) {

            return back()->with(
                'mensaje',
                'No se seleccionaron cédulas para descargar'
            );
        }

        /*
         * ============================================================
         * 2. CREAR DIRECTORIO TEMPORAL
         * ============================================================
         */

        $tempDir = storage_path(
            'app/temp/' .
            uniqid('carpeta_completa_', true)
        );

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $carpetasIncluidas = 0;
        $errores = [];

        /*
         * ============================================================
         * 3. PROCESAR CADA CÉDULA POR SEPARADO
         * ============================================================
         */

        foreach ($cedulas as $cedula) {

            /*
             * ========================================================
             * VALIDACIÓN DE CÉDULA
             * ========================================================
             */

            if (!preg_match('/^\d+$/', $cedula)) {

                $errores[] =
                    "Cédula {$cedula}: formato inválido";

                continue;
            }

            /*
             * ========================================================
             * BUSCAR PERSONA EN BASE DE DATOS
             * ========================================================
             *
             * IMPORTANTE:
             *
             * Coincidencia EXACTA.
             *
             * NO:
             *
             * LIKE "%{$cedula}%"
             */
            $cita = CitaRecibida::where(
                'cedula',
                $cedula
            )->first();

            if (!$cita) {

                $errores[] =
                    "Cédula {$cedula}: No existe en la base de datos";

                continue;
            }

            /*
             * ========================================================
             * CARPETA DE ORIGEN
             * ========================================================
             *
             * Esta es la ÚNICA ubicación de donde se pueden sacar
             * los archivos de esta persona.
             *
             * RESULTADOS/123456789/
             */
            $carpetaOrigen = storage_path(
                'app/public/RESULTADOS/' . $cedula
            );

            if (!is_dir($carpetaOrigen)) {

                $errores[] =
                    "Cédula {$cedula}: La carpeta de resultados no existe";

                continue;
            }

            /*
             * ========================================================
             * VERIFICACIÓN DEL NOMBRE DE LA CARPETA
             * ========================================================
             *
             * Debe llamarse exactamente igual a la cédula.
             */
            if (basename($carpetaOrigen) !== $cedula) {

                $errores[] =
                    "Cédula {$cedula}: La carpeta de origen no coincide con la cédula";

                continue;
            }

            /*
             * ========================================================
             * OBTENER RUTA REAL DE LA CARPETA
             * ========================================================
             */

            $carpetaOrigenReal = realpath($carpetaOrigen);

            if ($carpetaOrigenReal === false) {

                $errores[] =
                    "Cédula {$cedula}: No se pudo validar la carpeta de origen";

                continue;
            }

            /*
             * ========================================================
             * LEER SOLO ESTA CARPETA
             * ========================================================
             */

            $archivos = scandir($carpetaOrigen);

            $archivosValidos = [];

            foreach ($archivos as $archivo) {

                if ($archivo === '.' || $archivo === '..') {
                    continue;
                }

                /*
                 * Ruta completa del archivo.
                 */
                $rutaCompleta =
                    $carpetaOrigen .
                    DIRECTORY_SEPARATOR .
                    $archivo;

                /*
                 * ====================================================
                 * SOLO ARCHIVOS
                 * ====================================================
                 */
                if (!is_file($rutaCompleta)) {
                    continue;
                }

                /*
                 * ====================================================
                 * SOLO PDF
                 * ====================================================
                 */
                $extension = strtolower(
                    pathinfo($archivo, PATHINFO_EXTENSION)
                );

                if ($extension !== 'pdf') {
                    continue;
                }

                /*
                 * ====================================================
                 * SOLO PREFIJO A O EV
                 * ====================================================
                 */
                $prefijo = strtoupper(
                    $this->extraerPrefijo($archivo)
                );

                if ($prefijo !== 'A' && $prefijo !== 'EV') {
                    continue;
                }

                /*
                 * ====================================================
                 * VALIDACIÓN EXTRA DE RUTA
                 * ====================================================
                 *
                 * Verificamos que el archivo realmente esté dentro
                 * de la carpeta de ESTA cédula.
                 */
                $rutaReal = realpath($rutaCompleta);

                if ($rutaReal === false) {
                    continue;
                }

                if (
                    strpos(
                        $rutaReal,
                        $carpetaOrigenReal . DIRECTORY_SEPARATOR
                    ) !== 0
                ) {

                    $errores[] =
                        "Cédula {$cedula}: Archivo rechazado por seguridad: {$archivo}";

                    continue;
                }

                /*
                 * Guardamos el archivo junto con su ruta de origen.
                 */
                $archivosValidos[] = [
                    'nombre' => $archivo,
                    'ruta' => $rutaReal,
                    'cedula' => $cedula
                ];
            }

            /*
             * ========================================================
             * ORDENAR ARCHIVOS
             * ========================================================
             */

            usort($archivosValidos, function ($a, $b) {

                return strnatcasecmp(
                    $a['nombre'],
                    $b['nombre']
                );
            });

            /*
             * ========================================================
             * SI NO TIENE ARCHIVOS A/EV
             * ========================================================
             */

            if (empty($archivosValidos)) {

                $errores[] =
                    "Cédula {$cedula}: No tiene PDFs con prefijo A o EV";

                continue;
            }

            /*
             * ========================================================
             * CREAR CARPETA DESTINO DE LA CÉDULA
             * ========================================================
             *
             * Ejemplo:
             *
             * temp/
             * └── 123456789/
             */
            $carpetaDestino =
                $tempDir .
                DIRECTORY_SEPARATOR .
                $cedula;

            if (!is_dir($carpetaDestino)) {

                mkdir(
                    $carpetaDestino,
                    0777,
                    true
                );
            }

            /*
             * ========================================================
             * COPIAR ARCHIVOS
             * ========================================================
             */

            $archivosCopiados = 0;

            foreach ($archivosValidos as $archivo) {

                /*
                 * MUY IMPORTANTE:
                 *
                 * El origen ya fue validado como perteneciente
                 * exclusivamente a esta cédula.
                 */
                $origen = $archivo['ruta'];

                /*
                 * El destino SIEMPRE es:
                 *
                 * temp/{CEDULA}/{ARCHIVO}
                 */
                $destino =
                    $carpetaDestino .
                    DIRECTORY_SEPARATOR .
                    $archivo['nombre'];

                /*
                 * Copiar.
                 */
                if (copy($origen, $destino)) {

                    $archivosCopiados++;

                } else {

                    $errores[] =
                        "Cédula {$cedula}: Error al copiar {$archivo['nombre']}";
                }
            }

            /*
             * ========================================================
             * SOLO CONTAR CARPETA SI REALMENTE TIENE ARCHIVOS
             * ========================================================
             */

            if ($archivosCopiados > 0) {

                $carpetasIncluidas++;
            }
        }

        /*
         * ============================================================
         * 4. SI NO SE PUDO GENERAR NINGUNA CARPETA
         * ============================================================
         */

        if ($carpetasIncluidas === 0) {

            $this->eliminarDirectorio($tempDir);

            $mensaje =
                'No se pudo incluir ninguna cédula.';

            if (!empty($errores)) {

                $mensaje .=
                    ' ' .
                    implode(' | ', $errores);
            }

            return back()->with(
                'mensaje',
                $mensaje
            );
        }

        /*
         * ============================================================
         * 5. CREAR ZIP
         * ============================================================
         */

        $zipNombre =
            'Carpeta_Completa_' .
            date('Y-m-d_H-i-s') .
            '.zip';

        $zipRuta =
            storage_path(
                'app/temp/' . $zipNombre
            );

        $zip = new \ZipArchive();

        if (
            $zip->open(
                $zipRuta,
                \ZipArchive::CREATE |
                \ZipArchive::OVERWRITE
            ) !== true
        ) {

            throw new \Exception(
                'No se pudo crear el archivo ZIP'
            );
        }

        /*
         * ============================================================
         * 6. AGREGAR CARPETAS AL ZIP
         * ============================================================
         *
         * Resultado:
         *
         * ZIP
         * ├── 123456789/
         * ├── 987654321/
         * └── 555555555/
         */
        $this->agregarDirectorioAZip(
            $tempDir,
            $zip,
            ''
        );

        $zip->close();

        /*
         * ============================================================
         * 7. ELIMINAR TEMPORAL
         * ============================================================
         */

        $this->eliminarDirectorio(
            $tempDir
        );

        /*
         * ============================================================
         * 8. DESCARGAR
         * ============================================================
         */

        return response()
            ->download(
                $zipRuta,
                $zipNombre
            )
            ->deleteFileAfterSend(true);

    } catch (\Exception $e) {

        Log::error(
            'Error en descargarCarpetaCompleta: ' .
            $e->getMessage()
        );

        /*
         * Intentar limpiar temporal.
         */
        if (
            isset($tempDir) &&
            is_dir($tempDir)
        ) {

            $this->eliminarDirectorio(
                $tempDir
            );
        }

        return back()->with(
            'mensaje',
            'Error al generar la carpeta completa: ' .
            $e->getMessage()
        );
    }
}





    /**
     * Agrega recursivamente un directorio a un archivo ZIP
     */
    private function agregarDirectorioAZip($directorio, $zip, $subcarpeta)
    {
        $items = scandir($directorio);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $ruta = $directorio . '/' . $item;
            $nombreEnZip = empty($subcarpeta) ? $item : $subcarpeta . '/' . $item;
            
            if (is_dir($ruta)) {
                $zip->addEmptyDir($nombreEnZip);
                $this->agregarDirectorioAZip($ruta, $zip, $nombreEnZip);
            } else {
                $zip->addFile($ruta, $nombreEnZip);
            }
        }
    }



        /**
     * Vista para PDF Unificado (NUEVA VISTA INDEPENDIENTE)
     */
    public function pdfUnificado()
    {
        $prefijosPermitidos = $this->getUserAllowedPrefixes();
        return view('certificados_e.solo_vista.pdf_unificado', compact('prefijosPermitidos'));
    }

    /**
     * Consulta para la vista de PDF Unificado (SOLO AJAX)
     * Solo muestra cédulas que tengan archivos con prefijo H
     */
    public function consultarPdfUnificado(Request $request)
    {
        try {
            $cedulasTexto = $request->input('cedulas_multiple', '');
            $cedulas = array_filter(array_map('trim', explode("\n", $cedulasTexto)));
            
            if (empty($cedulas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ingresaron cédulas válidas'
                ], 400);
            }
            
            $resultados = [];
            $prefijosPermitidos = $this->getUserAllowedPrefixes();
            
            foreach ($cedulas as $cedula) {
                $cita = CitaRecibida::where('cedula', $cedula)->first();
                
                if (!$cita) {
                    $resultados[$cedula] = [
                        'encontrado' => false,
                        'mensaje' => 'No se encontró registro para esta cédula'
                    ];
                    continue;
                }
                
                $carpeta = storage_path('app/public/RESULTADOS/' . $cedula);
                $archivosH = [];
                
                if (is_dir($carpeta)) {
                    $archivosLista = scandir($carpeta);
                    foreach ($archivosLista as $archivo) {
                        if ($archivo === '.' || $archivo === '..') continue;
                        
                        $prefijo = $this->extraerPrefijo($archivo);
                        // SOLO ARCHIVOS CON PREFIJO H
                        if (strtoupper($prefijo) === 'H') {
                            $archivosH[] = [
                                'nombre' => $archivo,
                                'prefijo' => 'H',
                                'ruta' => $carpeta . '/' . $archivo
                            ];
                        }
                    }
                    sort($archivosH);
                }
                
                $resultados[$cedula] = [
                    'encontrado' => true,
                    'cedula' => $cedula,
                    'nombre' => $cita->nombre ?? 'N/A',
                    'fecha' => $cita->fecha ? date('d/m/Y', strtotime($cita->fecha)) : 'N/A',
                    'nit_empresa' => $cita->nit_empresa ?? 'N/A',
                    'nombre_empresa' => $cita->nombre_empresa ?? 'N/A',
                    'total_archivos_h' => count($archivosH),
                    'examenes_h' => $archivosH
                ];
            }
            
            return response()->json([
                'success' => true,
                'resultados' => $resultados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en consultarPdfUnificado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera un PDF unificado con todos los archivos H de las cédulas seleccionadas
     */
    public function generarPdfUnificado(Request $request)
    {
        try {
            $cedulasTexto = $request->input('cedulas', '');
            $cedulas = array_filter(array_map('trim', explode(',', $cedulasTexto)));
            
            if (empty($cedulas)) {
                return back()->with('mensaje', 'No se seleccionaron cédulas para generar el PDF');
            }
            
            // Crear carpeta temporal
            $tempDir = storage_path('app/temp/' . uniqid('pdf_unificado_'));
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            // Crear subcarpeta IPS90257
            $carpetaIPS = $tempDir . '/IPS90257';
            if (!is_dir($carpetaIPS)) {
                mkdir($carpetaIPS, 0777, true);
            }
            
            $archivosEncontrados = 0;
            $errores = [];
            $cedulasUsadas = [];
            $primerNit = null;
            
            // Recolectar todos los archivos H
            $archivosPdf = [];
            
            foreach ($cedulas as $cedula) {
                $cita = CitaRecibida::where('cedula', $cedula)->first();
                
                if (!$cita) {
                    $errores[] = "Cédula {$cedula}: No existe en la base de datos";
                    continue;
                }
                
                // Guardar el NIT de la primera cédula válida
                if ($primerNit === null && $cita->nit_empresa) {
                    $primerNit = $cita->nit_empresa;
                }
                
                $carpetaOrigen = storage_path('app/public/RESULTADOS/' . $cedula);
                
                if (!is_dir($carpetaOrigen)) {
                    $errores[] = "Cédula {$cedula}: La carpeta de resultados no existe";
                    continue;
                }
                
                $archivos = scandir($carpetaOrigen);
                $cedulaTieneH = false;
                
                foreach ($archivos as $archivo) {
                    if ($archivo === '.' || $archivo === '..') continue;
                    
                    $rutaCompleta = $carpetaOrigen . '/' . $archivo;
                    
                    if (!is_file($rutaCompleta)) continue;
                    
                    $prefijo = $this->extraerPrefijo($archivo);
                    
                    // SOLO ARCHIVOS CON PREFIJO H
                    if (strtoupper($prefijo) === 'H') {
                        $cedulaTieneH = true;
                        $archivosPdf[] = [
                            'ruta' => $rutaCompleta,
                            'nombre' => $archivo,
                            'cedula' => $cedula
                        ];
                        $archivosEncontrados++;
                    }
                }
                
                if ($cedulaTieneH) {
                    $cedulasUsadas[] = $cedula;
                } else {
                    $errores[] = "Cédula {$cedula}: No tiene archivos con prefijo H";
                }
            }
            
            if (empty($archivosPdf)) {
                $this->eliminarDirectorio($tempDir);
                return back()->with('mensaje', 'No se encontraron archivos con prefijo H para las cédulas seleccionadas.');
            }
            
            // Generar el nombre del PDF
            $nombrePdf = 'FEV_' . ($primerNit ?? '000000000') . '_IPS90257.pdf';
            $rutaPdfSalida = $carpetaIPS . '/' . $nombrePdf;
            
            // Usar FPDI + FPDF para unir los PDFs
            $this->unirPdfs($archivosPdf, $rutaPdfSalida);
            
            // Crear ZIP con la carpeta IPS90257
            $zipNombre = 'PDF_Unificado_' . date('Y-m-d_H-i-s') . '.zip';
            $zipRuta = storage_path('app/temp/' . $zipNombre);
            
            $zip = new \ZipArchive();
            if ($zip->open($zipRuta, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }
            
            $this->agregarDirectorioAZip($tempDir, $zip, '');
            $zip->close();
            
            $this->eliminarDirectorio($tempDir);
            
            return response()->download($zipRuta, $zipNombre)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error en generarPdfUnificado: ' . $e->getMessage());
            return back()->with('mensaje', 'Error al generar el PDF unificado: ' . $e->getMessage());
        }
    }

/**
 * Une múltiples archivos PDF en uno solo usando FPDI + FPDF
 */
private function unirPdfs($archivosPdf, $rutaSalida)
{
    // ✅ RUTA CORRECTA PARA FPDF v1.8.2
    $fpdfPath = base_path('vendor/setasign/fpdf/fpdf.php');
    
    // ✅ RUTA CORRECTA PARA FPDI
    $fpdiPath = base_path('vendor/setasign/fpdi/src/Fpdi.php');
    
    // 🔥 VERIFICAR QUE EXISTAN
    if (!file_exists($fpdfPath)) {
        throw new \Exception('No se encontró FPDF en: ' . $fpdfPath);
    }
    
    if (!file_exists($fpdiPath)) {
        throw new \Exception('No se encontró FPDI en: ' . $fpdiPath);
    }
    
    // 🔥 CARGAR LAS LIBRERÍAS
    require_once $fpdfPath;
    require_once $fpdiPath;
    
    // 🔥 VERIFICAR QUE FPDF SE CARGÓ
    if (!class_exists('FPDF')) {
        throw new \Exception('FPDF no se pudo cargar correctamente');
    }
    
    // ✅ CREAR INSTANCIA DE FPDI (usa FPDF internamente)
    $pdf = new \setasign\Fpdi\Fpdi();
    
    // Recorrer todos los archivos PDF
    foreach ($archivosPdf as $archivo) {
        $ruta = $archivo['ruta'];
        
        if (!file_exists($ruta)) {
            \Log::warning('Archivo no encontrado: ' . $ruta);
            continue;
        }
        
        try {
            // Importar el PDF
            $pageCount = $pdf->setSourceFile($ruta);
            
            // Importar cada página
            for ($i = 1; $i <= $pageCount; $i++) {
                $template = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($template);
                
                // Agregar página con el tamaño del original
                $pdf->AddPage(
                    $size['orientation'] ?? 'P',
                    [$size['width'] ?? 210, $size['height'] ?? 297]
                );
                $pdf->useTemplate($template);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al importar PDF ' . basename($ruta) . ': ' . $e->getMessage());
            continue;
        }
    }
    
    // Guardar el PDF combinado
    $pdf->Output('F', $rutaSalida);
    
    if (!file_exists($rutaSalida)) {
        throw new \Exception('No se pudo generar el PDF combinado');
    }
}




    private function eliminarDirectorio($directorio)
    {
        if (!is_dir($directorio)) return;
        
        $items = scandir($directorio);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $ruta = $directorio . '/' . $item;
            if (is_dir($ruta)) {
                $this->eliminarDirectorio($ruta);
            } else {
                unlink($ruta);
            }
        }
        rmdir($directorio);
    }
}