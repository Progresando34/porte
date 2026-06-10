<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Documentos - Solo Visualización</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            line-height: 1.6;
        }
        .app-wrapper { display: flex; min-height: 100vh; }
        .sidebar-custom {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-custom .logo-sidebar {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-custom .logo-sidebar img { max-width: 120px; margin-bottom: 15px; }
        .sidebar-custom .logo-sidebar h3 { color: #2fc900; }
        .nav-menu { list-style: none; padding: 0; }
        .nav-menu li a {
            display: flex;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-left: 3px solid transparent;
        }
        .nav-menu li a:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-menu li a.active { background: rgba(47, 201, 0, 0.2); border-left-color: #2fc900; }
        .main-content { flex: 1; margin-left: 280px; padding: 20px; }
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .content-header img { max-width: 60px; }
        .container-custom {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #2fc900;
        }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; display: block; margin-bottom: 8px; }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1rem;
        }
        button {
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .submit-btn {
            background: #2fc900;
            color: white;
            width: 100%;
        }
        .submit-btn:hover { background: #52cc00; }
        .view-btn {
            background: #28a745;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
        }
        .resultado-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e1e5eb;
        }
        .resultado-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .badge-cedula {
            background: #0066cc;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .mensaje.warning { background: #f8d7da; color: #721c24; }
        .mensaje.success { background: #d4edda; color: #155724; }
        @media (max-width: 768px) {
            .sidebar-custom { transform: translateX(-100%); position: fixed; z-index: 2000; }
            .sidebar-custom.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                background: #2fc900;
                color: white;
                padding: 10px;
                border-radius: 8px;
                cursor: pointer;
                z-index: 2001;
            }
        }
        @media (min-width: 769px) { .menu-toggle { display: none; } }
        .logout-btn { margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; }
        .logout-btn a { display: block; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none; }
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
            <li><a href="{{ route('solo_vista.index') }}" class="active">Buscar Documentos</a></li>
        </ul>
        <div class="logout-btn">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar Sesión</a>
            </form>
        </div>
    </div>
    
    <div class="menu-toggle" id="menuToggle">☰ Menú</div>
    
    <div class="main-content">
        <div class="content-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <div>
                <h1>Consulta de Documentos</h1>
                <p>Modo solo visualización</p>
            </div>
        </div>
        
        <div class="container-custom">
            <h2>Buscar Documentos</h2>
            
            @if(auth()->check())
            <div class="user-info">
                <p><strong>Usuario:</strong> {{ auth()->user()->name }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            </div>
            @endif

            @if(session('mensaje'))
                <div class="mensaje {{ str_contains(session('mensaje'), 'Por favor') ? 'warning' : 'success' }}">
                    {{ session('mensaje') }}
                </div>
            @endif

            <!-- FORMULARIO DE BÚSQUEDA -->
            <form method="POST" action="{{ route('solo_vista.buscar') }}" id="busquedaForm">
                @csrf
                <div class="form-group">
                    <label>Cédula (búsqueda individual)</label>
                    <input type="text" name="cedula" placeholder="Ej: 12345678" value="{{ old('cedula') }}">
                </div>
                <div class="form-group">
                    <label>Cédulas múltiples (una por línea)</label>
                    <textarea name="cedulas_multiple[]" rows="3" placeholder="12345678&#10;87654321">{{ old('cedulas_multiple') ? implode("\n", old('cedulas_multiple')) : '' }}</textarea>
                </div>
                <button type="submit" class="submit-btn">🔍 Buscar Documentos</button>
            </form>

            <!-- RESULTADOS -->
            @if(isset($resultados) && !empty($resultados))
                <div style="margin-top: 30px;">
                    <h3>Resultados encontrados:</h3>
                    @foreach($resultados as $cedula => $documentos)
                        <div class="resultado-card">
                            <div class="resultado-header">
                                <span class="badge-cedula">Cédula: {{ $cedula }}</span>
                                <span>{{ count($documentos) }} documento(s)</span>
                            </div>
                            <a href="{{ route('solo_vista.ver.documentos', $cedula) }}" target="_blank" class="view-btn">
                                👁️ Ver documentos
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if (menuToggle) {
    menuToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}
</script>
</body>
</html>