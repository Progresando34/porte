<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Mi App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

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
            background-color: rgba(20, 173, 0, 1);
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
            background-color: rgba(26, 31, 0, 1);
            color: white;
        }

        .main-content {
            margin-left: 220px;
            padding: 30px;
        }
    </style>
</head>
<body>

    {{-- Barra Lateral Izquierda --}}
    <div class="sidebar">
        <div class="text-center mb-4">
            <img src="{{ asset('images/prog.webp') }}" alt="Logo" class="img-fluid" style="max-width: 200px;">
        </div>
        <div class="text-center mb-4">
            <img src="{{ asset('images/logoeagle.png') }}" alt="Logo" class="img-fluid" style="max-width: 200px;">
        </div>
        <h6 class="text-black mb-3">Eagle American De Seguridad</h6>
        <a href="#"><i class="fas fa-home me-2"></i>Definir</a>
        <a href="#"><i class="fas fa-file-alt me-2"></i>Definir</a>
        <a href="#"><i class="fas fa-cog me-2"></i>Definir</a>
    </div>

    {{-- Contenido principal --}}
    <div class="main-content">
        @yield('content')
    </div>

    {{-- Aquí se insertarán los scripts dinámicos --}}
    @stack('scripts')

</body>
</html>
