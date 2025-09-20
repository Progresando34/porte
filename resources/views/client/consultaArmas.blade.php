@extends('layouts.simple') {{-- Este layout no tiene sidebar --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            {{-- Contenedor gris con espacio y sombra --}}
            <div class="p-4 rounded shadow-sm w-100 mx-0" style="background-color: #f0f0f0;">

                {{-- Formulario de búsqueda --}}
                <div class="p-4 mb-4 bg-white rounded shadow-sm">
                  <form action="{{ route('client.consultaArmas') }}" method="GET" class="row g-2 align-items-end">
    <div class="col-12 col-md-3">
        <label for="filtro" class="form-label">Buscar por</label>
        <select name="filtro" class="form-select" id="filtroSelect" required>
            <option value="">Seleccionar</option>
            <option value="nombre">Nombre</option>
            <option value="cedula">Cédula</option>
            <option value="codigo_control">Número de Control</option>
        </select>
    </div>

    {{-- Campo valor único --}}
    <div class="col-12 col-md-5" id="valorUnico">
        <label for="valor" class="form-label">Valor</label>
        <input type="text" name="valor" class="form-control" id="valorInput" />
    </div>

    {{-- Campos múltiples cédulas --}}
{{-- Campos múltiples cédulas --}}
<div class="col-12 col-md-5" id="valoresMultiples" style="display: none;">
    <label class="form-label">Cédulas</label>
    <div id="inputsCedulas">
        <input type="text" name="cedulas_multiple[]" class="form-control mb-2" placeholder="Ingrese una cédula">
    </div>
    <button type="button" class="btn btn-sm btn-outline-success" id="addCedulaBtn">
        + Agregar otra cédula
    </button>
</div>


    <div class="col-12 col-md-4 d-flex flex-column justify-content-end">
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

                   <div class="table-responsive">
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
                    <td>{{ $cliente->fecha_atencion ? \Carbon\Carbon::parse($cliente->fecha_atencion)->format('d/m/Y') : 'Sin fecha' }}</td>
                    <td>
                        @if($cliente->activo)
                            <span class="badge" style="background-color: #04af0dff; color: white;">Apto</span>
                        @else
                            <span class="badge bg-danger">No Apto</span>
                        @endif
                    </td>
                    <td>
                        @if($cliente->certificado && Storage::disk('public')->exists($cliente->certificado))
                            <a href="{{ route('descargar.certificado', basename($cliente->certificado)) }}"
                               class="btn btn-sm"
                               style="background-color: #04af0dff; color: white;">
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
</div>
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
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleCedulasBtn');
    const valoresMultiples = document.getElementById('valoresMultiples');
    const valorUnico = document.getElementById('valorUnico');
    const filtroSelect = document.getElementById('filtroSelect');
    const inputsCedulas = document.getElementById('inputsCedulas');
    const addCedulaBtn = document.getElementById('addCedulaBtn');

    let modoMultiple = false;

    filtroSelect.addEventListener('change', function() {
        if (this.value === 'cedula') {
            toggleBtn.style.display = 'block';
        } else {
            toggleBtn.style.display = 'none';
            valoresMultiples.style.display = 'none';
            valorUnico.style.display = 'block';
            modoMultiple = false;
        }
    });

    toggleBtn.addEventListener('click', function() {
        modoMultiple = !modoMultiple;
        if (modoMultiple) {
            valoresMultiples.style.display = 'block';
            valorUnico.style.display = 'none';
            this.textContent = 'Buscar por cédula única';
        } else {
            valoresMultiples.style.display = 'none';
            valorUnico.style.display = 'block';
            this.textContent = 'Buscar por múltiples cédulas';
        }
    });

    // Agregar más inputs dinámicamente
    addCedulaBtn.addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'cedulas_multiple[]';
        input.classList.add('form-control', 'mb-2');
        input.placeholder = "Ingrese otra cédula";
        inputsCedulas.appendChild(input);
    });

    if (filtroSelect.value === 'cedula') {
        toggleBtn.style.display = 'block';
    }
});

</script>




@endpush
