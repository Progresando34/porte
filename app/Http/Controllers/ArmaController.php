<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ✅ Importación correcta
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // ✅ Esto soluciona el error
class ArmaController extends Controller
{
 

    public function index()
    {
        return view('armas.index');
    }

    public function create()
    {
        return view('armas.create');
    }

public function store(Request $request)
{
    Log::info('Entró al método store');

    try {
        DB::connection('mysql')->table('armas')->insert([
            'nombre' => 'Prueba',
            'cedula' => '123456',
            'codigo_control' => 'abc123',
            'activo' => true,
            'fecha_atencion' => now(),
            'certificado' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Insert en base primaria exitoso');

        try {
    DB::connection('secundaria')->getPdo();
    Log::info('✅ Conexión secundaria exitosa');
} catch (\Exception $e) {
    Log::error('❌ Fallo conexión secundaria: ' . $e->getMessage());
}

        DB::connection('secundaria')->table('armas')->insert([
            'nombre' => 'Prueba',
            'cedula_trabajador' => '123456',
            'codigo_numero_control' => 'abc123',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'certificado' => null,
        ]);

        Log::info('Insert en base secundaria exitoso');

        return redirect()->back()->with('success', 'Registro de prueba exitoso.');
    } catch (\Exception $e) {
        Log::error('Error en inserción: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}







    public function show($id)
    {
        // lógica para mostrar
    }

    public function edit($id)
    {
        // lógica para editar
    }

    public function update(Request $request, $id)
    {
        // lógica para actualizar
    }

    public function destroy($id)
    {
        // lógica para eliminar
    }

    // Métodos personalizados

public function consulta(Request $request)
{
    Log::info('✅ consultaArmas fue invocada');
    Log::info('📥 Datos recibidos:', $request->all());

    $clientes = [];

    $filtro = $request->input('filtro');
    $valor = $request->input('valor');
$cedulasInput = $request->input('cedulas_multiple', []);


    Log::info('🧾 CedulasInput:', $cedulasInput);

    $filtrosPermitidos = ['nombre', 'cedula', 'codigo_control'];

if (is_array($cedulasInput) && count(array_filter($cedulasInput)) > 0) {
    $cedulas = array_map('trim', array_filter($cedulasInput));
    Log::info('🔍 Cedulas limpias:', $cedulas);

    $clientes = DB::table('armas')
        ->whereIn('cedula', $cedulas)
        ->orWhereIn('cedula', $cedulas)
        ->get();

    Log::info('📦 Total resultados:', ['count' => $clientes->count()]);
}
elseif ($filtro && $valor && in_array($filtro, $filtrosPermitidos)) {
        Log::info("🔍 Búsqueda simple por $filtro = $valor");

        $clientes = DB::table('armas')
            ->where($filtro, 'like', '%' . $valor . '%')
            ->get();

        Log::info('📦 Total resultados:', ['count' => $clientes->count()]);
    } else {
        Log::warning('⚠️ Filtros inválidos o sin datos');
    }

    return view('client.consultaArmas', compact('clientes'));
}







  public function ver($filename)
{
    return Storage::disk('public')->response('certificados/' . $filename);
}

    public function descargar($filename)
    {
  return Storage::download('certificados/' . $filename);
    }
}
