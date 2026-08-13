<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descarga de Carpeta Completa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        .app-wrapper { display: flex; min-height: 100vh; }
        
        .sidebar-custom {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-custom .logo-sidebar {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-custom .logo-sidebar img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        .sidebar-custom .logo-sidebar h3 {
            font-size: 1.2rem;
            color: #2fc900;
        }
        .sidebar-custom .nav-menu {
            list-style: none;
            padding: 0;
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
        .sidebar-custom .logout-btn {
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
        }
        .sidebar-custom .logout-btn a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }
        .sidebar-custom .logout-btn a:hover {
            background: rgba(255,255,255,0.1);
            color: #ff6b6b;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
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
        .content-header h1 {
            font-size: 1.5rem;
            color: #1a1a2e;
            margin: 0;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #000000;
            padding-bottom: 15px;
            border-bottom: 2px solid #eaeaea;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #2fc900;
        }
        .user-info p { margin: 5px 0; }
        .user-info strong { color: #00aa2b; min-width: 80px; display: inline-block; }
        
        .form-group { margin-bottom: 20px; }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }
        textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            min-height: 120px;
            resize: vertical;
        }
        textarea:focus {
            outline: none;
            border-color: #2fc900;
            box-shadow: 0 0 0 3px rgba(47, 201, 0, 0.1);
        }
        .text-muted {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
        
        button {
            padding: 16px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-buscar {
            background: #2fc900;
            color: white;
            width: 100%;
        }
        .btn-buscar:hover {
            background: #52cc00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47, 201, 0, 0.3);
        }
        .btn-descargar {
            background: #28a745;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
        }
        .btn-descargar:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-descargar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
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
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .mensaje.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .mensaje.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .mensaje.warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        
        #tablaResultados {
            overflow-x: auto;
            margin: 20px 0;
        }
        #tablaResultados table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        #tablaResultados th {
            background: #1a1a2e;
            color: white;
            padding: 10px;
            text-align: left;
        }
        #tablaResultados td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        #tablaResultados tr:hover { background: #f8f9fa; }
        
        .badge-examen {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            margin: 2px;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        .badge-ok { background: #28a745; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; }
        .badge-error { background: #dc3545; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; }
        .badge-warning { background: #ffc107; color: #333; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; }
        
        .footer-note {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .sidebar-custom { transform: translateX(-100%); }
            .sidebar-custom.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
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
        @media (min-width: 769px) { .menu-toggle { display: none; } }
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
                <a href="{{ route('solo_vista.index') }}">
                    <i></i> Buscar Documentos
                </a>
            </li>
            <li>
                <a href="{{ route('solo_vista.carpeta_completa') }}" class="active">
                    <i></i> 📁 Carpeta Completa
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
    
    <div class="menu-toggle" id="menuToggle">☰ Menú</div>
    
    <div class="main-content">
        <div class="content-header">
            <div style="flex:1;">
                <h1>📁 Descarga de Carpeta Completa</h1>
                <p class="subtitle">Selecciona múltiples cédulas y descarga todas sus carpetas en un solo ZIP</p>
            </div>
        </div>
        
        <div class="container-custom">
            <h2>Consulta Múltiple para Descarga</h2>
            
            @if(auth()->check())
            <div class="user-info">
                <p><strong>Usuario:</strong> {{ auth()->user()->name }}</p>
                <p><strong>Prefijos permitidos:</strong> {{ implode(', ', $prefijosPermitidos ?? []) }}</p>
            </div>
            @endif

            @if(session('mensaje'))
                <div class="mensaje {{ str_contains(session('mensaje'), 'Error') ? 'error' : (str_contains(session('mensaje'), 'No se pudo') ? 'warning' : 'success') }}">
                    {{ session('mensaje') }}
                </div>
            @endif

            <div class="form-group">
                <label for="cedulas_multiple">Cédulas (una por línea)</label>
                <textarea id="cedulas_multiple" rows="4" placeholder="Ej:&#10;12345678&#10;87654321&#10;11122233"></textarea>
                <small class="text-muted">Ingrese una cédula por línea. Solo se descargarán las que tengan archivos.</small>
            </div>

            <button class="btn-buscar" id="btnConsultar">
                🔍 Consultar Cédulas
            </button>

            <!-- Panel de resultados -->
            <div id="resultadosPanel" style="display: none; margin-top: 30px;">
                <h3 style="margin-bottom: 15px;">📋 Resultados de la consulta</h3>
                
                <div id="tablaResultados">
                    <table>
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Empresa</th>
                                <th>NIT</th>
                                <th>Fecha</th>
                                <th style="text-align:center;">Exámenes</th>
                                <th style="text-align:center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTabla"></tbody>
                    </table>
                </div>
                
                <div style="display: flex; justify-content: center; margin: 20px 0;">
                    <form id="formDescarga" method="POST" action="{{ route('solo_vista.descargar.carpeta.completa') }}">
                        @csrf
                        <input type="hidden" name="cedulas" id="cedulasSeleccionadas" value="">
                        
                        <button type="submit" 
                                id="btnDescargar"
                                class="btn-descargar"
                                disabled>
                            📁 CARPETA COMPLETA
                            <span id="contadorCarpetas" style="background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 20px; font-size: 0.8rem;"></span>
                        </button>
                    </form>
                </div>
                
                <div id="mensajeEstado" style="text-align: center; color: #6c757d; font-size: 0.9rem; margin-top: 10px;"></div>
            </div>

            <div class="footer-note">
                <p>Sistema de Gestión de Documentos - Descarga de Carpetas Completas</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    const btnConsultar = document.getElementById('btnConsultar');
    const resultadosPanel = document.getElementById('resultadosPanel');
    const cuerpoTabla = document.getElementById('cuerpoTabla');
    const btnDescargar = document.getElementById('btnDescargar');
    const cedulasSeleccionadas = document.getElementById('cedulasSeleccionadas');
    const contadorCarpetas = document.getElementById('contadorCarpetas');
    const mensajeEstado = document.getElementById('mensajeEstado');
    const textarea = document.getElementById('cedulas_multiple');

    btnConsultar.addEventListener('click', function() {
        const cedulas = textarea.value;
        
        if (!cedulas || cedulas.trim() === '') {
            alert('Por favor ingrese al menos una cédula');
            return;
        }

        btnConsultar.disabled = true;
        btnConsultar.innerHTML = '<span class="loading"></span> Consultando...';

        fetch('{{ route("solo_vista.consultar.carpeta") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cedulas_multiple: cedulas })
        })
        .then(response => response.json())
        .then(data => {
            btnConsultar.disabled = false;
            btnConsultar.innerHTML = '🔍 Consultar Cédulas';

            if (!data.success) {
                alert('Error: ' + data.message);
                return;
            }

            cuerpoTabla.innerHTML = '';
            let cedulasValidas = [];
            let totalEncontrados = 0;
            let totalConArchivos = 0;

            const resultados = data.resultados;
            for (const [cedula, info] of Object.entries(resultados)) {
                const fila = document.createElement('tr');
                
                if (!info.encontrado) {
                    fila.innerHTML = `
                        <td><strong>${cedula}</strong></td>
                        <td style="color:#dc3545;">No encontrado</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td style="text-align:center;">-</td>
                        <td style="text-align:center;"><span class="badge-error">❌ Sin datos</span></td>
                    `;
                    cuerpoTabla.appendChild(fila);
                    continue;
                }

                totalEncontrados++;
                const tieneArchivos = info.total_archivos > 0;
                if (tieneArchivos) {
                    totalConArchivos++;
                    cedulasValidas.push(cedula);
                }

                let examenesHtml = '';
                if (info.examenes && info.examenes.length > 0) {
                    examenesHtml = info.examenes.map(e => 
                        `<span class="badge-examen">${e.nombre}</span>`
                    ).join('');
                } else {
                    examenesHtml = '<span style="color:#6c757d;font-size:0.8rem;">Sin exámenes</span>';
                }

                fila.innerHTML = `
                    <td><strong>${cedula}</strong></td>
                    <td>${info.nombre}</td>
                    <td>${info.nombre_empresa}</td>
                    <td>${info.nit_empresa}</td>
                    <td>${info.fecha}</td>
                    <td style="text-align:center;max-width:200px;">
                        <div style="display:flex;flex-wrap:wrap;gap:3px;justify-content:center;">${examenesHtml}</div>
                    </td>
                    <td style="text-align:center;">
                        ${tieneArchivos 
                            ? `<span class="badge-ok">✅ ${info.total_archivos} archivos</span>`
                            : `<span class="badge-warning">⚠️ Sin archivos</span>`
                        }
                    </td>
                `;
                cuerpoTabla.appendChild(fila);
            }

            resultadosPanel.style.display = 'block';

            if (cedulasValidas.length > 0) {
                btnDescargar.disabled = false;
                contadorCarpetas.textContent = `${cedulasValidas.length} carpeta(s)`;
                mensajeEstado.innerHTML = `✅ ${totalConArchivos} de ${totalEncontrados} cédulas tienen archivos disponibles para descargar.`;
                cedulasSeleccionadas.value = cedulasValidas.join(',');
            } else {
                btnDescargar.disabled = true;
                contadorCarpetas.textContent = '0 carpetas';
                mensajeEstado.innerHTML = '⚠️ Ninguna cédula tiene archivos disponibles para descargar.';
                cedulasSeleccionadas.value = '';
            }
        })
        .catch(error => {
            btnConsultar.disabled = false;
            btnConsultar.innerHTML = '🔍 Consultar Cédulas';
            console.error('Error:', error);
            alert('Error al consultar: ' + error.message);
        });
    });
});
</script>
</body>
</html>