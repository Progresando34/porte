@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar_docs') {{-- Sidebar --}}
    </div>

    <div class="col-md-9">
        <h2>Consultar Historias</h2>

        <div class="p-4 mb-4 rounded shadow" style="background-color: #b5ff60ff;">
            <form action="{{ route('client.consulta') }}" method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                <select name="filtro" class="form-select" style="min-width: 180px;">
                    <option value="">Buscar por...</option>
                    <option value="nombre">Nombre</option>
                    <option value="cedula">Cédula</option>
                </select>

                <input type="text" name="valor" class="form-control" placeholder="Escribe nombre o cédula" required style="min-width: 200px;" />

                <button type="submit" class="btn btn-success">Consultar</button>
            </form>
        </div>

        {{-- Resultados de la consulta --}}
{{-- Resultados de la consulta --}}
@if(isset($clientes) && count($clientes) > 0)
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Email</th>
                <th>Documento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->cedula }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td>
           @if($cliente->archivo_certificado && Storage::disk('public')->exists($cliente->archivo_certificado))
    <h5>Vista previa del certificado:</h5>
    <h5>Vista previa del certificado:</h5>
<embed src="{{ route('ver.certificado', ['filename' => $cliente->archivo_certificado]) }}" type="application/pdf" width="100%" height="600px" />

<br><br>

<a href="{{ route('descargar.certificado', basename($cliente->archivo_certificado)) }}">
    Descargar
</a>


@else
    <p class="text-danger">El certificado no está disponible.</p>
@endif
</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@elseif(isset($clientes))
    <p class="mt-4 text-danger">No se encontraron resultados.</p>
@endif


 
            Documentos<br>Legales
        </a>
    </div>
</div>
@endsection

