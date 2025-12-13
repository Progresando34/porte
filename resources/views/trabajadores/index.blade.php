@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Trabajadores</h2>
        <a href="{{ route('trabajadores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Trabajador
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Prefijos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trabajadores as $trabajador)
                    <tr>
                        <td>{{ $trabajador->id }}</td>
                        <td>{{ $trabajador->nombre }}</td>
                        <td>{{ $trabajador->cedula }}</td>
                        <td>{{ $trabajador->usuario }}</td>
                        <td>
                            <span class="badge {{ $trabajador->activo ? 'bg-success' : 'bg-danger' }}">
                                {{ $trabajador->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            @if($trabajador->prefijos->count() > 0)
                                @foreach($trabajador->prefijos->take(2) as $prefijo)
                                    <span class="badge bg-primary me-1">{{ $prefijo->prefijo }}</span>
                                @endforeach
                                @if($trabajador->prefijos->count() > 2)
                                    <span class="text-muted">+{{ $trabajador->prefijos->count() - 2 }} más</span>
                                @endif
                            @else
                                <span class="text-muted">Sin prefijos</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('trabajadores.show', $trabajador->id) }}" 
                               class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('trabajadores.edit', $trabajador->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('trabajadores.destroy', $trabajador->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de eliminar este trabajador?')">
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
                {{ $trabajadores->links() }}
            </div>
        </div>
    </div>
</div>
@endsection