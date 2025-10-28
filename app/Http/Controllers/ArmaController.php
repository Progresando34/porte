<?php

namespace App\Http\Controllers;
use ZipArchive;
use Illuminate\Support\Facades\Response;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ✅ Importación correcta
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // ✅ Esto soluciona el error
use Carbon\Carbon;
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
        // ✅ Guardar los datos reales del formulario
        DB::connection('mysql')->table('armas')->insert([
            'nombre'         => $request->input('nombre'),
            'cedula'         => $request->input('cedula'),
            'codigo_control' => $request->input('codigo_control'),
            'activo'         => $request->has('activo'), // checkbox
            'fecha_atencion' => $request->input('fecha_atencion') ?? now(),
            'certificado'    => $request->file('certificado') 
                                    ? $request->file('certificado')->store('certificados', 'public')
                                    : null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Log::info('Insert en base primaria exitoso');

        // Verificar conexión secundaria (si la usas para algo)
        try {
            DB::connection('secundaria')->getPdo();
            Log::info('✅ Conexión secundaria exitosa');
        } catch (\Exception $e) {
            Log::error('❌ Fallo conexión secundaria: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Registro creado correctamente.');
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

        Carbon::setLocale('es');
        
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


    public function descargarMultiples(Request $request)
{
    $cedulas = $request->input('cedulas', []);

    if (empty($cedulas)) {
        return redirect()->back()->with('error', 'No se recibieron cédulas para descargar.');
    }

    // Buscar todos los registros con esas cédulas
    $clientes = DB::table('armas')
        ->whereIn('cedula', $cedulas)
        ->get();

    if ($clientes->isEmpty()) {
        return redirect()->back()->with('error', 'No se encontraron certificados.');
    }

    $zip = new ZipArchive;
    $zipFileName = 'certificados_' . now()->format('Ymd_His') . '.zip';
    $zipPath = storage_path('app/public/' . $zipFileName);

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($clientes as $cliente) {
            if ($cliente->certificado && Storage::disk('public')->exists($cliente->certificado)) {
                $filePath = Storage::disk('public')->path($cliente->certificado);
                $zip->addFile($filePath, basename($cliente->certificado));
            }
        }
        $zip->close();
    } else {
        return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP.');
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}

}
