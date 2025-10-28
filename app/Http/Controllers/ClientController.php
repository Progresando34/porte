<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client; // Asegúrate de tener el modelo Client creado y vinculado a tu tabla

class ClientController extends Controller
{
    public function consulta(Request $request)
    {
        $filtro = $request->input('filtro');
        $valor = $request->input('valor');

        $clientes = [];

        if ($filtro && $valor) {
            if ($filtro === 'nombre') {
                $clientes = Client::where('nombre', 'like', '%' . $valor . '%')->get();
            } elseif ($filtro === 'cedula') {
             $clientes = Client::whereRaw('BINARY TRIM(cedula) = ?', [$valor])->get();

            }
        }

        return view('client.consulta', compact('clientes'));
    }

public function consultaArmas(Request $request)
{
    $filtro = $request->input('filtro');
    $valor = $request->input('valor');
    $cedulasInput = $request->input('cedulas', []);

    $clientes = [];

    if ($filtro === 'cedula' && is_array($cedulasInput) && count(array_filter($cedulasInput)) > 0) {
        // Limpiar y filtrar cédulas no vacías
        $cedulas = array_map('trim', array_filter($cedulasInput));

        // Consulta múltiple
        $clientes = Client::whereIn('cedula', $cedulas)->get();

    } elseif ($filtro === 'cedula' && $valor) {
        $clientes = Client::where('cedula', $valor)->get();

    } elseif ($filtro === 'nombre' && $valor) {
        $clientes = Client::where('nombre', 'like', '%' . $valor . '%')->get();

    } elseif ($filtro === 'codigo_control' && $valor) {
        $clientes = Client::where('codigo_control', 'like', '%' . $valor . '%')->get();
    }

    return view('client.consultaArmas', compact('clientes'));
}

}