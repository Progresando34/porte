<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Response;

class CertificadoEController extends Controller
{
    public function index()
    {
        return view('certificados_e.buscar');
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
            $path = public_path("storage/RESULTADOS/{$cedula}");

            if (is_dir($path)) {
                $archivos = glob($path . '/*.pdf');
                if (!empty($archivos)) {
                    $resultados[$cedula] = [];
                    foreach ($archivos as $archivo) {
                        $nombre = basename($archivo);
                        $resultados[$cedula][] = (object)[
                            'nombre_archivo' => $nombre,
                            'url' => asset("storage/RESULTADOS/{$cedula}/{$nombre}")
                        ];
                    }
                }
            }
        }
    }

    // ✅ Procesar una sola cédula
    elseif (!empty($cedulaSimple)) {
        $cedula = preg_replace('/[^0-9]/', '', $cedulaSimple);
        $path = public_path("storage/RESULTADOS/{$cedula}");

        if (is_dir($path)) {
            $archivos = glob($path . '/*.pdf');
            if (!empty($archivos)) {
                $resultados[$cedula] = [];
                foreach ($archivos as $archivo) {
                    $nombre = basename($archivo);
                    $resultados[$cedula][] = (object)[
                        'nombre_archivo' => $nombre,
                        'url' => asset("storage/RESULTADOS/{$cedula}/{$nombre}")
                        ];
                }
            }
        }
    }

    if (empty($resultados)) {
        return back()->with('mensaje', 'No se encontraron certificados para la(s) cédula(s) ingresada(s).');
    }

    return view('certificados_e.resultados', compact('resultados'));
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
