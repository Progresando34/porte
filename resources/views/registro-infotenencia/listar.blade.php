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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($certificados as $certificado)
                                    <tr>
                                        <td>{{ $certificado->id }}</td>
                                        <td>
                                            @if($certificado->resultado_apto)
                                                <span class="badge bg-success">Apto ✅</span>
                                            @else
                                                <span class="badge bg-danger">No Apto ❌</span>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>