@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Usuarios</h2>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Prefijos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($usuario->avatar)
                                    <img src="{{ Storage::url($usuario->avatar) }}" 
                                         alt="{{ $usuario->name }}" 
                                         class="rounded-circle me-2" 
                                         width="30" height="30">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 30px; height: 30px;">
                                        <span class="text-white">{{ substr($usuario->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                {{ $usuario->name }}
                            </div>
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            <span class="badge bg-info">{{ $usuario->profile->name }}</span>
                        </td>
                        <td>
                            @if($usuario->prefijos->count() > 0)
                                @foreach($usuario->prefijos->take(3) as $prefijo)
                                    <span class="badge bg-primary me-1">{{ $prefijo->prefijo }}</span>
                                @endforeach
                                @if($usuario->prefijos->count() > 3)
                                    <span class="text-muted">+{{ $usuario->prefijos->count() - 3 }} más</span>
                                @endif
                            @else
                                <span class="text-muted">Sin prefijos</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('usuarios.show', $usuario->id) }}" 
                               class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('usuarios.edit', $usuario->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
</div>
@endsection