<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos - {{ $empresa->nombre }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>
                            <i class="fas fa-building"></i> {{ $empresa->nombre }}
                            <small class="text-white-50">NIT: {{ $empresa->nit }}</small>
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="documentosTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#consolidados">
                                    <i class="fas fa-file-alt"></i> Consolidados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#profesiogramas">
                                    <i class="fas fa-chart-line"></i> Profesiogramas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#dcondiciones">
                                    <i class="fas fa-notes-medical"></i> Dictámenes/Condiciones
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <!-- Consolidados Tab -->
                            <div class="tab-pane fade show active" id="consolidados">
                                @include('partials.documentos-table', [
                                    'tipo' => 'consolidado',
                                    'documentos' => $consolidados,
                                    'formAction' => route('documentos.subir.consolidado', $empresa->nit)
                                ])
                            </div>
                            
                            <!-- Profesiogramas Tab -->
                            <div class="tab-pane fade" id="profesiogramas">
                                @include('partials.documentos-table', [
                                    'tipo' => 'profesiograma',
                                    'documentos' => $profesiogramas,
                                    'formAction' => route('documentos.subir.profesiograma', $empresa->nit),
                                    'extraField' => 'cargo'
                                ])
                            </div>
                            
                            <!-- Dcondiciones Tab -->
                            <div class="tab-pane fade" id="dcondiciones">
                                @include('partials.documentos-table', [
                                    'tipo' => 'dcondicione',
                                    'documentos' => $dcondiciones,
                                    'formAction' => route('documentos.subir.dcondicione', $empresa->nit)
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>