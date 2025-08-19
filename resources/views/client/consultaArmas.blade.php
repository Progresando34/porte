@extends('layouts.simple') {{-- Este layout no tiene sidebar --}}

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-md-10">
            {{-- Contenedor gris con espacio y sombra --}}
            <div class="p-4 rounded shadow-sm w-100 mx-0" style="background-color: #f0f0f0;">

                {{-- Formulario de búsqueda --}}
                <div class="p-4 mb-4 bg-white rounded shadow-sm">
                    <form action="{{ route('client.consultaArmas') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="filtro" class="form-label">Buscar por</label>
                            <select name="filtro" class="form-select" id="filtroSelect" required>
                                <option value="">Seleccionar</option>
                                <option value="nombre">Nombre</option>
                                <option value="cedula">Cédula</option>
                                <option value="codigo_control">Número de Control</option>
                            </select>
                        </div>

                        {{-- Campo valor único --}}
                        <div class="col-md-5" id="valorUnico">
                            <label for="valor" class="form-label">Valor</label>
                           <input type="text" name="valor" class="form-control" id="valorInput" />

                        </div>

                        {{-- Campos múltiples cédulas --}}
                        <div class="col-md-5" id="valoresMultiples" style="display: none;">
                            <label class="form-label">Cédulas</label>
                            <input type="text" name="cedulas[]" class="form-control mb-2" placeholder="Cédula 1" />
                            <input type="text" name="cedulas[]" class="form-control mb-2" placeholder="Cédula 2" />
                            <input type="text" name="cedulas[]" class="form-control" placeholder="Cédula 3" />
                        </div>

                        <div class="col-md-4 d-flex flex-column justify-content-end">
                            <button type="button" id="toggleCedulasBtn"
                                    class="btn btn-outline-primary btn-sm mb-2 w-100">
                                Buscar por múltiples cédulas
                            </button>

                            <button type="submit"
                                    class="btn btn-sm w-100"
                                    style="background-color: #04af0dff; color: white; border: 1px solid #04af0dff;">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Resultados --}}
                @if(isset($clientes) && count($clientes) > 0)
                    <h4 class="mb-3">Resultados de la búsqueda</h4>
                    <table class="table table-bordered mt-4 shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Número de Control</th>
                                <th>Fecha de Atención</th>
                                <th>Estado</th>
                                <th>Certificado</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->nombre }}</td>
                                    <td>{{ $cliente->cedula }}</td>
                                    <td>{{ $cliente->codigo_control }}</td>
                                    <td>
                                        {{ $cliente->fecha_atencion
                                            ? \Carbon\Carbon::parse($cliente->fecha_atencion)->format('d/m/Y')
                                            : 'Sin fecha' }}
                                    </td>
                                    <td>
                                        @if($cliente->activo)
                                            <span class="badge"
                                                style="background-color: #04af0dff; color: white; border: 1px solid #04af0dff;">
                                                Apto
                                            </span>
                                        @else
                                            <span class="badge bg-danger">No Apto</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cliente->certificado && Storage::disk('public')->exists($cliente->certificado))
                                            <a href="{{ route('descargar.certificado', basename($cliente->certificado)) }}"
                                               class="btn btn-sm"
                                               style="background-color: #04af0dff; color: white; border: 1px solid #04af0dff;">
                                                Descargar Certificado
                                            </a>
                                        @else
                                            <span class="text-danger">No disponible</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif(isset($clientes))
                    <p class="mt-4 text-danger">No se encontraron resultados.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Script para alternar campos --}}
@push('scripts')

<script>
    const toggleBtn = document.getElementById('toggleCedulasBtn');
    const valoresMultiples = document.getElementById('valoresMultiples');
    const valorUnico = document.getElementById('valorUnico');
    const valorInput = document.getElementById('valorInput');

    toggleBtn.addEventListener('click', () => {
        if (valoresMultiples.style.display === 'none') {
            valoresMultiples.style.display = 'block';
            valorUnico.style.display = 'none';
            valorInput.value = '';
        } else {
            valoresMultiples.style.display = 'none';
            valorUnico.style.display = 'block';
        }
    });
</script>


@endpush
@endsection
