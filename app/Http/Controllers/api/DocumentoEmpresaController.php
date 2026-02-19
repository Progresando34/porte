<?php

namespace App\Http\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'cedula'        => 'required|string',
            'filename'      => 'required|string',
            'ruta_archivo'  => 'required|string'
        ]);

        try {
            DB::table('documentos_empresas')->insert([
                'cedula'        => $request->cedula,
                'filename'      => $request->filename,
                'ruta_archivo'  => $request->ruta_archivo,
                'created_at'    => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
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