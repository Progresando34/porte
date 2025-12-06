@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detalles del Usuario</h2>
        <div>
            <a href="{{ route('usuarios.edit', $user->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header text-center">Avatar</div>
                <div class="card-body text-center">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle mb-3" 
                             width="150" height="150">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 150px; height: 150px;">
                            <span class="text-white display-3">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Información del Usuario</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="200">ID:</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Perfil:</th>
                            <td>
                                <span class="badge bg-info">{{ $user->profile->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de creación:</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Última actualización:</th>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Prefijos Asignados</div>
                <div class="card-body">
                    @if($user->prefijos->count() > 0)
                        <div class="row">
                            @foreach($user->prefijos as $prefijo)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <span class="badge bg-primary">{{ $prefijo->prefijo }}</span>
                                            </h5>
                                            <p class="card-text">{{ $prefijo->descripcion }}</p>
                                            <small class="text-muted">
                                                Estado: 
                                                <span class="{{ $prefijo->activo ? 'text-success' : 'text-danger' }}">
                                                    {{ $prefijo->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="alert alert-info">
                            <strong>Total:</strong> {{ $user->prefijos->count() }} prefijos asignados
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Este usuario no tiene prefijos asignados. No podrá ver certificados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection