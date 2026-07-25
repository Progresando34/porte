<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certificado;
use Carbon\Carbon; // Importar Carbon

class TenenciaPanelConsultaController extends Controller
{
    // PANEL DE BÚSQUEDA
    public function index()
    {
        // Estadísticas reales de la tabla usando el modelo Certificado
        $totalRegistros = Certificado::count();
        
        // Obtener la última actualización y convertir a Carbon
        $ultimaActualizacion = Certificado::max('updated_at');
        
        // Verificar si es null y convertir a Carbon si es necesario
        if ($ultimaActualizacion) {
            $ultimaActualizacion = Carbon::parse($ultimaActualizacion)->format('d/m/Y H:i');
        } else {
            $ultimaActualizacion = 'Sin registros';
        }
        
        return view('tenencia-panel', compact('totalRegistros', 'ultimaActualizacion'));
    }
    
    // RESULTADOS POR CÉDULA
    public function resultados(Request $request)
    {
        $cedula = $request->get('busqueda', '');
        
        // Buscar registros por cédula usando el modelo Certificado
        $resultados = Certificado::where('cedula', 'LIKE', "%{$cedula}%")
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('tenencia-resultados', compact('resultados', 'cedula'));
    }
}