<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TuModelo; // Importa el modelo que necesites

class TenenciaPanelConsultaController extends Controller
{
    public function index()
    {
        // Aquí puedes hacer tus consultas a la base de datos
        // $datos = TuModelo::all();
        // $datos = TuModelo::where('condicion', 'valor')->get();
        
        return view('tenenciapanelconsulta');
    }
}