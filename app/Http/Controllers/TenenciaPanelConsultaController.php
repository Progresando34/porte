<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenenciaPanelConsultaController extends Controller
{
    // PANEL DE BÚSQUEDA
    public function index()
    {
        // Datos de ejemplo (después los reemplazarás con tu modelo)
        $totalRegistros = 1234;
        $ultimaActualizacion = 'Hoy';
        
        return view('tenencia-panel', compact('totalRegistros', 'ultimaActualizacion'));
    }
    
    // RESULTADOS
    public function resultados(Request $request)
    {
        $termino = $request->get('busqueda', '');
        
        // TEMPORAL: Datos de ejemplo
        // DESPUÉS: Reemplaza con tu modelo real
        $resultados = collect([
            (object) [
                'id' => 1,
                'codigo' => 'TEN-001',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'documento' => '123456789',
                'estado' => 'activo'
            ],
            (object) [
                'id' => 2,
                'codigo' => 'TEN-002',
                'nombre' => 'María',
                'apellido' => 'González',
                'documento' => '987654321',
                'estado' => 'inactivo'
            ]
        ]);
        
        return view('tenencia-resultados', compact('resultados', 'termino'));
    }
}