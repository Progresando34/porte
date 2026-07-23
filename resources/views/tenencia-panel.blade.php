<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Consulta - Tenencia</title>
    
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
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                
                <!-- TARJETA PRINCIPAL -->
                <div class="card-panel">
                    
                    <!-- LOGO -->
                    <div class="logo-container">
                        <img src="{{ asset('images/logoconjunto.png') }}" alt="Logo Empresa">
                    </div>
                    
                    <!-- HEADER -->
                    <div class="text-center mb-4">
                        <h1 class="titulo-principal">Consulta de Tenencia</h1>
                        <p class="subtitulo">Ingresa un documento o código para consultar</p>
                        <span class="badge-publico">
                       
                      
                        </span>
                    </div>
                    
                    <!-- FORMULARIO -->
                    <form action="{{ route('tenencia.resultados') }}" method="GET">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <div class="input-wrapper">
                                    <i class="fas fa-search input-icon"></i>
                                    <input 
                                        type="text" 
                                        class="input-busqueda" 
                                        name="busqueda" 
                                        placeholder="Número de documento, código o nombre..."
                                        required
                                        autofocus
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn-buscar">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- BÚSQUEDAS RÁPIDAS 
                    <div class="mt-4 pt-3" style="border-top: 1px solid var(--gris-borde);">
                        <small style="color: var(--gris-claro);">Búsquedas rápidas:</small>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button class="btn-rapido" onclick="buscarRapido('TEN-001')">
                                <i class="fas fa-hashtag"></i> TEN-001
                            </button>
                            <button class="btn-rapido" onclick="buscarRapido('123456789')">
                                <i class="fas fa-id-card"></i> 123456789
                            </button>
                            <button class="btn-rapido" onclick="buscarRapido('activo')">
                                <i class="fas fa-check-circle"></i> Activos
                            </button>
                        </div>
                    </div>
                    -->
                </div>
                
                <!-- INFO
                <div class="card-panel-light">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="info-box">
                                <i class="fas fa-database"></i>
                                <div class="label">Registros</div>
                                <div class="value">{{ $totalRegistros ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-box">
                                <i class="fas fa-clock"></i>
                                <div class="label">Actualizado</div>
                                <div class="value">{{ $ultimaActualizacion ?? 'Hoy' }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-box">
                                <i class="fas fa-shield-alt"></i>
                                <div class="label">Estado</div>
                                <div class="value" style="color: var(--verde-oscuro);">Activo</div>
                            </div>
                        </div>
                    </div>
                </div>
 -->

                
            </div>
        </div>
    </div>
    
    <script>
    function buscarRapido(termino) {
        document.querySelector('input[name="busqueda"]').value = termino;
        document.querySelector('form').submit();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>