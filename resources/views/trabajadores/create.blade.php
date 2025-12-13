@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Trabajador</h2>

    <form method="POST" action="{{ route('trabajadores.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Información Personal</div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Nombre Completo: *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Cédula: *</label>
                            <input type="text" name="cedula" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Nombre de Usuario: *</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Contraseña: *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirmar Contraseña: *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" checked>
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
                                        <input class="form-check-input" type="checkbox" 
                                               name="prefijos[]" 
                                               value="{{ $prefijo->id }}" 
                                               id="prefijo_{{ $prefijo->id }}">
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
            <button type="submit" class="btn btn-primary">Registrar Trabajador</button>
        </div>
    </form>
</div>
@endsection