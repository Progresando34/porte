@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Usuario</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group mb-3">
            <label>Nombre:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Correo electrónico:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Confirmar contraseña:</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Perfil:</label>
            <select name="profile_id" class="form-control" required>
                <option value="">-- Selecciona un perfil --</option>
                @foreach($perfiles as $perfil)
                    <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Prefijos de Certificados (acceso):</label>
            <div class="row">
                @foreach($prefijos as $prefijo)
                    <div class="col-md-3 mb-2">
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
            <small class="text-muted">Selecciona los tipos de certificados a los que tendrá acceso el usuario.</small>
        </div>

        <div class="form-group mb-3">
            <label>Avatar:</label>
            <input type="file" name="avatar" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Registrar</button>
<a href="{{ url('/usuarios') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection