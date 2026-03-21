@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Mensajes de alerta -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header con título y contador -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-x-ray me-2"></i>Listado de Rayos X
            </h2>
            <p class="text-muted mt-2">Total de registros: {{ $registros->count() }}</p>
        </div>
        <a href="{{ route('rayosx.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Registro
        </a>
    </div>

    <!-- Tarjeta con la tabla -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-user me-1"></i> Nombre</th>
                            <th><i class="fas fa-id-card me-1"></i> Cédula</th>
                            <th><i class="fas fa-calendar-alt me-1"></i> Fecha</th>
                            <th><i class="fas fa-file-alt me-1"></i> Archivo</th>
                            <th><i class="fas fa-cog me-1"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $item)
                            @php
                                $rutaCompleta = storage_path('app/public/' . $item->ruta);
                                $existeArchivo = file_exists($rutaCompleta);
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <strong>{{ $item->nombre }}</strong>
                                </td>
                                <td class="align-middle">{{ $item->cedula }}</td>
                                <td class="align-middle">
                                    {{ \Carbon\Carbon::parse($item->fecha_rx)->format('d/m/Y') }}
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted mb-1">
                                            <i class="fas fa-folder me-1"></i> {{ $item->ruta }}
                                        </small>
                                        @if($existeArchivo)
                                            <span class="badge bg-success" style="width: fit-content;">
                                                <i class="fas fa-check-circle me-1"></i> Archivo disponible
                                            </span>
                                        @else
                                            <span class="badge bg-danger" style="width: fit-content;">
                                                <i class="fas fa-times-circle me-1"></i> Archivo no encontrado
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="{{ url('/ver-rx/' . $item->id) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success"
                                           title="Ver archivo">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <form action="{{ route('rayosx.destroy', $item->id) }}" 
                                              method="POST" 
                                              style="display: inline-block;"
                                              onsubmit="return confirmDelete(event, '{{ $item->nombre }}', '{{ $item->cedula }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Eliminar registro">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">No hay registros de rayos X</p>
                                        <a href="{{ route('rayosx.create') }}" class="btn btn-sm btn-primary mt-3">
                                            <i class="fas fa-plus-circle me-1"></i> Crear primer registro
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script de confirmación mejorado -->
<script>
    function confirmDelete(event, nombre, cedula) {
        event.preventDefault();
        
        if (confirm(`¿Está seguro de eliminar el registro de ${nombre} con cédula ${cedula}?\n\nEsta acción eliminará permanentemente el registro y el archivo asociado.`)) {
            event.target.submit();
        }
        
        return false;
    }
</script>

<!-- Estilos adicionales para mejorar la visualización -->
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem;
        transition: all 0.2s;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endsection