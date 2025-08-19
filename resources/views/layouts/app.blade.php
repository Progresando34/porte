<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Mi App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- Estilos personalizados --}}
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0b1c3c; /* azul mega oscuro */
            color: white;
            padding: 20px 15px;
        }

        .sidebar a {
            color: #bbb;
            text-decoration: none;
            display: block;
            padding: 10px 10px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #1a2a4d;
            color: white;
        }

        

        .main-content {
            margin-left: 220px;
            padding: 30px;
        }
    </style>
</head>
<body>

    {{-- Panel Lateral Izquierdo --}}
    <div class="sidebar">
        <h4 class="text-white mb-4">Panel de Administración - Jonathan Lopez</h4>
        <a href="{{ route('archivos.index') }}"><i class="fas fa-file-alt me-2"></i> Archivos</a>
        <a href="{{ route('armas.create') }}"><i class="fas fa-plus-circle me-2"></i> Crear Arma</a>
        <a href="#"><i class="fas fa-cogs me-2"></i> Configuración</a>
    </div>

    {{-- Contenido Principal --}}
    <div class="main-content">
        @yield('content')
    </div>

</body>
</html>
