<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class DocumentoEmpresaController extends Controller
{
    /**
     * Verifica si un documento ya existe
     * Endpoint: POST /api/documentos/existe
     */
    public function existe(string $doc): JsonResponse
    {
        try {
            if (empty($doc)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El documento es requerido'
                ], 422);
            }

            $exists = DB::table('documentos_empresas')
                ->where('cedula', $doc)
                ->exists();

            return response()->json([
                'success' => true,
                'exists'  => $exists
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el documento'
            ], 500);
        }
    }


    /**
     * Registra un nuevo documento
     * Endpoint: POST /api/documentos/registrar
     */
    public function registrar(Request $request): JsonResponse
    {
        $request->validate([
            'cedula'  => 'required|string|max:50',
            'archivo' => 'required|file|mimes:pdf|max:51200',
            'filename' => 'nullable|string|max:255',
        ]);

        try {
            $cedula = trim((string) $request->cedula);
            $archivo = $request->file('archivo');

            $filename = trim((string) ($request->input('filename') ?: $archivo->getClientOriginalName()));
            $filename = basename($filename);

            if ($filename === '' || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nombre de archivo inválido'
                ], 422);
            }

            $exists = DB::table('documentos_empresas')
                ->where('cedula', $cedula)
                ->where('filename', $filename)
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'ruta_archivo' => $exists->ruta_archivo ?? null,
                    'message' => 'Documento ya registrado'
                ], 200);
            }

            $diskPath = 'RESULTADOS/' . $cedula . '/' . $filename;
            Storage::disk('public')->putFileAs('RESULTADOS/' . $cedula, $archivo, $filename);
            $rutaArchivo = '/storage/' . $diskPath;

            DB::table('documentos_empresas')->insert([
                'cedula'       => $cedula,
                'filename'     => $filename,
                'ruta_archivo' => $rutaArchivo,
                'created_at'   => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'exists' => false,
                'ruta_archivo' => $rutaArchivo,
                'message' => 'Documento registrado correctamente'
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el documento'
            ], 500);
        }
    }
}
