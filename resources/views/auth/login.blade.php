<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Arial', sans-serif;
    }

    .login-container {
      display: flex;
      height: 100vh;
    }

    /* Panel izquierdo */
    .left-panel {
      background: linear-gradient(135deg, #9dff00ff, #004b10ff); /* Degradado azul */
      color: white;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 50px;
    }

    .left-panel h1 {
      font-size: 2.5rem;
      font-weight: bold;
    }

    .left-panel p {
      margin-top: 20px;
      font-size: 1.1rem;
      max-width: 350px;
      text-align: center;
    }

    /* Panel derecho */
    .right-panel {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      background: #fff;
    }

    .card {
      border: none;
      width: 100%;
      max-width: 380px;
    }

    .btn-google {
      background: #fff;
      border: 1px solid #ddd;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-google img {
      width: 18px;
      margin-right: 8px;
    }

    .text-small {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="login-container">
   
    
    <!-- Panel izquierdo -->
    <div class="left-panel">
    <img src="{{ asset('images/lo.png') }}" 
       alt="Logo" 
       style="width:390px; margin-bottom:20px;">

      <h1>Bienvenido de nuevo,<br> EAGLE AMERICAN</h1>
      <p>
        
      </p>
    </div>

    <!-- Panel derecho -->
    <div class="right-panel">
      <div class="card shadow p-4">
               <div class="text-center mb-3">
      <img src="{{ asset('images/logoeagle.png') }}" 
           alt="Logo"
           style="width:180px; max-width:100%; height:auto;">
    </div>


        <h3 class="mb-3 text-center">Ingreso al Portal</h3>
        <p class="text-center text-muted text-small">

        </p>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
          </div>
          <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
          </div>
          <button type="submit" class="btn btn-dark w-100">Iniciar sesión</button>

          <button type="button" class="btn btn-google w-100 mt-2">
          
        </form>

        <div class="mt-3 text-center">
          <a href="#">¿Olvidaste tu contraseña?</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
