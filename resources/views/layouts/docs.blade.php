<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Documentación')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .sidebar-docs {
            width: 220px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f0f0f0;
            color: #333;
            padding: 20px 15px;
        }

        .active-doc-link {
    background-color: #d1e7dd; /* Verde claro */
    color: #0f5132 !important; /* Verde oscuro del texto */
    font-weight: bold;
}

        .sidebar-docs a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 10px 10px;
            border-radius: 5px;
        }

        .sidebar-docs a:hover {
            background-color: #ddd;
            color: #000;
        }

        .main-docs-content {
            margin-left: 220px;
            padding: 30px;
        }
    </style>
</head>
<body>

    {{-- Sidebar para docs --}}
    <div class="sidebar-docs">
        @include('partials.sidebar_docs')
    </div>

       <div class="sidebar-client">
        @include('partials.sidebar_client')
    </div>

    {{-- Contenido principal --}}
    <div class="main-docs-content">
        @yield('content')
    </div>

</body>
</html>
