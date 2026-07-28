<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Certificados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-list me-2"></i>Listado de Certificados
                </h4>
                <a href="{{ route('registro-infotenencia.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Nuevo Registro
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($certificados->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay registros de certificados</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Resultado</th>
                                    <th>Dirección IPS</th>
                                    <th>Sede IPS</th>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Fecha Expedición</th>
                                    <th>Archivo</th>
                                    <th>Fecha Creación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($certificados->sortBy('id') as $certificado)
                                    <tr>
                                        <td>{{ $certificado->id }}</td>
                                        <td>
                                            @if($certificado->resultado_apto)
                                                <span class="badge bg-success">Apto</span>
                                            @else
                                                <span class="badge bg-danger">No Apto</span>
                                            @endif
                                        </td>
                                        <td>{{ $certificado->direccion_ips ?? 'N/A' }}</td>
                                        <td>{{ $certificado->sede_ips ?? 'N/A' }}</td>
                                        <td>{{ $certificado->nombre }}</td>
                                        <td>{{ $certificado->cedula }}</td>
                                        <td>{{ $certificado->fecha_expedicion ? date('d/m/Y', strtotime($certificado->fecha_expedicion)) : 'N/A' }}</td>
                                        <td>
                                            @if($certificado->archivo_certificado)
                                                <a href="{{ asset('storage/' . $certificado->archivo_certificado) }}" 
                                                   target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-file"></i> Ver
                                                </a>
                                            @else
                                                <span class="text-muted">Sin archivo</span>
                                            @endif
                                        </td>
                                        <td>{{ $certificado->created_at ? date('d/m/Y H:i', strtotime($certificado->created_at)) : 'N/A' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <!-- Botón Editar -->
                                                <a href="{{ route('registro-infotenencia.edit', $certificado->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Botón Eliminar con confirmación -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmarEliminacion({{ $certificado->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este registro?</p>
                    <p class="text-muted small">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Sí, Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmarEliminacion(id) {
            // Configurar el formulario con la ruta correcta
            const form = document.getElementById('formEliminar');
            form.action = `/registro-infotenencia/${id}`;
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            modal.show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>