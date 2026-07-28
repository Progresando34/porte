<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Infotenencia</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --verde-principal: #2ecc71;
            --verde-oscuro: #27ae60;
            --gris-fondo: #f8f9fa;
            --gris-borde: #e9ecef;
            --gris-medio: #6c757d;
            --gris-claro: #adb5bd;
            --rojo: #e74c3c;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, var(--verde-oscuro), var(--verde-principal));
            padding: 1.5rem 0;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo-text {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .logo-text i {
            margin-right: 10px;
        }
        
        .btn-volver {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            color: var(--verde-oscuro);
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-volver:hover {
            background: var(--gris-fondo);
            color: var(--verde-oscuro);
            text-decoration: none;
        }
        
        .card-panel-light {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid var(--gris-borde);
        }
        
        .badge-publico {
            background: var(--gris-fondo);
            color: var(--gris-medio);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .table-tenencia {
            margin-bottom: 0;
        }
        
        .table-tenencia thead th {
            background: var(--gris-fondo);
            color: var(--gris-medio);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
            border-bottom: 2px solid var(--gris-borde);
        }
        
        .table-tenencia tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gris-borde);
        }
        
        .table-tenencia tbody tr:hover {
            background: rgba(46, 204, 113, 0.05);
        }
        
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--verde-principal), var(--verde-oscuro));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .badge-estado {
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-activo {
            background: rgba(46, 204, 113, 0.15);
            color: var(--verde-oscuro);
        }
        
        .badge-inactivo {
            background: rgba(231, 76, 60, 0.15);
            color: var(--rojo);
        }
        
        .btn-ver {
            background: var(--gris-fondo);
            border: none;
            color: var(--gris-medio);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .btn-ver:hover {
            background: var(--verde-principal);
            color: white;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--gris-claro);
            margin-bottom: 1rem;
        }
        
        .empty-title {
            color: var(--gris-medio);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-text {
            color: var(--gris-claro);
        }
        
        .btn-buscar {
            display: inline-block;
            background: linear-gradient(135deg, var(--verde-principal), var(--verde-oscuro));
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-buscar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
            color: white;
            text-decoration: none;
        }
        
        /* Modal */
        .modal-contenido {
            border-radius: 16px;
            border: none;
        }
        
        .detalle-item {
            padding: 0.75rem;
            background: var(--gris-fondo);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .detalle-item .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--gris-claro);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .detalle-item .value {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
    </style>
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
                    <i class="fas fa-calendar-alt me-1"></i> {{ date('d/m/Y') }}
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
                        <i class="fas fa-arrow-left me-2"></i>Volver a buscar
                    </a>
                </div>
                
                <!-- RESUMEN -->
                <div class="card-panel-light">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-3 text-center text-md-start">
                            <img src="{{ asset('images/logoconjunto.png') }}" alt="Logo" style="max-width: 250px; height: auto;">
                        </div>
                        <div class="col-12 col-md-6 text-center">
                            <div>
                                <i class="fas fa-search me-2" style="color: var(--verde-oscuro);"></i>
                                Resultados para cédula: <strong>"{{ $cedula ?? 'Todos' }}"</strong>
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
                
                <!-- TABLA DE RESULTADOS -->
                @if(isset($resultados) && $resultados->count() > 0)
                <div class="card-panel-light p-0">
                    <div class="table-responsive">
                        <table class="table table-tenencia mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha de Expedición</th>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Resultado</th>
                                    <th>Dirección IPS</th>
                                    <th class="text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resultados as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span style="background: var(--gris-fondo); padding: 0.2rem 0.8rem; border-radius: 4px; font-weight: 500; font-size: 0.85rem;">
                                            {{ $item->fecha_expedicion ? date('d/m/Y', strtotime($item->fecha_expedicion)) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar">
                                                {{ substr($item->nombre ?? 'N', 0, 1) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 500;">{{ $item->nombre ?? 'Sin nombre' }}</div>
                                                <small style="color: var(--gris-claro);">ID: {{ $item->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->cedula ?? 'N/A' }}</td>
                                    <td>

@php
    $valor = $item->resultado_apto ?? 0;
    if ($valor == 1) {
        $clase = 'badge-activo';
        $texto = 'Apto';
    } elseif ($valor == 2) {
        $clase = 'badge-warning';
        $texto = 'Aplazado';
    } else {
        $clase = 'badge-inactivo';
        $texto = 'No Apto';
    }
@endphp

                                        <span class="badge-estado {{ $clase }}">
                                            <i class="fas fa-circle me-1" style="font-size: 5px; vertical-align: middle;"></i>
                                            {{ $texto }}
                                        </span>
                                    </td>
                                    <td>
                                        <small style="font-size: 0.75rem; color: var(--gris-medio);">
                                            {{ $item->direccion_ips ?? 'N/A' }}
                                        </small>
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
                
                @else
                <!-- VACÍO -->
                <div class="card-panel-light text-center py-5">
                    <i class="fas fa-search-minus empty-icon"></i>
                    <h3 class="empty-title">No se encontraron resultados</h3>
                    <p class="empty-text">
                        No hay registros que coincidan con la cédula <strong>"{{ $cedula }}"</strong>.
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
    
    <!-- MODALES DE DETALLE -->
    @if(isset($resultados) && $resultados->count() > 0)
    @foreach($resultados as $item)
    <div class="modal fade" id="detalleModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-contenido">
                <div class="modal-header" style="border-bottom: 2px solid var(--verde-principal);">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2" style="color: var(--verde-oscuro);"></i>
                        Detalle del Registro #{{ $item->id }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Nombre</div>
                                <div class="value">{{ $item->nombre ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Cédula</div>
                                <div class="value">{{ $item->cedula ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Fecha Expedición</div>
                                <div class="value">{{ $item->fecha_expedicion ? date('d/m/Y', strtotime($item->fecha_expedicion)) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Resultado Apto</div>
                                <div class="value">


@php
    $valor = $item->resultado_apto ?? 0;
    if ($valor == 1) {
        $color = 'var(--verde-oscuro)';
        $texto = 'Apto';
    } elseif ($valor == 2) {
        $color = '#f39c12';
        $texto = 'Aplazado';
    } else {
        $color = 'var(--rojo)';
        $texto = 'No Apto';
    }
@endphp
<span style="color: {{ $color }}; font-weight: 600;">
    {{ $texto }}
</span>


                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Dirección IPS</div>
                                <div class="value" style="font-size: 0.85rem;">{{ $item->direccion_ips ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Sede IPS</div>
                                <div class="value">{{ $item->sede_ips ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detalle-item">
                                <div class="label">Archivo Certificado</div>
                                <div class="value">
                                    @if($item->archivo_certificado)
                                        <a href="{{ asset('storage/' . $item->archivo_certificado) }}" 
                                           target="_blank" 
                                           style="color: var(--verde-oscuro); text-decoration: none; font-weight: 500;">
                                            <i class="fas fa-file-pdf me-2"></i>Ver certificado
                                        </a>
                                    @else
                                        <span style="color: var(--gris-claro);">Sin archivo adjunto</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Registrado</div>
                                <div class="value" style="font-size: 0.85rem;">{{ $item->created_at ? date('d/m/Y H:i', strtotime($item->created_at)) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <div class="label">Última actualización</div>
                                <div class="value" style="font-size: 0.85rem;">{{ $item->updated_at ? date('d/m/Y H:i', strtotime($item->updated_at)) : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--gris-borde);">
                    <button type="button" class="btn" style="background: var(--gris-fondo); border: 1px solid var(--gris-borde); color: var(--gris-medio); border-radius: 8px; padding: 0.5rem 1.5rem;" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cerrar
                    </button>
                    <button type="button" class="btn" style="background: linear-gradient(135deg, var(--verde-principal), var(--verde-oscuro)); color: white; border: none; border-radius: 8px; padding: 0.5rem 1.5rem;" onclick="copyToClipboard('{{ $item->cedula ?? '' }}')">
                        <i class="fas fa-copy me-2"></i>Copiar cédula
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
                alert('✅ Cédula copiada al portapapeles');
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('✅ Cédula copiada al portapapeles');
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>