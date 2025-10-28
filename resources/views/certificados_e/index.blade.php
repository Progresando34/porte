<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Certificados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .mensaje {
            color: red;
            margin-bottom: 10px;
        }
        .add-btn {
            background: #28a745;
            margin-bottom: 10px;
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
