<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Agregar Bootstrap CDN para el diseño -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Establecer el fondo en el div principal que envuelve todo */
        .background-container {
            background-image: url('{{ asset("images/login") }}'); /* Ruta de la imagen */
            background-size: cover; /* Hace que la imagen cubra toda la pantalla */
            background-position: center; /* Centra la imagen */
            background-attachment: fixed; /* Fija la imagen de fondo */
            height: 100vh; /* Asegura que ocupe toda la altura de la ventana */
            display: flex; /* Usamos flex para alinear el contenido */
            justify-content: center; /* Centra el contenido horizontalmente */
            align-items: center; /* Centra el contenido verticalmente */
        }

        /* Fondo semi-transparente para la tarjeta */
        .card {
            background-color: rgba(255, 255, 255, 0.8); /* Fondo blanco con algo de transparencia */
        }

        /* Ajustar márgenes del contenedor */
        .container {
            z-index: 2; /* Asegura que la tarjeta esté por encima del fondo */
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Contenedor con imagen de fondo -->
    <div class="background-container">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg border-0 rounded">
                        <div class="card-body p-5">
                            <h2 class="mb-4 text-center">Iniciar Sesión</h2>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <strong>¡Ups!</strong> Hay un problema con tus credenciales.<br><br>
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group mb-3">
                                    <label for="email">Correo electrónico</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="password">Contraseña</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                                </div>

                                <div class="mt-3 text-center">
                                    <!-- Aquí podrías agregar enlaces como "Olvidé mi contraseña" -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agregar JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
