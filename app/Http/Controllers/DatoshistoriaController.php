<?php

namespace App\Http\Controllers;

use App\Models\Datoshistoria;
use Illuminate\Http\Request;

class DatoshistoriaController extends Controller
{
    /**
     * Listar todos los registros
     */
    public function index(Request $request)
    {
        $query = Datoshistoria::query();

        // Filtros opcionales
        if ($request->filled('cedula')) {
            $query->porCedula($request->cedula);
        }

        if ($request->filled('nombre')) {
            $query->porNombre($request->nombre);
        }

        if ($request->filled('fecha')) {
            $query->porFecha($request->fecha);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return view('datoshistoria.index', compact('registros'));
    }

    /**
     * Mostrar un registro específico
     */
    public function show($id)
    {
        $registro = Datoshistoria::findOrFail($id);
        return view('datoshistoria.show', compact('registro'));
    }

    /**
     * Buscar registros (API o vista)
     */
    public function buscar(Request $request)
    {
        $query = Datoshistoria::query();

        if ($request->filled('cedula')) {
            $query->porCedula($request->cedula);
        }

        if ($request->filled('nombre')) {
            $query->porNombre($request->nombre);
        }

        if ($request->filled('fecha')) {
            $query->porFecha($request->fecha);
        }

        $resultados = $query->paginate(50);
        
        // Si es petición AJAX/API
        if ($request->wantsJson()) {
            return response()->json($resultados);
        }

        return view('datoshistoria.buscar', compact('resultados'));
    }

    /**
     * Obtener por cédula (para API)
     */
    public function porCedula($cedula)
    {
        $registro = Datoshistoria::porCedula($cedula)->first();
        
        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($registro);
    }
}