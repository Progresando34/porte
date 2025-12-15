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
            padding: 16px;
            color: #2c3e50;
            line-height: 1.6;
        }

        /* Avatar del usuario - mejorado para móviles */
        .user-avatar {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(52, 152, 219, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            max-width: calc(100vw - 32px);
        }

        .avatar-img, .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
            padding: 2px;
            flex-shrink: 0;
        }

        .avatar-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            color: #2c3e50;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: #7f8c8d;
            font-size: 10px;
            margin-top: 2px;
        }

        .container {
            max-width: 1200px;
            margin: 60px auto 20px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.02),
                0 10px 15px -3px rgba(0, 0, 0, 0.04),
                0 20px 25px -5px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            padding: 24px;
            border: 1px solid #f0f4f8;
            width: 100%;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e8edf5;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 2px;
        }

        .logo-container {
            margin-bottom: 20px;
            position: relative;
        }

        .logo {
            max-height: 60px;
            height: auto;
            width: auto;
            max-width: 100%;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
        }

        h1 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.3;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.2px;
        }

        /* Mensajes */
        .message-alert {
            background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            color: #dc2626;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
            border-left: 4px solid #dc2626;
            font-size: 14px;
        }

        .message-alert::before {
            content: "⚠️";
            font-size: 16px;
            opacity: 0.8;
            flex-shrink: 0;
        }

        /* Cédula Sections */
        .cedula-section {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e8edf5;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .cedula-header {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .cedula-title-wrapper {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .cedula-title {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cedula-title::before {
            content: '📋';
            font-size: 18px;
            opacity: 0.7;
        }

        .certificate-count {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        .cedula-number {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
            width: fit-content;
        }

        /* Botones */
        .download-all-btn {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.25);
            margin-bottom: 20px;
            letter-spacing: 0.2px;
            position: relative;
            overflow: hidden;
            width: 100%;
            text-align: center;
        }

        /* Tabla responsiva */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -24px;
            padding: 0 24px;
        }

        .certificates-table {
            width: 100%;
            min-width: 600px;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 4px;
            border-radius: 16px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #e8edf5;
        }

        .certificates-table th {
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: none;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            white-space: nowrap;
        }

        .certificates-table td {
            padding: 16px 20px;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 400;
            border-bottom: none;
        }

        .description-cell {
            color: #2c3e50;
            font-weight: 500;
            position: relative;
            padding-left: 24px;
            min-width: 200px;
        }

        .date-cell {
            color: #64748b;
            font-size: 13px;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .actions-cell {
            text-align: center;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 13px;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .view-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .download-btn {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
        }

        /* Sin resultados */
        .no-results {
            text-align: center;
            padding: 40px 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            border: 2px dashed #cbd5e1;
            margin: 24px 0;
        }

        .no-results h3 {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .no-results p {
            color: #94a3b8;
            font-size: 14px;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Botón Volver */
        .back-container {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e8edf5;
            position: relative;
        }

        .back-btn {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        /* Media Queries para pantallas más grandes */
        @media (min-width: 640px) {
            body {
                padding: 20px;
            }
            
            .container {
                padding: 32px;
                margin-top: 70px;
                border-radius: 24px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .subtitle {
                font-size: 16px;
            }
            
            .message-alert {
                padding: 20px 28px;
                font-size: 15px;
            }
            
            .cedula-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .download-all-btn,
            .back-btn {
                width: auto;
                padding: 16px 32px;
            }
            
            .action-buttons {
                flex-direction: row;
                gap: 8px;
            }
            
            .action-btn {
                width: auto;
            }
            
            .table-responsive {
                margin: 0;
                padding: 0;
            }
        }

        @media (min-width: 768px) {
            .container {
                padding: 40px;
            }
            
            h1 {
                font-size: 32px;
            }
            
            .user-avatar {
                top: 20px;
                right: 20px;
                padding: 10px 16px;
                gap: 12px;
            }
            
            .avatar-img, .avatar-placeholder {
                width: 48px;
                height: 48px;
            }
            
            .user-name {
                font-size: 14px;
            }
            
            .user-role {
                font-size: 12px;
            }
            
            .certificates-table th,
            .certificates-table td {
                padding: 20px 24px;
                font-size: 15px;
            }
            
            .action-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        @media (min-width: 1024px) {
            body {
                padding: 30px;
            }
            
            .container {
                padding: 48px;
            }
        }

        /* Animaciones */
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

        /* Scroll personalizado */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            border-radius: 4px;
        }

        /* Estados focus para accesibilidad */
        button:focus,
        a:focus {
            outline: 2px solid #3498db;
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* Ocultar avatar en pantallas muy pequeñas si es necesario */
        @media (max-width: 360px) {
            .user-avatar .user-info {
                display: none;
            }
            
            .user-avatar {
                padding: 8px;
            }
        }
    </style>
</head>

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
            <h3>No se encontraron certificados</h3>
            <p>No se encontraron documentos para la(s) cédula(s) ingresada(s).</p>
        </div>
    @else
        @foreach ($resultados as $cedula => $archivos)
            <div class="cedula-section">
                <div class="cedula-header">
                    <div class="cedula-title-wrapper">
                        <span class="cedula-title">Documentos encontrados</span>
                        <span class="certificate-count">{{ count($archivos) }} documento(s)</span>
                    </div>
                    <div class="cedula-number">Cédula: {{ $cedula }}</div>
                </div>

                @if(count($archivos) > 0)
                    <!-- Botón para descargar todos -->
                    <form method="POST" action="{{ route('certificados_e.descargarMultiples') }}">
                        @csrf
                        <input type="hidden" name="cedula" value="{{ $cedula }}">
                        <button type="submit" class="download-all-btn">
                            <span>📥</span>
                            Descargar todos los certificados
                        </button>
                    </form>

                    <!-- Tabla de certificados -->
                    <div class="table-responsive">
                        <table class="certificates-table">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($archivos as $archivo)
                                    <tr>
                                        <td class="description-cell">{{ $archivo->descripcion }}</td>
                                        <td class="date-cell">{{ $archivo->fecha ?: 'Sin fecha especificada' }}</td>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <a href="{{ $archivo->url }}" target="_blank" class="action-btn view-btn">
                                                    <span>👁️</span> Ver
                                                </a>
                                                <a href="{{ $archivo->descargar_url }}" class="action-btn download-btn">
                                                    <span>⬇</span> Descargar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-results">
                        <h3>No se encontraron documentos para esta cédula</h3>
                        <p>La cédula {{ $cedula }} no tiene documentos asociados o no tienes permisos para verlos.</p>
                    </div>
                @endif
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