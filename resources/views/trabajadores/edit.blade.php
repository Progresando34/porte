@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Trabajador</h2>

    <form method="POST" action="{{ route('trabajadores.update', $trabajador->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Información Personal</div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Nombre Completo: *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="{{ $trabajador->nombre }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Cédula: *</label>
                            <input type="text" name="cedula" class="form-control" 
                                   value="{{ $trabajador->cedula }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Nombre de Usuario: *</label>
                            <input type="text" name="usuario" class="form-control" 
                                   value="{{ $trabajador->usuario }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Contraseña (dejar en blanco para no cambiar):</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirmar Contraseña:</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="activo" 
                                   id="activo" value="1" {{ $trabajador->activo ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">
                                Activo
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Prefijos Asignados</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($prefijos as $prefijo)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        @php
                                            // SOLUCIÓN: Obtener IDs de prefijos directamente
                                            $prefijosIds = $trabajador->prefijos->pluck('id')->toArray();
                                            $checked = in_array($prefijo->id, $prefijosIds) ? 'checked' : '';
                                        @endphp
                                        <input class="form-check-input" type="checkbox" 
                                               name="prefijos[]" 
                                               value="{{ $prefijo->id }}" 
                                               id="prefijo_{{ $prefijo->id }}"
                                               {{ $checked }}>
                                        <label class="form-check-label" for="prefijo_{{ $prefijo->id }}">
                                            <strong>{{ $prefijo->prefijo }}</strong> - {{ $prefijo->descripcion }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('trabajadores.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar Trabajador</button>
        </div>
    </form>
</div>
@endsection