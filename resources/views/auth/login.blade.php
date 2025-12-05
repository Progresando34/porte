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
      background: linear-gradient(135deg, #9dff00ff, #004b10ff);
      color: white;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 50px;
      text-align: center;
    }

    .left-panel h1 {
      font-size: 2.2rem;
      font-weight: bold;
      line-height: 1.3;
    }

    .left-panel p {
      margin-top: 20px;
      font-size: 1rem;
      max-width: 350px;
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

    /* Responsividad */
    @media (max-width: 992px) {
      .left-panel h1 {
        font-size: 1.8rem;
      }
      .left-panel img {
        width: 250px;
      }
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column; /* Apila los paneles */
        height: auto;
      }

      .left-panel {
        padding: 30px 20px;
      }

      .left-panel h1 {
        font-size: 1.6rem;
      }

      .left-panel img {
        width: 180px; /* Logo más pequeño */
        margin-bottom: 15px;
      }

      .right-panel {
        padding: 20px;
      }

      .card {
        max-width: 100%;
      }
    }

    @media (max-width: 480px) {
      .left-panel h1 {
        font-size: 1.3rem;
      }
      .left-panel img {
        width: 150px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Panel izquierdo -->
    <div class="left-panel">
      <img src="{{ asset('images/lo.png') }}" 
           alt="Logo" 
           style="width:600px; max-width:100%; height:auto; margin-bottom:20px;">

      <p></p>
    </div>

    <!-- Panel derecho -->
    <div class="right-panel">
      <div class="card shadow p-4">
        <div class="text-center mb-3">
          <img src="{{ asset('images/logoi.png') }}" 
               alt="Logo"
               style="width:180px; max-width:100%; height:auto;">
        </div>

        <h3 class="mb-3 text-center">Ingreso al Portal</h3>
       
        <h3>hola bienvenido *********</h3>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
          </div>
          <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
          </div>
          <button type="submit" class="btn btn-dark w-100">Iniciar sesión</button>

  
        </form>

        <div class="mt-3 text-center">
        
        </div>
      </div>
    </div>
  </div>
</body>
</html>
