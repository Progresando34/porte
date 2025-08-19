<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel con Barra Lateral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">
        <!-- Barra de administración (izquierda) -->
        <aside class="w-64 bg-green-600 text-white p-6">
            <h2 class="text-2xl font-bold mb-6">Administración</h2>
            <ul class="space-y-4">
       <li><a href="{{ route('armas.create') }}" class="hover:underline">Cargar Archivos</a></li>

          <li><a href="#" class="hover:underline">Configuración</a></li>


                <li><a href="{{ route('armas.licita') }}" class="hover:underline">Licitaciones</a></li>
                <li><a href="#" class="hover:underline">Salir</a></li>
               <li><a href="{{ route('armas.docs') }}" class="hover:underline">Cargar Certificado</a></li>
                 <li><a href="#" class="hover:underline">Información de Especialistas</a></li>
                    <li><a href="{{ route('usuarios.create') }}" class="hover:underline">Crear usuarios</a></li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-1 p-10">
            <h1 class="text-4xl font-bold text-blue-600 mb-4">Bienvenido al Panel</h1>
            <p class="text-gray-700">Aquí va el contenido principal de la página.</p>
        </main>
    </div>

</body>
</html>
