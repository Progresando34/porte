<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Certificados - Trabajador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
            margin: 0;
        }
        
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
        }
        
        h2 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
            color: #333;
        }
        
        .user-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #28a745;
        }
        
        .user-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .user-info strong {
            color: #155724;
        }
        
        .warning-note {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #ffc107;
        }
        
        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        button {
            padding: 12px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #1e7e34;
        }
        
        .mensaje {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 5px;
            text-align: center;
        }
        
        .logout-btn {
            background: #dc3545;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .container {
                padding: 15px;
                margin: 0;
                border-radius: 8px;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            input, button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Consulta de Certificados - Trabajador</h2>
    
    <!-- Información del trabajador -->
    <div class="user-info">
        <p><strong>Trabajador:</strong> {{ session('trabajador_nombre') }}</p>
        <p><strong>Cédula:</strong> {{ session('trabajador_cedula') }}</p>
        <p><strong>Usuario:</strong> {{ session('trabajador_usuario') }}</p>
    </div>
    
    <!-- Advertencia -->
    <div class="warning-note">
        ⚠️ <strong>Nota:</strong> Solo puedes consultar tu propia cédula: 
        <strong>{{ session('trabajador_cedula') }}</strong>
    </div>

    @if (session('mensaje'))
        <div class="mensaje">{{ session('mensaje') }}</div>
    @endif

    <!-- Formulario con cédula pre-cargada -->
    <form method="POST" action="{{ route('certificados_e.buscar') }}">
        @csrf
        <input type="hidden" name="cedula" value="{{ session('trabajador_cedula') }}">
        
        <label>Tu cédula (solo puedes consultar esta):</label>
        <input type="text" value="{{ session('trabajador_cedula') }}" readonly disabled style="background-color: #f8f9fa;">
        
        <button type="submit">🔍 Consultar mis certificados</button>
    </form>
    
    <!-- Botón de logout -->
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="logout-btn">🚪 Cerrar sesión</button>
    </form>
</div>
</body>
</html>