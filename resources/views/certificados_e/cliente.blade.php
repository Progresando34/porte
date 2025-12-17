<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Certificados - Cliente</title>
    <style>
        /* Reset y base mobile-first */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 15px;
            line-height: 1.6;
        }
        
        .container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        
        h2 {
            margin-bottom: 20px;
            font-size: clamp(1.3rem, 4vw, 1.8rem);
            text-align: center;
            color: #0066cc;
            padding-bottom: 15px;
            border-bottom: 2px solid #eaeaea;
        }
        
        /* Información del usuario */
        .user-info {
            background: linear-gradient(to right, #e6f7ff, #f0f9ff);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #0066cc;
            position: relative;
            overflow: hidden;
        }
        
        .user-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #0066cc, #0099ff);
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: clamp(0.9rem, 3vw, 1rem);
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .user-info strong {
            color: #004080;
            min-width: 80px;
            display: inline-block;
        }
        
        .user-info span {
            color: #333;
            word-break: break-word;
        }
        
        /* Formulario */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: clamp(0.95rem, 3vw, 1rem);
        }
        
        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: clamp(0.95rem, 3vw, 1rem);
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        input:focus {
            outline: none;
            border-color: #0066cc;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }
        
        input::placeholder {
            color: #888;
            opacity: 0.8;
        }
        
        /* Contenedor de cédulas múltiples */
        #cedulas-multiples {
            margin-top: 10px;
        }
        
        #cedulas-multiples > div {
            margin-bottom: 10px;
            position: relative;
        }
        
        #cedulas-multiples > div:not(:first-child) {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Botones */
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 25px;
        }
        
        button {
            padding: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: clamp(0.95rem, 3vw, 1rem);
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .add-btn {
            background: #28a745;
            color: white;
        }
        
        .add-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .submit-btn {
            background: #0066cc;
            color: white;
        }
        
        .submit-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }
        
        .add-btn::before {
            content: '+';
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .submit-btn::before {
            content: '🔍';
        }
        
        /* Mensajes */
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive para tablets */
        @media (min-width: 768px) {
            body {
                padding: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .container {
                padding: 35px;
            }
            
            .button-group {
                flex-direction: row;
            }
            
            button {
                flex: 1;
            }
        }
        
        /* Responsive para móviles pequeños */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                padding: 20px;
                border-radius: 10px;
            }
            
            .user-info {
                padding: 15px;
            }
            
            input, button {
                padding: 12px;
            }
            
            .user-info p {
                flex-direction: column;
                gap: 2px;
            }
            
            .user-info strong {
                min-width: auto;
                margin-bottom: 2px;
            }
        }
        
        /* Responsive para pantallas grandes */
        @media (min-width: 1024px) {
            .container {
                max-width: 700px;
            }
        }
        
        /* Mejora de accesibilidad */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Estados de enfoque para accesibilidad */
        button:focus, input:focus {
            outline: 3px solid #4d90fe;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Consulta de Certificados - Cliente</h2>
    
    <!-- Información del cliente -->
    @if(auth()->check())
    <div class="user-info">
        <p><strong>Cliente:</strong> <span>{{ auth()->user()->name }}</span></p>
        <p><strong>Email:</strong> <span>{{ auth()->user()->email }}</span></p>
        <p><strong>Perfil:</strong> <span>Cliente</span></p>
        <p><strong>Nota:</strong> <span>Puedes consultar cualquier cédula</span></p>
    </div>
    @endif

    @if (session('mensaje'))
        <div class="mensaje {{ session('tipo') ?? 'success' }}">{{ session('mensaje') }}</div>
    @endif

    <!-- Formulario para clientes (sin restricciones) -->
    <form method="POST" action="{{ route('cliente.certificados.buscar') }}">
        @csrf
        
        <div class="form-group">
            <label for="cedula">Ingrese número(s) de cédula:</label>
            <input type="text" name="cedula" id="cedula" placeholder="Ej: 123456789" required />
        </div>

        <div class="form-group">
            <label>O ingrese varias cédulas (opcional):</label>
            <div id="cedulas-multiples">
                <div>
                    <input type="text" name="cedulas_multiple[]" placeholder="Ej: 987654321" />
                </div>
            </div>
        </div>

        <div class="button-group">
            <button type="button" class="add-btn" onclick="agregarCampo()">Agregar otra cédula</button>
            <button type="submit" class="submit-btn">Buscar certificados</button>
        </div>
    </form>
</div>

<script>
function agregarCampo() {
    const div = document.createElement('div');
    div.innerHTML = `<input type="text" name="cedulas_multiple[]" placeholder="Ej: 987654321" />`;
    document.getElementById('cedulas-multiples').appendChild(div);
    
    // Enfocar el nuevo campo automáticamente
    const inputs = div.getElementsByTagName('input');
    if (inputs.length > 0) {
        inputs[0].focus();
    }
}

// Mejora: Permitir usar Enter para agregar campos cuando estás en el último input
document.addEventListener('DOMContentLoaded', function() {
    const cedulasMultiples = document.getElementById('cedulas-multiples');
    
    cedulasMultiples.addEventListener('keypress', function(e) {
        if (e.target.tagName === 'INPUT' && e.key === 'Enter') {
            e.preventDefault();
            agregarCampo();
        }
    });
});
</script>
</body>
</html>