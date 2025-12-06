<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Certificados</title>
    <style>
   * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, sans-serif;
    background: #f9fbfd;
    min-height: 100vh;
    padding: 30px;
    color: #2c3e50;
    line-height: 1.6;
}

/* Avatar del usuario */
.user-avatar {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(52, 152, 219, 0.1);
    transition: all 0.3s ease;
    z-index: 100;
}

.user-avatar:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    border-color: rgba(52, 152, 219, 0.2);
}

.avatar-img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #3498db;
    padding: 2px;
}

.avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3498db, #2ecc71);
    color: white;
    font-weight: 600;
    font-size: 18px;
    border: 2px solid white;
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    color: #2c3e50;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.user-role {
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 2px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 
        0 4px 6px -1px rgba(0, 0, 0, 0.02),
        0 10px 15px -3px rgba(0, 0, 0, 0.04),
        0 20px 25px -5px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    padding: 48px;
    border: 1px solid #f0f4f8;
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 48px;
    padding-bottom: 32px;
    border-bottom: 1px solid #e8edf5;
    position: relative;
}

.header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    border-radius: 2px;
}

.logo-container {
    margin-bottom: 28px;
    position: relative;
}

.logo {
    max-height: 72px;
    height: auto;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
}

h1 {
    color: #2c3e50;
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    color: #7f8c8d;
    font-size: 16px;
    font-weight: 400;
    letter-spacing: 0.3px;
}

/* Mensajes */
.message-alert {
    background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
    border: 1px solid #fee2e2;
    border-radius: 12px;
    padding: 20px 28px;
    margin-bottom: 32px;
    color: #dc2626;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 16px;
    backdrop-filter: blur(10px);
    border-left: 4px solid #dc2626;
}

.message-alert::before {
    content: "⚠️";
    font-size: 20px;
    opacity: 0.8;
}

/* Cédula Sections */
.cedula-section {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 32px;
    border: 1px solid #e8edf5;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.cedula-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cedula-section:hover {
    border-color: #dbeafe;
    box-shadow: 
        0 10px 25px -5px rgba(52, 152, 219, 0.08),
        0 20px 40px -10px rgba(52, 152, 219, 0.12);
    transform: translateY(-2px);
}

.cedula-section:hover::before {
    opacity: 1;
}

.cedula-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.cedula-title {
    font-size: 20px;
    color: #2c3e50;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cedula-title::before {
    content: '📋';
    font-size: 20px;
    opacity: 0.7;
}

.cedula-number {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 500;
    font-size: 16px;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.certificate-count {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 3px 10px rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Botones */
.download-all-btn {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 6px 20px rgba(46, 204, 113, 0.25);
    margin-bottom: 28px;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
}

.download-all-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.download-all-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(46, 204, 113, 0.35);
}

.download-all-btn:hover::before {
    left: 100%;
}

/* Tabla */
.certificates-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 4px;
    border-radius: 16px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e8edf5;
}

.certificates-table thead {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    position: relative;
}

.certificates-table thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
}

.certificates-table th {
    padding: 20px 24px;
    text-align: left;
    font-weight: 500;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    border-bottom: none;
}

.certificates-table tbody tr {
    background: #ffffff;
    transition: all 0.3s ease;
    position: relative;
}

.certificates-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.certificates-table tbody tr:hover {
    background: #f0f7ff;
    transform: translateX(4px);
}

.certificates-table tbody tr::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 24px;
    right: 24px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e8edf5, transparent);
}

.certificates-table tbody tr:last-child::after {
    display: none;
}

.certificates-table td {
    padding: 20px 24px;
    color: #2c3e50;
    font-size: 15px;
    font-weight: 400;
    border-bottom: none;
}

.description-cell {
    color: #2c3e50;
    font-weight: 500;
    position: relative;
    padding-left: 28px;
}

.description-cell::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #3498db;
    font-size: 24px;
    line-height: 1;
    top: 50%;
    transform: translateY(-50%);
}

.date-cell {
    color: #64748b;
    font-size: 14px;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
    letter-spacing: 0.5px;
}

.actions-cell {
    text-align: center;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 14px;
    margin: 0 4px;
    border: 1px solid transparent;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.action-btn:hover::before {
    width: 300px;
    height: 300px;
}

.view-btn {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

.view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
    border-color: rgba(255, 255, 255, 0.3);
}

.download-btn {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
    border-color: rgba(255, 255, 255, 0.3);
}

/* Sin resultados */
.no-results {
    text-align: center;
    padding: 64px 48px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    border: 2px dashed #cbd5e1;
    margin: 32px 0;
}

.no-results-icon {
    font-size: 64px;
    margin-bottom: 24px;
    opacity: 0.3;
    filter: grayscale(1);
}

.no-results h3 {
    color: #64748b;
    font-size: 22px;
    margin-bottom: 12px;
    font-weight: 500;
}

.no-results p {
    color: #94a3b8;
    font-size: 15px;
    max-width: 400px;
    margin: 0 auto;
}

/* Botón Volver */
.back-container {
    text-align: center;
    margin-top: 48px;
    padding-top: 32px;
    border-top: 1px solid #e8edf5;
    position: relative;
}

.back-btn {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    border: none;
    padding: 16px 40px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
    position: relative;
    overflow: hidden;
}

.back-btn:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(100, 116, 139, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    body {
        padding: 16px;
    }
    
    .container {
        padding: 24px;
        border-radius: 20px;
    }
    
    h1 {
        font-size: 26px;
    }
    
    .cedula-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .certificates-table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .certificates-table th,
    .certificates-table td {
        padding: 16px 20px;
        font-size: 14px;
    }
    
    .action-btn {
        padding: 8px 16px;
        font-size: 13px;
        margin: 2px;
    }
    
    .download-all-btn,
    .back-btn {
        width: 100%;
        justify-content: center;
        padding: 16px;
    }
    
    .description-cell {
        padding-left: 20px;
    }
    
    .description-cell::before {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 20px;
    }
    
    .cedula-section {
        padding: 24px 20px;
    }
    
    .certificates-table th,
    .certificates-table td {
        padding: 14px 16px;
    }
    
    .action-btn {
        display: block;
        margin: 8px 0;
        width: 100%;
        justify-content: center;
    }
}

/* Animaciones suaves */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cedula-section {
    animation: fadeIn 0.6s ease-out;
}

.cedula-section:nth-child(2) {
    animation-delay: 0.1s;
}

.cedula-section:nth-child(3) {
    animation-delay: 0.2s;
}

/* Scroll personalizado */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3498db, #2ecc71);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2980b9, #27ae60);
}

/* Estados de carga */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Tooltips */
[data-tooltip] {
    position: relative;
}

[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 12px;
    background: #2c3e50;
    color: white;
    font-size: 12px;
    border-radius: 6px;
    white-space: nowrap;
    margin-bottom: 8px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

[data-tooltip]:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #2c3e50;
    margin-bottom: 2px;
    z-index: 1000;
}

/* Estados focus para accesibilidad */
button:focus,
a:focus,
input:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Mejoras tipográficas */
.description-cell {
    line-height: 1.5;
}

.date-cell {
    font-feature-settings: "tnum";
}

/* Efectos de profundidad */
.certificates-table {
    position: relative;
}

.certificates-table::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 16px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    pointer-events: none;
}
    </style>
</head>

<div class="header">
    <div class="logo-container">
        <!-- Avatar del usuario si está autenticado -->
        @auth
        <div style="position: absolute; top: 20px; right: 20px; display: flex; align-items: center; gap: 10px;">
            @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" 
                     alt="{{ auth()->user()->name }}" 
                     class="rounded-circle" 
                     width="50" height="50"
                     style="border: 2px solid #3498db;">
            @else
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px; background: linear-gradient(135deg, #3498db, #2ecc71);">
                    <span class="text-white" style="font-size: 18px;">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </span>
                </div>
            @endif
            <div style="color: #2c3e50; font-weight: 500;">
                {{ auth()->user()->name }}
                <div style="font-size: 12px; color: #7f8c8d;">
                    {{ auth()->user()->profile->name ?? 'Usuario' }}
                </div>
            </div>
        </div>
        @endauth
        
        <!-- Logo principal -->
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
    </div>
    <h1>Resultados de Certificados</h1>
    <p class="subtitle">Consulta y gestión de documentos empresariales</p>
</div>

<body>

<div class="container">
    <!-- Header con logo y título -->
    <div class="header">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
        </div>
        <h1>Resultados de Certificados</h1>
        <p class="subtitle">Consulta y gestión de documentos empresariales</p>
    </div>

    <!-- Mensajes del sistema -->
    @if(session('mensaje'))
        <div class="message-alert">
            {{ session('mensaje') }}
        </div>
    @endif

    <!-- Resultados -->
    @if(empty($resultados))
        <div class="no-results">
            <div class="no-results-icon"></div>
            <h3>No se encontraron certificados</h3>
            <p>No se encontraron documentos para la(s) cédula(s) ingresada(s).</p>
        </div>
    @else
        @foreach ($resultados as $cedula => $archivos)
            <div class="cedula-section">
                <div class="cedula-header">
                    <div>
                        <span class="cedula-title">Documentos encontrados</span>
                        <span class="certificate-count">{{ count($archivos) }} documento(s)</span>
                    </div>
                    <div class="cedula-number">Cédula: {{ $cedula }}</div>
                </div>

                <!-- Botón para descargar todos -->
                <form method="GET" action="{{ route('certificados_e.descargarMultiples') }}" style="margin-bottom: 25px;">
                    @csrf
                    <input type="hidden" name="cedulas[]" value="{{ $cedula }}">
                    <button type="submit" class="download-all-btn">
                        <span></span>
                        Descargar todos los certificados
                    </button>
                </form>

                <!-- Tabla de certificados -->
                <table class="certificates-table">
                    <thead>
                        <tr>
                            <th width="55%">Descripción</th>
                            <th width="25%">Fecha</th>
                            <th width="20%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archivos as $archivo)
                            <tr>
                                <td class="description-cell">{{ $archivo->descripcion }}</td>
                                <td class="date-cell">{{ $archivo->fecha ?: 'Sin fecha especificada' }}</td>
                                <td class="actions-cell">
                                    <a href="{{ $archivo->url }}" target="_blank" class="action-btn view-btn">
                                        <span></span> Ver
                                    </a>
                                    <a href="{{ $archivo->descargar_url }}" class="action-btn download-btn">
                                        <span>⬇</span> Descargar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <!-- Botón para volver -->
    <div class="back-container">
        <form action="{{ route('certificados_e.index') }}" method="GET">
            <button type="submit" class="back-btn">
                <span>←</span>
                Volver a la búsqueda
            </button>
        </form>
    </div>
</div>

</body>
</html>