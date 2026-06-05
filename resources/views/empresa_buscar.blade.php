<!DOCTYPE html>
<html>
<head>
    <title>Buscar Empresa</title>
</head>
<body>

<h2>Buscar Empresa por NIT</h2>

<form method="POST" action="{{ route('empresa.resultado') }}">
    @csrf
    <input type="text" name="nit" placeholder="Ingrese NIT" required>
    <button type="submit">Buscar</button>
</form>

</body>
</html>