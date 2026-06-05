<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos - {{ $empresa->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .document-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .document-card:hover {
            transform: translateY(-3px);
        }
        .document-icon {
            font-size: 40px;
            color: #2c3e50;
            margin-right: 15px;
        }
        .btn-download {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-download:hover {
            background: #229954;
            transform: scale(1.05);
        }
        .header {
            background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
        }
        .badge-doc {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-building"></i> {{ $empresa->nombre }}</h2>
                    <p class="mb-0 mt-2">
                        <i class="fas fa-id-card"></i> NIT: {{ $empresa->nit }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ url('/citas') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

    
        <div class="mb-4">
            <h3><i class="fas fa-file-alt text-primary"></i>Consolidado</h3>
            <hr>
            @if($consolidados->count() > 0)
                @foreach($consolidados as $doc)
                <div class="document-card p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-file-pdf document-icon text-danger"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">{{ $doc->nombre_archivo }}</h6>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt"></i> {{ date('d/m/Y', strtotime($doc->fecha_documento)) }}
                                | <i class="fas fa-database"></i> {{ $doc->tamanio }} KB
                            </small>
                            @if($doc->descripcion)
                                <p class="mb-0 mt-1 small">{{ $doc->descripcion }}</p>
                            @endif
                        </div>
                        <div class="col-auto">
                            <a href="{{ url('/documentos/descargar/consolidado/' . $doc->id) }}" class="btn-download" target="_blank">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay documentos consolidados para esta empresa.
                </div>
            @endif
        </div>

  
        <div class="mb-4">
            <h3><i class="fas fa-chart-line text-success"></i> Profesiogramas</h3>
            <hr>
            @if($profesiogramas->count() > 0)
                @foreach($profesiogramas as $doc)
                <div class="document-card p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-chart-bar document-icon text-success"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">{{ $doc->nombre_archivo }}</h6>
                            @if($doc->cargo)
                                <span class="badge-doc"><i class="fas fa-briefcase"></i> {{ $doc->cargo }}</span>
                            @endif
                            <small class="text-muted d-block mt-1">
                                <i class="far fa-calendar-alt"></i> {{ date('d/m/Y', strtotime($doc->fecha_documento)) }}
                                | <i class="fas fa-database"></i> {{ $doc->tamanio }} KB
                            </small>
                            @if($doc->descripcion)
                                <p class="mb-0 mt-1 small">{{ $doc->descripcion }}</p>
                            @endif
                        </div>
                        <div class="col-auto">
                            <a href="{{ url('/documentos/descargar/profesiograma/' . $doc->id) }}" class="btn-download" target="_blank">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay profesiogramas para esta empresa.
                </div>
            @endif
        </div>

  
        <div class="mb-4">
            <h3><i class="fas fa-notes-medical text-info"></i> Condiciones de Salud</h3>
            <hr>
            @if($dcondiciones->count() > 0)
                @foreach($dcondiciones as $doc)
                <div class="document-card p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-stethoscope document-icon text-info"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">{{ $doc->nombre_archivo }}</h6>
                            <span class="badge-doc">
                                <i class="fas fa-tag"></i> 
                                @switch($doc->tipo_condicion)
                                    @case('medica') Médica @break
                                    @case('laboral') Laboral @break
                                    @case('psicosocial') Psicosocial @break
                                    @case('ambiental') Ambiental @break
                                    @default {{ $doc->tipo_condicion }}
                                @endswitch
                            </span>
                            <small class="text-muted d-block mt-1">
                                <i class="far fa-calendar-alt"></i> {{ date('d/m/Y', strtotime($doc->fecha_documento)) }}
                                | <i class="fas fa-database"></i> {{ $doc->tamanio }} KB
                            </small>
                            @if($doc->descripcion)
                                <p class="mb-0 mt-1 small">{{ $doc->descripcion }}</p>
                            @endif
                        </div>
                        <div class="col-auto">
                            <a href="{{ url('/documentos/descargar/dcondicione/' . $doc->id) }}" class="btn-download" target="_blank">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay dictámenes o condiciones para esta empresa.
                </div>
            @endif
        </div>
    </div>
</body>
</html>