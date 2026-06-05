{{-- resources/views/solo-vista/ver-datos.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Documento</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .info-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-item label {
            font-weight: 600;
            color: #6c757d;
            display: block;
            margin-bottom: 5px;
        }
        .btn-cerrar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-width: 80px;">
            <h2>Detalles del Documento</h2>
        </div>
        
        <div class="info-item">
            <label>Cédula</label>
            <div>{{ $documento->cedula ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>Nombres y Apellidos</label>
            <div>{{ $documento->nombre ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>Fecha</label>
            <div>{{ $documento->fecha ? date('d/m/Y', strtotime($documento->fecha)) : 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>Misión</label>
            <div>{{ $documento->mision ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>NIT Empresa</label>
            <div>{{ $documento->nit_empresa ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>Nombre Empresa</label>
            <div>{{ $documento->nombre_empresa ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <label>Misión Empresa</label>
            <div>{{ $documento->mision_empresa ?? 'N/A' }}</div>
        </div>
        
        <button class="btn-cerrar" onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>