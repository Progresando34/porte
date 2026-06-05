<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados para NIT: {{ $nit }} - Sistema de Gestión</title>
    
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


.logo-container {
    margin-bottom: 20px;
    text-align: center;
}

.logo {
    max-width: 150px;
    height: auto;
    border-radius: 10px;
}

/* Si quieres un logo circular */
.logo-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
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

        .btn-back {
            background: #95a5a6;
            color: white;
        }

        .btn-back:hover {
            background: #7f8c8d;
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

        .nit-badge {
            display: inline-block;
            background: #2bb900;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
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
            min-width: 600px;
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

        td:nth-child(n+3) {
            text-align: center;
            font-weight: 500;
        }

        td:first-child, td:nth-child(2) {
            font-weight: 600;
        }

        .total-cell {
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
        }

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
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- SIDEBAR IZQUIERDO - Estadísticas y filtros -->
        <div class="sidebar">
         <div class="sidebar-header">
    <!-- Logo -->
    <div class="logo-container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
    </div>
    <h2> Resultado Búsqueda</h2>
    <p>Exámenes médicos ocupacionales</p>
</div>

            @if($conteo->isNotEmpty())
                @php
                    $empresa = $conteo->first();
                @endphp
                <div class="stats-container">
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">{{ $empresa->total_personas }}</div>
                        <div class="stat-label-sidebar">Total Personas</div>
                    </div>
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">{{ $empresa->ingreso ?: 0 }}</div>
                        <div class="stat-label-sidebar">Exámenes Ingreso</div>
                    </div>
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">{{ $empresa->egreso ?: 0 }}</div>
                        <div class="stat-label-sidebar">Exámenes Egreso</div>
                    </div>
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">{{ $empresa->periodico ?: 0 }}</div>
                        <div class="stat-label-sidebar">Exámenes Periódico</div>
                    </div>
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">{{ ($empresa->ingreso ?: 0) + ($empresa->egreso ?: 0) + ($empresa->periodico ?: 0) }}</div>
                        <div class="stat-label-sidebar">Total Exámenes</div>
                    </div>
                </div>
            @else
                <div class="stats-container">
                    <div class="stat-card-sidebar">
                        <div class="stat-number-sidebar">0</div>
                        <div class="stat-label-sidebar">Resultados</div>
                    </div>
                </div>
            @endif

            <div class="filters-sidebar">
                <div class="search-box-sidebar">
                    <input type="text" id="searchInput" placeholder="Buscar por NIT, empresa..." onkeyup="filterTable()">
                </div>
                <button class="btn-sidebar btn-export" onclick="exportToExcel()"> Exportar a Excel</button>
                <button class="btn-sidebar btn-print" onclick="window.print()"> Imprimir Reporte</button>
                <a href="{{ route('empresa.buscar') }}" style="text-decoration: none;">
                    <button class="btn-sidebar btn-back">← Nueva búsqueda</button>
                </a>
            </div>
        </div>

        <!-- MAIN CONTENT - Panel Derecho -->
        <div class="main-content">
            <div class="content-header">
                <h3> Resultados para NIT: <strong style="color: #2bb800;">{{ $nit }}</strong></h3>
                <p>Información detallada de exámenes por empresa</p>
                @if($conteo->isNotEmpty())
                    <div class="nit-badge">
                         {{ $conteo->first()->empresa }}
                    </div>
                @endif
            </div>

            <div class="toolbar">
                <div class="info-badge">
                    <span> Mostrando {{ $conteo->count() }} empresa(s)</span>
                </div>
            </div>

            <div class="table-container">
                <div class="table-wrapper">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>NIT</th>
                                <th>Empresa</th>
                                <th>Total</th>
                                <th>Ingreso</th>
                                <th>Egreso</th>
                                <th>Periódico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conteo as $item)
                            <tr>
                                <td><strong>{{ $item->nit ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $item->empresa ?? 'Sin especificar' }}</strong></td>
                                <td class="total-cell">{{ $item->total_personas }}</td>
                                <td>{{ $item->ingreso ?: 0 }}</td>
                                <td>{{ $item->egreso ?: 0 }}</td>
                                <td>{{ $item->periodico ?: 0 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 60px;">
                                    <div style="font-size: 48px; margin-bottom: 20px;"></div>
                                    <div style="font-size: 18px; color: #999;">No se encontraron resultados</div>
                                    <div style="font-size: 13px; color: #ccc; margin-top: 8px;">No hay registros para el NIT: {{ $nit }}</div>
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
                for(let i = 0; i < Math.min(row.cells.length, 2); i++) {
                    textContent += row.cells[i].textContent.toUpperCase() + ' ';
                }
                
                if(textContent.indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
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
            link.setAttribute('download', 'empresa_{{ $nit }}_' + new Date().toISOString().slice(0,19).replace(/:/g, '-') + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>