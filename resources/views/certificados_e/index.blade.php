<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Certificados</title>
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
            background: #007bff;
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
            background: #0056b3;
        }
        
        .mensaje {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 5px;
            text-align: center;
        }
        
        .add-btn {
            background: #28a745;
        }
        
        .add-btn:hover {
            background: #1e7e34;
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
        
        @media (max-width: 400px) {
            .container {
                padding: 12px;
            }
            
            h2 {
                font-size: 1.2rem;
            }
            
            input, button {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Consulta de Certificados</h2>

    @if (session('mensaje'))
        <div class="mensaje">{{ session('mensaje') }}</div>
    @endif

    <form method="POST" action="{{ route('certificados_e.buscar') }}">
        @csrf
        <label for="cedula">Ingrese su número de cédula:</label>
        <input type="text" name="cedula" id="cedula" placeholder="Ej: 123456789" />

        <div id="cedulas-multiples">
            <label>O ingrese varias cédulas:</label>
            <input type="text" name="cedulas_multiple[]" placeholder="Ej: 987654321" />
        </div>

        <button type="button" class="add-btn" onclick="agregarCampo()">+ Agregar otra cédula</button>
        <br>
        <button type="submit">Buscar</button>
    </form>
</div>

<script>
function agregarCampo() {
    const div = document.createElement('div');
    div.innerHTML = `<input type="text" name="cedulas_multiple[]" placeholder="Ej: 987654321" />`;
    document.getElementById('cedulas-multiples').appendChild(div);
}
</script>
</body>
</html>