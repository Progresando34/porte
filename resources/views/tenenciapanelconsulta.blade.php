<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Consulta - Tenencia</title>
    
    <!-- Aquí puedes incluir tus estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1>Panel de Consulta - Tenencia</h1>
                <p>Este panel es público y no requiere autenticación.</p>
                
                <!-- Aquí va tu contenido -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resultados de la Consulta</h5>
                        <!-- Muestra tus datos aquí -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>