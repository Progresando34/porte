<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Tenencia</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/tenencia.css') }}">
</head>
<body>
    <!-- HEADER CON GRADIENTE VERDE -->
    <div class="header-gradient">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo-text">
                    <i class="fas fa-shield-alt"></i>
                    Sistema de Consulta - Porte y Tenencia de Armas de Fuego - IPS PROGRESANDO EN SALUD
                </div>
                <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                  
                    
                </span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                
                <!-- VOLVER -->
                <div class="mt-3">
                    <a href="{{ route('tenencia.panel.consulta') }}" class="btn-volver">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
                
                <!-- LOGO PEQUEÑO Y RESUMEN -->
                <div class="card-panel-light">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-3 text-center text-md-start">
                            <img src="{{ asset('images/logoi.png') }}" alt="Logo" style="max-width: 120px; height: auto;">
                        </div>
                        <div class="col-12 col-md-6 text-center">
                            <div>
                                <i class="fas fa-search me-2" style="color: var(--verde-oscuro);"></i>
                                Resultados para: <strong>"{{ $termino ?? 'Todos' }}"</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 text-center text-md-end">
                            <span class="badge-publico">
                                <i class="fas fa-list me-1"></i>
                                {{ $resultados->count() ?? 0 }} resultados
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- TABLA -->
                @if(isset($resultados) && $resultados->count() > 0)
                <div class="card-panel-light p-0">
                    <div class="table-responsive">
                        <table class="table table-tenencia mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha de Expedición</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th class="text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resultados as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span style="background: var(--gris-fondo); padding: 0.2rem 0.8rem; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">
                                            {{ $item->codigo ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar">
                                                {{ substr($item->nombre ?? 'N', 0, 1) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 500;">{{ $item->nombre ?? 'Sin nombre' }}</div>
                                                <small style="color: var(--gris-claro);">{{ $item->apellido ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->documento ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $estado = $item->estado ?? 'No Acto';
                                            $clase = $estado == 'Acto' ? 'badge-activo' : 'badge-inactivo';
                                        @endphp
                                        <span class="badge-estado {{ $clase }}">
                                            <i class="fas fa-circle me-1" style="font-size: 5px; vertical-align: middle;"></i>
                                            {{ ucfirst($estado) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-ver" data-bs-toggle="modal" data-bs-target="#detalleModal{{ $item->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- PAGINACIÓN -->
                @if(method_exists($resultados, 'links'))
                <div class="mt-4 d-flex justify-content-center">
                    {{ $resultados->links('pagination::bootstrap-5') }}
                </div>
                @endif
                
                @else
                <!-- VACÍO -->
                <div class="card-panel-light text-center py-5">
                    <i class="fas fa-search-minus empty-icon"></i>
                    <h3 class="empty-title">No se encontraron resultados</h3>
                    <p class="empty-text">
                        No hay registros que coincidan con tu búsqueda.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('tenencia.panel.consulta') }}" class="btn-buscar" style="width: auto; padding: 0.6rem 2rem;">
                            <i class="fas fa-search me-2"></i>Nueva búsqueda
                        </a>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
    
    <!-- MODALES -->
    @if(isset($resultados) && $resultados->count() > 0)
    @foreach($resultados as $item)
    <div class="modal fade" id="detalleModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-contenido">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2" style="color: var(--verde-oscuro);"></i>
                        Detalle de Tenencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Código</div>
                                <div class="value">{{ $item->codigo ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Documento</div>
                                <div class="value">{{ $item->documento ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Nombre</div>
                                <div class="value">{{ $item->nombre ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Apellido</div>
                                <div class="value">{{ $item->apellido ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Estado</div>
                                <div class="value">
                                    @php
                                        $estado = $item->estado ?? 'No Acto';
                                        $color = $estado == 'Acto' ? 'var(--verde-oscuro)' : '#c0392b';
                                    @endphp
                                    <span style="color: {{ $color }}; font-weight: 600;">
                                        {{ ucfirst($estado) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detalle-item">
                                <div class="label">Fecha</div>
                                <div class="value">{{ $item->fecha ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" style="background: var(--gris-fondo); border: 1px solid var(--gris-borde); color: var(--gris-medio); border-radius: 8px; padding: 0.5rem 1.5rem;" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cerrar
                    </button>
                    <button type="button" class="btn" style="background: linear-gradient(135deg, var(--verde-principal), var(--verde-oscuro)); color: white; border: none; border-radius: 8px; padding: 0.5rem 1.5rem;" onclick="copyToClipboard('{{ $item->codigo ?? '' }}')">
                        <i class="fas fa-copy me-2"></i>Copiar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
    
    <script>
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                alert('✅ Código copiado');
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('✅ Código copiado');
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>