<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Consulta - Tenencia</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 (gratuito) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ----- VARIABLES DE ESTILO SERIO ----- */
        :root {
            --fondo: #f2f4f8;
            --blanco: #ffffff;
            --gris-oscuro: #076436;
            --azul-profundo: #41b668;
            --gris-texto: #047e0e;
            --gris-borde: #dde1e8;
            --gris-claro: #6c7a8d;
            --dorado: #ffffff;
            --dorado-claro: #ffffff;
            --sombra: 0 8px 30px rgba(0, 0, 0, 0.08);
            --sombra-hover: 0 12px 40px rgba(0, 0, 0, 0.12);
            --radio: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--fondo);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--gris-texto);
            line-height: 1.5;
            padding: 2rem 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ----- HEADER INSTITUCIONAL (sin gradiente llamativo) ----- */
        .header-institucional {
            background-color: var(--azul-profundo);
            border-bottom: 3px solid var(--dorado);
            padding: 0.9rem 0;
            margin-bottom: 2.5rem;
            border-radius: 0 0 var(--radio) var(--radio);
            box-shadow: var(--sombra);
        }

        .header-institucional .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .logo-text {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #ffffff;
            font-weight: 500;
            font-size: 1.05rem;
            letter-spacing: 0.3px;
        }

        .logo-text i {
            color: var(--dorado-claro);
            font-size: 1.4rem;
        }

        .header-badge {
            background-color: rgba(255, 255, 255, 0.08);
            padding: 0.3rem 1rem;
            border-radius: 30px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.8rem;
            font-weight: 400;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* ----- TARJETA PRINCIPAL (seria, limpia) ----- */
        .card-panel {
            background: var(--blanco);
            border-radius: var(--radio);
            padding: 2.5rem 2.2rem;
            box-shadow: var(--sombra);
            border: 1px solid var(--gris-borde);
            transition: box-shadow 0.2s ease;
            margin-bottom: 2rem;
        }

        .card-panel:hover {
            box-shadow: var(--sombra-hover);
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .logo-container img {
            max-height: 70px;
            width: auto;
            opacity: 0.85;
            filter: grayscale(0.1);
        }

        /* Títulos sobrios */
        .titulo-principal {
            font-size: 2rem;
            font-weight: 600;
            color: var(--azul-profundo);
            letter-spacing: -0.5px;
            margin-bottom: 0.2rem;
        }

        .subtitulo {
            color: var(--gris-claro);
            font-size: 1rem;
            font-weight: 400;
            border-bottom: 1px solid var(--gris-borde);
            padding-bottom: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .badge-publico {
            background-color: #eef2f7;
            color: var(--gris-claro);
            font-weight: 400;
            padding: 0.3rem 1.2rem;
            border-radius: 40px;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            display: inline-block;
            margin-top: 0.2rem;
        }

        /* ----- FORMULARIO (minimalista) ----- */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: var(--gris-claro);
            font-size: 1rem;
            opacity: 0.6;
            pointer-events: none;
        }

        .input-busqueda {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #fafbfc;
            transition: 0.15s ease;
            color: var(--gris-oscuro);
        }

        .input-busqueda:focus {
            outline: none;
            border-color: var(--azul-profundo);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(27, 43, 75, 0.08);
        }

        .input-busqueda::placeholder {
            color: #aab3c2;
            font-weight: 300;
        }

        .btn-buscar {
            background-color: var(--azul-profundo);
            border: none;
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            width: 100%;
            transition: 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 1px solid var(--azul-profundo);
            letter-spacing: 0.3px;
        }

        .btn-buscar:hover {
            background-color: #142232;
            border-color: #142232;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(27, 43, 75, 0.2);
        }

        .btn-buscar i {
            font-size: 0.9rem;
        }

        /* ----- BOTONES RÁPIDOS (opcionales, más discretos) ----- */
        .btn-rapido {
            background: transparent;
            border: 1px solid var(--gris-borde);
            color: var(--gris-claro);
            padding: 0.3rem 1.2rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 400;
            transition: 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-rapido i {
            font-size: 0.7rem;
            opacity: 0.6;
        }

        .btn-rapido:hover {
            background-color: var(--azul-profundo);
            border-color: var(--azul-profundo);
            color: white;
        }

        /* ----- INFO BOX (más seria) ----- */
        .card-panel-light {
            background: var(--blanco);
            border-radius: var(--radio);
            padding: 1.5rem 1.8rem;
            border: 1px solid var(--gris-borde);
            box-shadow: var(--sombra);
        }

        .info-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0.5rem 0;
        }

        .info-box i {
            font-size: 1.4rem;
            color: var(--azul-profundo);
            opacity: 0.5;
            margin-bottom: 0.25rem;
        }

        .info-box .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gris-claro);
            font-weight: 500;
        }

        .info-box .value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--azul-profundo);
            margin-top: 0.1rem;
        }

        /* ----- RESPONSIVE (ajustes finos) ----- */
        @media (max-width: 768px) {
            body {
                padding: 1rem 0.75rem;
            }
            .card-panel {
                padding: 1.8rem 1.2rem;
            }
            .titulo-principal {
                font-size: 1.6rem;
            }
            .header-institucional .container {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.4rem;
            }
            .logo-text {
                font-size: 0.9rem;
                flex-wrap: wrap;
            }
            .header-badge {
                font-size: 0.7rem;
                padding: 0.15rem 0.8rem;
            }
            .btn-buscar {
                padding: 0.7rem 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER INSTITUCIONAL (sobrio, sin gradiente) -->
    <div class="header-institucional">
        <div class="container">
            <div class="logo-text">
                <i class="fas fa-shield-alt"></i>
                <span>Sistema de Consulta · Porte y Tenencia de Armas</span>
            </div>
            <div class="header-badge">
                <i class="far fa-clock me-1"></i> IPS PROGRESANDO EN SALUD
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                <!-- TARJETA PRINCIPAL -->
                <div class="card-panel">

                    <!-- LOGO (con opacidad y filtro sutil) -->
                    <div class="logo-container">
                        <img src="{{ asset('images/logoconjunto.png') }}" alt="Logo institucional">
                    </div>

                    <!-- TÍTULOS -->
                    <div class="text-center mb-4">
                        <h1 class="titulo-principal">Consulta de Tenencia</h1>
                        <p class="subtitulo">Ingrese el número de documento o código de referencia</p>
                        <span class="badge-publico">
                            
                        </span>
                    </div>

                    <!-- FORMULARIO DE BÚSQUEDA -->
                    <form action="{{ route('tenencia.resultados') }}" method="GET">
                        @csrf
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-8">
                                <div class="input-wrapper">
                                    <i class="fas fa-id-card input-icon"></i>
                                    <input 
                                        type="text" 
                                        class="input-busqueda" 
                                        name="busqueda" 
                                        placeholder="Número de documento o código"
                                        required
                                        autofocus
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn-buscar">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- BÚSQUEDAS RÁPIDAS (opcionales, con estilo más discreto) 
                    <div class="mt-4 pt-3" style="border-top: 1px solid var(--gris-borde);">
                        <small style="color: var(--gris-claro); font-weight: 400;">Búsquedas rápidas:</small>
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

                <!-- PANEL DE INFORMACIÓN (estilo serio, sin colores chillones) 
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
                                <div class="label">Actualización</div>
                                <div class="value">{{ $ultimaActualizacion ?? 'Hoy' }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-box">
                                <i class="fas fa-shield-alt"></i>
                                <div class="label">Estado</div>
                                <div class="value" style="color: var(--azul-profundo);">Operativo</div>
                            </div>
                        </div>
                    </div>
                </div>
                -->

            </div>
        </div>
    </div>

    <!-- Script para búsqueda rápida (opcional) -->
    <script>
        function buscarRapido(termino) {
            document.querySelector('input[name="busqueda"]').value = termino;
            document.querySelector('form').submit();
        }
    </script>

    <!-- Bootstrap JS (opcional para algunos componentes) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>