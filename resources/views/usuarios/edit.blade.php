@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Usuario</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('usuarios.update', $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">Información Básica</div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Nombre:</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Correo electrónico:</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Contraseña (dejar en blanco para no cambiar):</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirmar contraseña:</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label>Perfil:</label>
                            <select name="profile_id" class="form-control" required>
                                @foreach($perfiles as $perfil)
                                    <option value="{{ $perfil->id }}" 
                                            {{ $user->profile_id == $perfil->id ? 'selected' : '' }}>
                                        {{ $perfil->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Avatar</div>
                    <div class="card-body text-center">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle mb-3" 
                                 width="120" height="120">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 120px; height: 120px;">
                                <span class="text-white display-4">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label>Cambiar avatar:</label>
                            <input type="file" name="avatar" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Prefijos de Certificados (acceso)</div>
            <div class="card-body">
                <div class="row">
                    @foreach($prefijos as $prefijo)
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="prefijos[]" 
                                       value="{{ $prefijo->id }}" 
                                       id="prefijo_{{ $prefijo->id }}"
                                       {{ in_array($prefijo->id, $user->obtenerPrefijosIds()) ? 'checked' : '' }}>
                                <label class="form-check-label" for="prefijo_{{ $prefijo->id }}">
                                    <strong>{{ $prefijo->prefijo }}</strong> - {{ $prefijo->descripcion }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">Selecciona los tipos de certificados a los que tendrá acceso el usuario.</small>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </div>
    </form>
</div>
@endsection