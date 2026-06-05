<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Consolidado;
use App\Models\Profesiograma;
use App\Models\Dcondicione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentosEmpresaController extends Controller
{
    // Vista principal con buscador
    public function index()
    {
        return view('documentos-empresa.index');
    }
    
    // Buscar empresas por NIT o nombre (para la lupa)
    public function buscarEmpresas(Request $request)
    {
        $search = $request->get('q');
        
        $empresas = Empresa::where('nit', 'LIKE', "%{$search}%")
            ->orWhere('nombre', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['nit', 'nombre']);
            
        return response()->json($empresas);
    }
    
    // Mostrar documentos de una empresa específica
    public function mostrarDocumentos($nit)
    {
        $empresa = Empresa::where('nit', $nit)->firstOrFail();
        
        $consolidados = Consolidado::where('empresa_nit', $nit)
            ->orderBy('fecha_documento', 'desc')
            ->get();
            
        $profesiogramas = Profesiograma::where('empresa_nit', $nit)
            ->orderBy('fecha_documento', 'desc')
            ->get();
            
        $dcondiciones = Dcondicione::where('empresa_nit', $nit)
            ->orderBy('fecha_documento', 'desc')
            ->get();
            
        return view('documentos-empresa.show', compact('empresa', 'consolidados', 'profesiogramas', 'dcondiciones'));
    }
    
    // SUBIR DOCUMENTOS
    public function subirConsolidado(Request $request, $nit)
    {
        $request->validate([
            'fecha_documento' => 'required|date',
            'archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:10240',
            'descripcion' => 'nullable|string'
        ]);
        
        $empresa = Empresa::where('nit', $nit)->firstOrFail();
        
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreOriginal = $file->getClientOriginalName();
            $tamanio = round($file->getSize() / 1024, 2);
            $tipoArchivo = $file->getMimeType();
            
            $ruta = $file->store("documentos/consolidados/{$nit}", 'public');
            
            $consolidado = Consolidado::create([
                'empresa_nit' => $nit,
                'fecha_documento' => $request->fecha_documento,
                'archivo' => $ruta,
                'nombre_archivo' => $nombreOriginal,
                'tamanio' => $tamanio,
                'tipo_archivo' => $tipoArchivo,
                'descripcion' => $request->descripcion
            ]);
            
            return redirect()->back()->with('success', 'Consolidado subido correctamente');
        }
        
        return redirect()->back()->with('error', 'Error al subir el archivo');
    }
    
    public function subirProfesiograma(Request $request, $nit)
    {
        $request->validate([
            'fecha_documento' => 'required|date',
            'archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'cargo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string'
        ]);
        
        $empresa = Empresa::where('nit', $nit)->firstOrFail();
        
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreOriginal = $file->getClientOriginalName();
            $tamanio = round($file->getSize() / 1024, 2);
            $tipoArchivo = $file->getMimeType();
            
            $ruta = $file->store("documentos/profesiogramas/{$nit}", 'public');
            
            $profesiograma = Profesiograma::create([
                'empresa_nit' => $nit,
                'fecha_documento' => $request->fecha_documento,
                'archivo' => $ruta,
                'nombre_archivo' => $nombreOriginal,
                'tamanio' => $tamanio,
                'tipo_archivo' => $tipoArchivo,
                'cargo' => $request->cargo,
                'descripcion' => $request->descripcion
            ]);
            
            return redirect()->back()->with('success', 'Profesiograma subido correctamente');
        }
        
        return redirect()->back()->with('error', 'Error al subir el archivo');
    }
    
    public function subirDcondicione(Request $request, $nit)
    {
        $request->validate([
            'fecha_documento' => 'required|date',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'tipo_condicion' => 'required|in:medica,laboral,psicosocial,ambiental',
            'descripcion' => 'nullable|string'
        ]);
        
        $empresa = Empresa::where('nit', $nit)->firstOrFail();
        
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreOriginal = $file->getClientOriginalName();
            $tamanio = round($file->getSize() / 1024, 2);
            $tipoArchivo = $file->getMimeType();
            
            $ruta = $file->store("documentos/dcondiciones/{$nit}", 'public');
            
            $dcondicione = Dcondicione::create([
                'empresa_nit' => $nit,
                'fecha_documento' => $request->fecha_documento,
                'archivo' => $ruta,
                'nombre_archivo' => $nombreOriginal,
                'tamanio' => $tamanio,
                'tipo_archivo' => $tipoArchivo,
                'tipo_condicion' => $request->tipo_condicion,
                'descripcion' => $request->descripcion
            ]);
            
            return redirect()->back()->with('success', 'Documento de condiciones subido correctamente');
        }
        
        return redirect()->back()->with('error', 'Error al subir el archivo');
    }
    
    // Descargar documento
    public function descargar($tipo, $id)
    {
        $modelo = $this->getModelo($tipo);
        $documento = $modelo::findOrFail($id);
        
        if (Storage::disk('public')->exists($documento->archivo)) {
            return Storage::disk('public')->download($documento->archivo, $documento->nombre_archivo);
        }
        
        return redirect()->back()->with('error', 'Archivo no encontrado');
    }
    
    // Mostrar documentos de una empresa específica (VISTA CLIENTE)
public function mostrarDocumentosCliente($nit)
{
    $empresa = Empresa::where('nit', $nit)->firstOrFail();
    
    $consolidados = Consolidado::where('empresa_nit', $nit)
        ->orderBy('fecha_documento', 'desc')
        ->get();
        
    $profesiogramas = Profesiograma::where('empresa_nit', $nit)
        ->orderBy('fecha_documento', 'desc')
        ->get();
        
    $dcondiciones = Dcondicione::where('empresa_nit', $nit)
        ->orderBy('fecha_documento', 'desc')
        ->get();
        
    return view('documentos-empresa.cliente-show', compact('empresa', 'consolidados', 'profesiogramas', 'dcondiciones'));
}
    // Eliminar documento
    public function eliminar($tipo, $id)
    {
        $modelo = $this->getModelo($tipo);
        $documento = $modelo::findOrFail($id);
        
        // Eliminar archivo físico
        if (Storage::disk('public')->exists($documento->archivo)) {
            Storage::disk('public')->delete($documento->archivo);
        }
        
        $documento->delete();
        
        return redirect()->back()->with('success', 'Documento eliminado correctamente');
    }
    
    private function getModelo($tipo)
    {
        $modelos = [
            'consolidado' => Consolidado::class,
            'profesiograma' => Profesiograma::class,
            'dcondicione' => Dcondicione::class
        ];
        
        return $modelos[$tipo];
    }
}