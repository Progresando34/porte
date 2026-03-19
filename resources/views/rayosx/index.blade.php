@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Listado de Rayos X</h2>

    <a href="{{ route('rayosx.create') }}" class="btn btn-primary mb-3">
        Nuevo Registro
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Fecha</th>
                <th>Archivo</th>
            </tr>
        </thead>

        <tbody>
            @forelse($registros as $item)

                @php
                    $rutaCompleta = storage_path('app/public/' . $item->ruta);
                @endphp

                <tr>
                    <td>{{ $item->nombre }}</td>
                    <td>{{ $item->cedula }}</td>
                    <td>{{ $item->fecha_rx }}</td>

                    <td>
                        {{ $item->ruta }} <br>

                        @if(file_exists($rutaCompleta))
                            <span style="color:green;">✔ EXISTE</span><br>
                        @else
                            <span style="color:red;">❌ NO EXISTE</span><br>
                        @endif

                        <a href="{{ url('/ver-rx/' . $item->id) }}" target="_blank" class="btn btn-sm btn-success">
                            Ver
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="4">No hay registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection