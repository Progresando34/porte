{{-- resources/views/certificados_e/solo_vista/index.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Documentos - Solo Visualización</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ========================================== */
        /* ESTILOS PARA EL PANEL Y BOTÓN CARPETA COMPLETA */
        /* ========================================== */

        .btn-descarga {
            background: linear-gradient(135deg, #0066cc, #004d99);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.4);
            width: 100%;
            max-width: 450px;
        }
        .btn-descarga:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 102, 204, 0.5);
        }
        .btn-descarga:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .panel-cedula {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e1e5eb;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .panel-cedula:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .panel-cedula-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 15px;
        }
        .panel-cedula-header .cedula-num {
            font-weight: bold;
            font-size: 1.1rem;
            color: #0066cc;
        }
        .panel-cedula-header .badge-archivos {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .panel-cedula-header .badge-sin-archivos {
            background: #ffc107;
            color: #333;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .panel-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .panel-info-item {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .panel-info-item label {
            font-size: 0.7rem;
            color: #6c757d;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .panel-info-item .value {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            word-break: break-word;
        }

        .panel-archivos-lista {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }
        .panel-archivos-lista .tag-archivo {
            background: #e9ecef;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            color: #495057;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .panel-archivos-lista .tag-prefijo {
            background: #0066cc;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: bold;
        }

        .no-resultados {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 12px;
            color: #6c757d;
        }

        .mensaje-exito {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
        }
        .mensaje-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
        }
        
        .sidebar-custom {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-custom .logo-sidebar {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-custom .logo-sidebar img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .sidebar-custom .logo-sidebar h3 {
            font-size: 1.2rem;
            color: #2fc900;
            margin-top: 10px;
        }
        
        .sidebar-custom .logo-sidebar p {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }
        
        .sidebar-custom .nav-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-custom .nav-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-custom .nav-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-custom .nav-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #2fc900;
        }
        
        .sidebar-custom .nav-menu li a.active {
            background: rgba(47, 201, 0, 0.2);
            border-left-color: #2fc900;
            color: white;
        }
        
        .sidebar-custom .nav-menu li a i {
            width: 24px;
            text-align: center;
            font-size: 1.2rem;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .content-header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .content-header .logo-container {
            flex-shrink: 0;
        }
        
        .content-header .logo-container img {
            max-width: 60px;
            height: auto;
        }
        
        .content-header .header-text {
            flex: 1;
        }
        
        .content-header h1 {
            font-size: 1.5rem;
            color: #1a1a2e;
            margin: 0 0 5px 0;
        }
        
        .content-header .subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0;
        }
        
        .container-custom {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 100%;
            margin: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        
        h2 {
            margin-bottom: 20px;
            font-size: clamp(1.3rem, 4vw, 1.8rem);
            text-align: center;
            color: #000000;
            padding-bottom: 15px;
            border-bottom: 2px solid #eaeaea;
        }
        
        .user-info {
            background: linear-gradient(to right, #ffffff, #ffffff);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #2feb00;
            position: relative;
            overflow: hidden;
        }
        
        .user-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #ebffdf, #d9ffc8);
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: clamp(0.9rem, 3vw, 1rem);
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .user-info strong {
            color: #00aa2b;
            min-width: 80px;
            display: inline-block;
        }
        
        .user-info span {
            color: #333;
            word-break: break-word;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: clamp(0.95rem, 3vw, 1rem);
        }
        
        input, textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: clamp(0.95rem, 3vw, 1rem);
            transition: all 0.3s ease;
            background: #ffffff;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #2fc900;
            background: white;
            box-shadow: 0 0 0 3px rgba(47, 201, 0, 0.1);
        }
        
        input::placeholder, textarea::placeholder {
            color: #888;
            opacity: 0.8;
        }
        
        .text-muted {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
        
        .or-divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .or-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
            z-index: 1;
        }
        
        .or-divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            z-index: 2;
            color: #6c757d;
            font-weight: 500;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 25px;
        }
        
        button {
            padding: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: clamp(0.95rem, 3vw, 1rem);
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .submit-btn {
            background: #2fc900;
            color: white;
        }
        
        .submit-btn:hover {
            background: #52cc00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47, 201, 0, 0.3);
        }
        
        .submit-btn::before {
            content: '🔍';
        }
        
        .view-btn {
            background: #28a745;
            color: white;
        }
        
        .view-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .resultados-container {
            margin-top: 30px;
        }
        
        .resultado-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e1e5eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .resultado-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .resultado-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .badge-cedula {
            background: #0066cc;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .badge-count {
            background: #6c757d;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.9rem;
        }
        
        .documentos-lista {
            list-style: none;
            margin-top: 15px;
        }
        
        .documentos-lista li {
            background: white;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 3px solid #d9ffca;
        }
        
        .documentos-lista strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        
        .documentos-lista small {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
        }
        
        .info-item label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-item .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            word-break: break-word;
        }
        
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error, .mensaje.warning {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .logout-btn {
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
        }
        
        .logout-btn a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn a:hover {
            background: rgba(255,255,255,0.1);
            color: #ff6b6b;
        }
        
        @media (max-width: 768px) {
            .sidebar-custom {
                transform: translateX(-100%);
                position: fixed;
                z-index: 2000;
            }
            
            .sidebar-custom.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 2001;
                background: #2fc900;
                color: white;
                padding: 10px 15px;
                border-radius: 8px;
                cursor: pointer;
            }
        }
        
        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <div class="sidebar-custom" id="sidebar">
        <div class="logo-sidebar">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <h3>Solo Visualización</h3>
            <p>Modo solo lectura</p>
        </div>
        
        <ul class="nav-menu">
            <li>
                <a href="{{ route('solo_vista.index') }}" class="active">
                    <i></i> Buscar Documentos
                </a>
            </li>
            <li>
                <a href="#">
                    <i></i> Mis Consultas
                </a>
            </li>
            <li>
                <a href="#">
                    <i></i> Ayuda
                </a>
            </li>
        </ul>
        
        <div class="logout-btn">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                    <i></i> Cerrar Sesión
                </a>
            </form>
        </div>
    </div>
    
    <div class="menu-toggle" id="menuToggle">
        ☰ Menú
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
            </div>
            <div class="header-text">
                <h1>Resultados de Certificados</h1>
                <p class="subtitle">Consulta y gestión de documentos empresariales - Modo solo visualización</p>
            </div>
        </div>
        
        <div class="container-custom">
            <h2>Consulta de Documentos - Solo Visualización</h2>
            
            @if(auth()->check())
            <div class="user-info">
                <p><strong>Usuario:</strong> <span>{{ auth()->user()->name }}</span></p>
                <p><strong>Email:</strong> <span>{{ auth()->user()->email }}</span></p>
            </div>
            @endif

            @if(session('mensaje'))
                <div class="mensaje {{ str_contains(session('mensaje'), 'No') ? 'warning' : 'success' }}">
                    {{ session('mensaje') }}
                </div>
            @endif

            <form method="POST" action="{{ route('solo_vista.buscar') }}" id="busquedaForm">
                @csrf
                
                <div class="form-group">
                    <label for="cedula">Cédula (búsqueda individual)</label>
                    <input type="text" 
                           id="cedula" 
                           name="cedula" 
                           placeholder="Ej: 12345678"
                           value="{{ old('cedula') }}">
                    <small class="text-muted">Ingrese una sola cédula</small>
                </div>

                <div class="or-divider">
                    <span>O</span>
                </div>

                <div class="form-group">
                    <label for="cedulas_multiple">Cédulas múltiples</label>
                    <textarea 
                        id="cedulas_multiple" 
                        name="cedulas_multiple" 
                        rows="3" 
                        placeholder="Ej:&#10;12345678&#10;87654321&#10;11122233">{{ old('cedulas_multiple') }}</textarea>
                    <small class="text-muted">Una cédula por línea</small>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        Buscar Documentos
                    </button>
                </div>
            </form>

            @if(isset($resultados) && !empty($resultados))
                <div class="resultados-container">
                    @foreach($resultados as $cedula => $documentos)
                        @php $primerDoc = $documentos->first(); @endphp
                        <div class="resultado-card">
                            <div class="resultado-header">
                                <span class="badge-cedula">Cédula: {{ $cedula }}</span>
                                <span class="badge-count">{{ count($documentos) }} documento(s)</span>
                            </div>
                            
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Nombres y Apellidos</label>
                                    <div class="value">{{ $primerDoc->nombre ?? 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Fecha</label>
                                    <div class="value">{{ $primerDoc->fecha ? date('d/m/Y', strtotime($primerDoc->fecha)) : 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Misión</label>
                                    <div class="value">{{ $primerDoc->mision ?? 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <label>NIT Empresa</label>
                                    <div class="value">{{ $primerDoc->nit_empresa ?? 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Nombre Empresa</label>
                                    <div class="value">{{ $primerDoc->nombre_empresa ?? 'N/A' }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Misión Empresa</label>
                                    <div class="value">{{ $primerDoc->mision_empresa ?? 'N/A' }}</div>
                                </div>
                            </div>
                            
@if(count($documentos) > 1)
    <div class="alert-info">
        Se encontraron {{ count($documentos) }} documentos. Se mostrarán fusionados en una sola vista.
    </div>
    
    <form method="POST" action="{{ url('/solo-vista/ver-fusionados/' . $cedula) }}" target="_blank">
        @csrf
        <button type="submit" class="view-btn" style="width: 100%;">
            Ver {{ count($documentos) }} documentos fusionados
        </button>
    </form>
@else
    <a href="{{ url('/solo-vista/ver-documentos/' . $cedula) }}" target="_blank" class="view-btn" style="width: 100%; text-decoration: none; display: inline-block; text-align: center;">
        Ver {{ count($documentos) }} documento(s)
    </a>
@endif
                            
                            <ul class="documentos-lista">
                                @foreach($documentos as $doc)
                                    <li>
                                        <strong>{{ $doc->mision ?? 'Documento sin misión' }}</strong>
                                        <small>Fecha: {{ $doc->fecha ? date('d/m/Y', strtotime($doc->fecha)) : 'N/A' }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <!-- ========================================== -->
                <!-- 🔥 PANEL DE RESULTADOS DETALLADOS -->
                <!-- 🔥 Y BOTÓN CARPETA COMPLETA -->
                <!-- ========================================== -->
                <div class="resultados-container" style="margin-top: 40px; border-top: 3px solid #e9ecef; padding-top: 30px;">
                    <h3 style="color: #0066cc; margin-bottom: 20px; text-align: center;">
                        📋 Detalle de Documentos por Cédula
                    </h3>
                    
                    <div id="panelResultados">
                        <div class="no-resultados">
                            <div class="loading" style="display: inline-block; width: 30px; height: 30px; border: 3px solid rgba(0,102,204,0.1); border-radius: 50%; border-top-color: #0066cc; animation: spin 0.8s linear infinite;"></div>
                            <p style="margin-top: 10px;">Cargando información detallada...</p>
                        </div>
                    </div>
                    
                    <div class="text-center" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                        <button id="btnCarpetaCompleta" class="btn-descarga" onclick="descargarCarpetaCompleta()" style="display: none;">
                            📦 DESCARGAR CARPETA COMPLETA
                        </button>
                        <div id="loadingCarpeta" style="display: none; text-align: center; padding: 20px;">
                            <div class="loading" style="display: inline-block; width: 30px; height: 30px; border: 3px solid rgba(0,102,204,0.1); border-radius: 50%; border-top-color: #0066cc; animation: spin 0.8s linear infinite;"></div>
                            <p style="margin-top: 10px; color: #6c757d;">Generando carpeta completa... Por favor espera.</p>
                        </div>
                        <div id="mensajeCarpeta" style="display: none; margin-top: 10px; padding: 15px; border-radius: 8px;"></div>
                    </div>
                </div>

            @elseif(isset($resultados))
                <div class="mensaje warning">
                    No se encontraron documentos para las cédulas ingresadas.
                </div>
            @endif

            <div class="footer-note">
                <p>Sistema de Gestión de Documentos</p>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// MENÚ TOGGLE (EXISTENTE)
// ==========================================
var menuToggle = document.getElementById('menuToggle');
var sidebar = document.getElementById('sidebar');

if (menuToggle) {
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
}

document.addEventListener('click', function(event) {
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// ==========================================
// 🔥 FUNCIONES PARA EL PANEL Y CARPETA COMPLETA
// ==========================================

var cedulasIniciales = [];

@if(isset($resultados) && !empty($resultados))
    cedulasIniciales = JSON.parse('{!! json_encode(array_keys($resultados)) !!}');
@endif

document.addEventListener('DOMContentLoaded', function() {
    if (cedulasIniciales.length > 0) {
        cargarInfoCedulas(cedulasIniciales);
    }
});

function cargarInfoCedulas(cedulas) {
    var panelContainer = document.getElementById('panelResultados');
    var btnCarpeta = document.getElementById('btnCarpetaCompleta');
    
    if (!panelContainer) return;
    
    fetch('{{ url("/solo-vista/obtener-info-cedulas") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ cedulas: cedulas })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (!data.success) {
            panelContainer.innerHTML = '<div class="no-resultados" style="color: #dc3545;">❌ Error al cargar información</div>';
            return;
        }
        
        var html = '';
        var totalCedulasConArchivos = 0;
        var cedulasList = [];
        var resultados = data.resultados || {};
        
        for (var cedula in resultados) {
            if (!resultados.hasOwnProperty(cedula)) continue;
            
            var info = resultados[cedula];
            
            if (!info.encontrado) {
                html += '<div class="panel-cedula" style="border-left: 4px solid #dc3545;">' +
                    '<div class="panel-cedula-header">' +
                        '<span class="cedula-num">❌ ' + cedula + '</span>' +
                        '<span style="color: #dc3545; font-size: 0.85rem;">No encontrado</span>' +
                    '</div>' +
                    '<p style="color: #dc3545; font-size: 0.9rem; margin: 0;">' + (info.mensaje || 'Cédula no encontrada') + '</p>' +
                '</div>';
                continue;
            }
            
            if (info.total_archivos > 0) {
                totalCedulasConArchivos++;
                cedulasList.push(cedula);
            }
            
            var archivosHtml = '';
            if (info.archivos && info.archivos.length > 0) {
                for (var i = 0; i < info.archivos.length; i++) {
                    var a = info.archivos[i];
                    archivosHtml += '<span class="tag-archivo"><span class="tag-prefijo">' + a.prefijo + '</span> ' + a.nombre + '</span>';
                }
            } else {
                archivosHtml = '<span style="color: #6c757d; font-size: 0.85rem;">Sin archivos disponibles</span>';
            }
            
            var badgeClass = info.total_archivos > 0 ? 'badge-archivos' : 'badge-sin-archivos';
            var badgeText = info.total_archivos > 0 ? info.total_archivos + ' archivo(s)' : 'Sin archivos';
            
            html += '<div class="panel-cedula" style="border-left: 4px solid ' + (info.total_archivos > 0 ? '#28a745' : '#ffc107') + ';">' +
                '<div class="panel-cedula-header">' +
                    '<span class="cedula-num">📁 ' + info.cedula + '</span>' +
                    '<span class="' + badgeClass + '">' + badgeText + '</span>' +
                '</div>' +
                '<div class="panel-info-grid">' +
                    '<div class="panel-info-item"><label>Nombre</label><div class="value">' + (info.nombre || 'N/A') + '</div></div>' +
                    '<div class="panel-info-item"><label>NIT Empresa</label><div class="value">' + (info.nit_empresa || 'N/A') + '</div></div>' +
                    '<div class="panel-info-item"><label>Empresa</label><div class="value">' + (info.nombre_empresa || 'N/A') + '</div></div>' +
                    '<div class="panel-info-item"><label>Fecha Cita</label><div class="value">' + (info.fecha_cita || 'N/A') + '</div></div>' +
                    '<div class="panel-info-item"><label>Misión</label><div class="value">' + (info.mision || 'N/A') + '</div></div>' +
                '</div>' +
                '<div class="panel-archivos-lista">' + archivosHtml + '</div>' +
            '</div>';
        }
        
        panelContainer.innerHTML = html;
        
        if (totalCedulasConArchivos > 0) {
            btnCarpeta.style.display = 'inline-block';
            btnCarpeta.disabled = false;
            btnCarpeta.dataset.cedulas = JSON.stringify(cedulasList);
        } else {
            btnCarpeta.style.display = 'none';
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        panelContainer.innerHTML = '<div class="