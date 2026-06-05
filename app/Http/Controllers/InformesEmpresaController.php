<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformesEmpresaController extends Controller
{
    // 👇 ESTE MÉTODO ES EL QUE FALTA
    public function buscar()
    {
        return view('empresa_buscar');
    }

    // 👇 ESTE ES EL RESULTADO
    public function resultado(Request $request)
    {
        $nit = $request->input('nit');

$conteo = DB::table('citas')
    ->leftJoin('empresas', 'citas.empresa', '=', 'empresas.nit')
    ->selectRaw("
        COALESCE(empresas.nit, citas.empresa) as nit,
        COALESCE(empresas.nombre, citas.empresa) as empresa,
        COUNT(*) as total_personas,

        SUM(CASE WHEN examen = 'Ingreso' THEN 1 ELSE 0 END) as ingreso,
        SUM(CASE WHEN examen = 'Egreso' THEN 1 ELSE 0 END) as egreso,
        SUM(CASE WHEN examen = 'Periodico' THEN 1 ELSE 0 END) as periodico
    ")
    ->where(function($query) use ($nit) {
        $query->where('empresas.nit', $nit)
              ->orWhere('citas.empresa', $nit);
    })
    ->groupByRaw("COALESCE(empresas.nit, citas.empresa), COALESCE(empresas.nombre, citas.empresa)")
    ->get();

        return view('empresa_resultado', compact('conteo', 'nit'));
    }
}