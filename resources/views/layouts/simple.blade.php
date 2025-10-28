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
        body { margin: 0; padding: 0; }

        .sidebar {
            width: 220px;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background-color: rgba(80, 201, 0, 1);
            color: white;
            padding: 20px 15px;
            transition: transform 0.3s ease;
        }


        .main-content {
    margin-left: 220px;
    padding: 30px;
    transition: margin-left 0.3s ease;
}

/* Evitar que el container interno vuelva a ocupar 100% */
.main-content .container-fluid {
    max-width: 100%;
}
       

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
           .main-content .container-fluid {
    margin-left: 0;
    padding-left: 0;
}
.table {
    table-layout: auto;
    width: 100%;
}
            .toggle-btn {
                position: fixed; top: 15px; left: 15px;
                z-index: 1100;
                background: rgba(123, 255, 0, 1);
                border: none; color: white;
                font-size: 1.5rem; border-radius: 5px;
                padding: 5px 10px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Botón hamburguesa (solo móvil) --}}
    <button class="toggle-btn d-md-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        
        <div class="text-center mb-4">
            

<div class="text-center mb-4 position-relative d-flex flex-row justify-content-center align-items-center gap-2">
    {{-- Logo --}}
    <img src="{{ asset('images/logoblanco.png') }}" 
         class="fixed-logo"
         style="width: 80px; height: auto;">

    {{-- Avatar --}}
    @if(Auth::check() && Auth::user()->avatar)
        <img src="{{ Storage::url(Auth::user()->avatar) }}" 
             class="img-fluid rounded-circle" 
             style="width: 80px; height: 80px; object-fit: cover;">
    @else
        <img src="{{ asset('images/default_avatar.png') }}" 
             class="img-fluid rounded-circle" 
             style="width: 80px; height: 80px; object-fit: cover;">
    @endif
</div>


        <div class="text-start mb-4">
            @if(Auth::check())
     <h6 class="text-white" style="font-weight: normal;">
    Bienvenido, {{ Auth::user()->name }}
</h6>

               
            @endif
        </div>


     {{-- Botón de Logout --}}
<div class="text-center mb-4">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
   <button type="submit" class="btn w-100" style="background-color: #b8c9b8ff; color: black;">
            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
        </button>
    </form>
</div>


    </div>

    {{-- Contenido principal --}}
    <div class="main-content">
        @yield('content')
    </div>

    {{-- Script toggle --}}
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- 👇 Aquí se inyectan los scripts de cada vista --}}
    @stack('scripts')
</body>
</html>
