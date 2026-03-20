<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Contraseña - Trabajador</title>
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
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .mensaje.success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .back-btn {
            background: #6c757d;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        .error-text {
            color: #dc3545;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .container {
                padding: 15px;
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
    <h2>Actualizar Contraseña</h2>
    
    <!-- Información del trabajador -->
    <div class="user-info">
        <p><strong>Trabajador:</strong> {{ session('trabajador_nombre') }}</p>
        <p><strong>Cédula:</strong> {{ session('trabajador_cedula') }}</p>
        <p><strong>Usuario:</strong> {{ session('trabajador_usuario') }}</p>
    </div>
    
    @if (session('success'))
        <div class="mensaje success">{{ session('success') }}</div>
    @endif
    
    @if (session('error'))
        <div class="mensaje error">{{ session('error') }}</div>
    @endif
    
    @if ($errors->any())
        <div class="mensaje error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif
    
    <!-- Formulario para actualizar contraseña -->
    <form method="POST" action="{{ route('trabajadores.actualizar-password') }}">
        @csrf
        @method('PUT')
        
        <label for="current_password">Contraseña Actual:</label>
        <input type="password" id="current_password" name="current_password" required>
        
        <label for="new_password">Nueva Contraseña:</label>
        <input type="password" id="new_password" name="new_password" required>
        
        <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
        
        <button type="submit">🔒 Actualizar Contraseña</button>
    </form>
    
    <!-- Botón para volver -->
    <form method="GET" action="{{ route('trabajadores.certificados.index') }}">
        <button type="submit" class="back-btn">← Volver al panel</button>
    </form>
</div>
</body>
</html>