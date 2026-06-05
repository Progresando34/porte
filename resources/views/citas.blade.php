<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conteo por Empresa - Sistema de Gestión</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        /* Estilos para documentos */
        .documentos-badge {
            position: relative;
            display: inline-block;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .badge.has-docs {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }

        .badge.has-docs:hover {
            background: #c8e6c9;
            transform: scale(1.05);
        }

        .badge.no-docs {
            background: #f5f5f5;
            color: #757575;
            border: 1px solid #e0e0e0;
        }

        /* Tooltip para mostrar detalles de documentos */
        .documentos-tooltip {
            visibility: hidden;
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            color: #333;
            padding: 12px;
            border-radius: 8px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
            min-width: 180px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .documentos-badge:hover .documentos-tooltip {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .documentos-tooltip ul {
            margin: 5px 0;
            padding-left: 15px;
        }

        .documentos-tooltip li {
            margin: 3px 0;
            font-size: 10px;
            text-align: left;
        }

        .ver-documentos-link {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 8px;
            background: #2196f3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 10px;
            transition: background 0.3s ease;
        }

        .ver-documentos-link:hover {
            background: #1976d2;
            color: white;
        }

        /* Alinear la nueva columna */
        td:last-child {
            text-align: center;
        }

        /* Layout Principal - Flexbox para dividir pantalla */
        .dashboard {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* SIDEBAR - Panel Izquierdo */
        .sidebar {
            width: 320px;
            background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
            color: white;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
        }

        /* Stats Cards en Sidebar */
        .stats-container {
            padding: 20px;
            flex: 1;
        }

        .stat-card-sidebar {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .stat-card-sidebar:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }

        .stat-number-sidebar {
            font-size: 32px;
            font-weight: bold;
            color: #91ff00;
            margin-bottom: 8px;
        }

        .stat-label-sidebar {
            font-size: 13px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Filtros en Sidebar */
        .filters-sidebar {
            padding: 0 20px 20px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 20px;
        }

        .search-box-sidebar {
            margin-bottom: 15px;
        }

        .search-box-sidebar input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            background: rgba(255,255,255,0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .search-box-sidebar input::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .search-box-sidebar input:focus {
            outline: none;
            background: rgba(255,255,255,0.2);
            box-shadow: 0 0 0 2px #91ff00;
        }

        .btn-sidebar {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-export {
            background: #27ae60;
            color: white;
        }

        .btn-export:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-print {
            background: #3498db;
            color: white;
        }

        .btn-print:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* MAIN CONTENT - Panel Derecho */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #f8f9fa;
        }

        /* Header del contenido principal */
        .content-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .content-header h3 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .content-header p {
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Toolbar superior */
        .toolbar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-chip {
            padding: 6px 15px;
            background: #f0f2f5;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #2c3e50;
        }

        .filter-chip:hover, .filter-chip.active {
            background: #91ff00;
            color: #1a2a3a;
            transform: translateY(-2px);
        }

        .info-badge {
            margin-left: auto;
            font-size: 12px;
            color: #7f8c8d;
        }

        /* Tabla con scroll */
        .table-container {
            flex: 1;
            overflow: auto;
            padding: 20px 30px;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: auto;
            height: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 800px;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 14px 12px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: #f8f9ff;
        }

        /* Estilos para celdas numéricas */
        td:nth-child(n+3) {
            text-align: center;
            font-weight: 500;
        }

        td:first-child, td:nth-child(2) {
            font-weight: 600;
        }

        .empresa-name {
            color: #2c3e50;
        }

        .total-cell {
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 280px;
            }
            
            .table-container {
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                max-height: 40%;
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .stats-container {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .stat-card-sidebar {
                flex: 1;
                min-width: 120px;
            }
            
            .main-content {
                height: 60%;
            }
        }

        /* Animaciones */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .table-wrapper {
            animation: slideIn 0.3s ease-out;
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            cursor: help;
        }
        
        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- SIDEBAR IZQUIERDO - Estadísticas y filtros -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Conteo por Empresa</h2>
                <p>Exámenes médicos ocupacionales</p>
            </div>

            <div class="stats-container">
                <div class="stat-card-sidebar">
                    <div class="stat-number-sidebar" id="totalEmpresas">0</div>
                    <div class="stat-label-sidebar">Empresas Registradas</div>
                </div>
                <div class="stat-card-sidebar">
                    <div class="stat-number-sidebar" id="totalPersonas">0</div>
                    <div class="stat-label-sidebar">Total Personas</div>
                </div>
                <div class="stat-card-sidebar">
                    <div class="stat-number-sidebar" id="totalExamenes">0</div>
                    <div class="stat-label-sidebar">Total Exámenes</div>
                </div>
                <div class="stat-card-sidebar">
                    <div class="stat-number-sidebar" id="fechaActual"></div>
                    <div class="stat-label-sidebar">Fecha Reporte</div>
                </div>
            </div>

            <div class="filters-sidebar">
                <div class="search-box-sidebar">
                    <input type="text" id="searchInput" placeholder=" Buscar por NIT, empresa o examen..." onkeyup="filterTable()">
                </div>
                <button class="btn-sidebar btn-export" onclick="exportToExcel()"> Exportar a Excel</button>
                <button class="btn-sidebar btn-print" onclick="window.print()"> Imprimir Reporte</button>
            </div>
        </div>

        <!-- MAIN CONTENT - Panel Derecho (Tabla) -->
        <div class="main-content">
            <div class="content-header">
                <h3>📋 Detalle de Exámenes por Empresa</h3>
                <p>Listado completo de empresas y sus respectivos conteos de exámenes</p>
            </div>

            <div class="toolbar">
                <div class="filter-buttons">
                    <span class="filter-chip active" onclick="filterByType('all')">Todos</span>
                    <span class="filter-chip" onclick="filterByType('ingreso')"> Ingreso</span>
                    <span class="filter-chip" onclick="filterByType('egreso')"> Egreso</span>
                    <span class="filter-chip" onclick="filterByType('periodico')"> Periódico</span>
                </div>
                <div class="info-badge">
                    <span>Mostrando {{ $conteo->count() }} empresas</span>
                </div>
            </div>

            <div class="table-container">
                <div class="table-wrapper">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>NIT</th>
                                <th>Empresa</th>
                                <th class="tooltip" data-tooltip="Total de personas por empresa">Total</th>
                                <th>Ingreso</th>
                                <th>Egreso</th>
                                <th>Periódico</th>
                                <th>Examen Médico</th>
                                <th>Electrocardiograma</th>
                                <th>Audiometria</th>
                                <th>RX Columna</th>
                                <th>Espirometris</th>
                                <th>Psicología</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conteo as $item)
                            <tr data-ingreso="{{ $item->ingreso }}" data-egreso="{{ $item->egreso }}" data-periodico="{{ $item->periodico }}">
                                <td><strong>{{ $item->nit ?? 'N/A' }}</strong></td>
                                <td class="empresa-name"><strong>{{ $item->empresa ?? 'Sin especificar' }}</strong></td>
                                <td class="total-cell">{{ $item->total_personas }}</td>
                                <td>{{ $item->ingreso ?: 0 }}</td>
                                <td>{{ $item->egreso ?: 0 }}</td>
                                <td>{{ $item->periodico ?: 0 }}</td>
                                <td>{{ $item->medico ?: 0 }}</td>
                                <td>{{ $item->electrocar ?: 0 }}</td>
                                <td>{{ $item->audiometri ?: 0 }}</td>
                                <td>{{ $item->rxcolumna ?: 0 }}</td>
                                <td>{{ $item->espirometr ?: 0 }}</td>
                                <td>{{ $item->sicologia ?: 0 }}</td>
                                <td>
                                    @if($item->total_documentos > 0)


<a href="{{ route('documentos.empresa.cliente', $item->nit) }}" target="_blank" style="text-decoration: none;">
    <span class="badge has-docs">
        📄 {{ $item->total_documentos }} documento(s)
    </span>
</a>


                                    @else
                                        <span class="badge no-docs">📭 Sin documentos</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" style="text-align: center; padding: 60px;">
                                    <div style="font-size: 18px; color: #999;">No hay datos disponibles</div>
                                    <div style="font-size: 13px; color: #ccc; margin-top: 8px;">Intente más tarde o contacte al administrador</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentFilter = 'all';
        let originalRows = [];

        // Guardar filas originales al cargar
        function initRows() {
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            if(tbody) {
                originalRows = Array.from(tbody.getElementsByTagName('tr'));
            }
        }

        // Actualizar estadísticas
        function updateStats() {
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            if(!tbody) return;
            
            const rows = tbody.getElementsByTagName('tr');
            let totalEmpresas = 0;
            let totalPersonas = 0;
            let totalExamenes = 0;
            
            for(let row of rows) {
                if(row.cells.length > 1 && row.cells[0].innerText !== 'No hay datos disponibles' && row.style.display !== 'none') {
                    totalEmpresas++;
                    const personas = parseInt(row.cells[2].innerText) || 0;
                    totalPersonas += personas;
                    
                    for(let i = 3; i < row.cells.length - 1; i++) {
                        totalExamenes += parseInt(row.cells[i].innerText) || 0;
                    }
                }
            }
            
            document.getElementById('totalEmpresas').innerText = totalEmpresas;
            document.getElementById('totalPersonas').innerText = totalPersonas;
            document.getElementById('totalExamenes').innerText = totalExamenes;
        }
        
        // Filtrar por tipo de examen
        function filterByType(type) {
            currentFilter = type;
            
            document.querySelectorAll('.filter-chip').forEach(chip => {
                chip.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            if(!tbody) return;
            
            const rows = tbody.getElementsByTagName('tr');
            
            for(let row of rows) {
                if(row.cells.length <= 1) continue;
                
                if(type === 'all') {
                    row.style.display = '';
                } else {
                    const ingreso = parseInt(row.getAttribute('data-ingreso') || 0);
                    const egreso = parseInt(row.getAttribute('data-egreso') || 0);
                    const periodico = parseInt(row.getAttribute('data-periodico') || 0);
                    
                    let hasType = false;
                    if(type === 'ingreso') hasType = ingreso > 0;
                    if(type === 'egreso') hasType = egreso > 0;
                    if(type === 'periodico') hasType = periodico > 0;
                    
                    row.style.display = hasType ? '' : 'none';
                }
            }
            
            updateStats();
        }
        
        // Función para filtrar tabla por texto
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            if(!tbody) return;
            
            const rows = tbody.getElementsByTagName('tr');
            
            for(let row of rows) {
                if(row.cells.length <= 1) continue;
                
                let textContent = '';
                for(let i = 0; i < 2; i++) {
                    textContent += row.cells[i].textContent.toUpperCase() + ' ';
                }
                
                if(textContent.indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
            
            updateStats();
        }
        
        // Exportar a Excel
        function exportToExcel() {
            const table = document.getElementById('dataTable');
            const rows = table.querySelectorAll('tr');
            const csv = [];
            
            for(let row of rows) {
                const rowData = [];
                for(let cell of row.querySelectorAll('th, td')) {
                    rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
                }
                csv.push(rowData.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob(["\uFEFF" + csvContent], {type: 'text/csv;charset=utf-8;'});
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', 'reporte_empresas_' + new Date().toISOString().slice(0,19).replace(/:/g, '-') + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Mostrar fecha actual
        const hoy = new Date();
        const fechaFormateada = hoy.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('fechaActual').innerText = fechaFormateada;
        
        // Inicializar
        setTimeout(() => {
            initRows();
            updateStats();
        }, 100);
    </script>
</body>
</html>