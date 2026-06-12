{{-- resources/views/certificados_e/solo_vista/ver-documentos.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de Cédula {{ $cedula }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 { margin-bottom: 10px; }
        .pdf-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .pdf-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .pdf-card:hover { transform: translateY(-5px); }
        .pdf-header {
            background: #2fc900;
            color: white;
            padding: 15px;
        }
        .pdf-header h3 { 
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pdf-iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
        .pdf-info {
            padding: 15px;
            background: #f8f9fa;
        }
        .pdf-info p { margin: 5px 0; }
        .badge-prefijo {
            display: inline-block;
            background: #0066cc;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        .descripcion-prefijo {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        .btn-cerrar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 768px) {
            .pdf-grid { grid-template-columns: 1fr; }
            .pdf-iframe { height: 400px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-width: 80px; margin-bottom: 15px;">
            <h1>Documentos Encontrados</h1>
            <p>Cédula: <strong>{{ $cedula }}</strong> | Total: {{ count($pdfs) }} documento(s)</p>
            <p><strong>Nombre:</strong> {{ $cita->nombre ?? 'N/A' }}</p>
        </div>
        
        <div class="pdf-grid">
            @foreach($pdfs as $index => $pdf)
                <div class="pdf-card">
                    <div class="pdf-header">
                        <h3>
                            <span>
                                @php
                                    $descripciones = [
                                        'a' => 'Certificado de aptitud',
                                        'c' => 'Certificado laboral',
                                        'ev' => 'Evaluación de puesto',
                                        's' => 'Seguimiento',
                                        'vis' => 'Visita domiciliaria',
                                        'h' => 'Historia'
                                    ];
                                    $descripcion = $descripciones[$pdf['prefijo']] ?? 'Documento sin descripción';
                                @endphp
                                {{ $descripcion }}
                            </span>
                            <span>
                                <span class="badge-prefijo">Prefijo: {{ strtoupper($pdf['prefijo']) }}</span>
                            </span>
                        </h3>
                    </div>
                    
                    <iframe class="pdf-iframe" src="{{ route('solo_vista.ver.pdf', ['id' => $cita->id, 'archivo' => $pdf['nombre']]) }}"></iframe>
                    
                    <div class="pdf-info">
                        <p><strong>Archivo:</strong> {{ $pdf['nombre'] }}</p>
                        <p><strong>Fecha:</strong> {{ $cita->fecha ? date('d/m/Y', strtotime($cita->fecha)) : 'N/A' }}</p>
                        <p><strong>Misión:</strong> {{ $cita->mision ?? 'N/A' }}</p>
                        <p><strong>Empresa:</strong> {{ $cita->nombre_empresa ?? 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        
        <button class="btn-cerrar" onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>