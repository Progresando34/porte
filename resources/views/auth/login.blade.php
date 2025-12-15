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
      align-items:center;
      justify-content: center;
    }

    .btn-google img {
      width: 18px;
      margin-right: 8px;
    }

    .text-small {
      font-size: 0.9rem;
    }

    .alert {
      margin-bottom: 20px;
      padding: 12px 15px;
      border-radius: 8px;
      animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Estilo para campos con error */
    .is-invalid {
      border-color: #dc3545 !important;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .invalid-feedback {
      display: block;
      color: #dc3545;
      font-size: 0.875em;
      margin-top: 5px;
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

      <h1>Bienvenido de nuevo,
ingrese sus credenciales para continuar</h1>
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
       
        <!-- Mostrar mensajes de error generales -->
        @if($errors->any())
        <div class="alert alert-danger">
          <strong>Error:</strong>
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
        @endif

        <!-- Mostrar mensaje de error específico del campo 'login' -->
        @error('login')
        <div class="alert alert-danger">
          {{ $message }}
        </div>
        @enderror

        <!-- Mostrar mensaje de error específico del campo 'password' -->
        @error('password')
        <div class="alert alert-danger">
          {{ $message }}
        </div>
        @enderror

        <!-- Mostrar mensajes de éxito (logout exitoso, etc.) -->
        @if(session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
        @endif

        <!-- Mostrar mensajes de error de sesión -->
        @if(session('error'))
        <div class="alert alert-danger">
          {{ session('error') }}
        </div>
        @endif

        <!-- Mostrar mensajes de información -->
        @if(session('info'))
        <div class="alert alert-info">
          {{ session('info') }}
        </div>
        @endif

        <!-- Mostrar mensaje si viene de redirección por middleware -->
        @if(session('trabajador_redirect'))
        <div class="alert alert-warning">
          {{ session('trabajador_redirect') }}
        </div>
        @endif

        <!-- Mantener valores del formulario si hubo error -->
        @php
          $oldLogin = old('login', '');
          $oldPassword = old('password', '');
        @endphp
        
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <input type="text" 
                   class="form-control @error('login') is-invalid @enderror" 
                   name="login" 
                   placeholder="Usuario o Correo electrónico" 
                   value="{{ $oldLogin }}"
                   required
                   autofocus>
            <small class="text-muted">Puedes usar tu email o nombre de usuario</small>
            @error('login')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
            @enderror
          </div>
          
          <div class="mb-3">
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" 
                   placeholder="Contraseña" 
                   required>
            @error('password')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
            @enderror
          </div>
          
          <button type="submit" class="btn btn-dark w-100">Iniciar sesión</button>
        </form>

        <!-- Mostrar información de depuración solo si está habilitado -->
        @if(app()->environment('local'))
        <div class="mt-3">
          <details>
            <summary class="text-muted small">Información de depuración</summary>
            <div class="small mt-2">
              <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
              <p><strong>Trabajador autenticado:</strong> {{ session()->has('trabajador_autenticado') ? 'Sí' : 'No' }}</p>
              <p><strong>Usuario autenticado:</strong> {{ auth()->check() ? 'Sí' : 'No' }}</p>
              @if(session()->has('trabajador_nombre'))
                <p><strong>Trabajador:</strong> {{ session('trabajador_nombre') }}</p>
              @endif
            </div>
          </details>
        </div>
        @endif

        <div class="mt-3 text-center">
          <small class="text-muted">
            Si tienes problemas para ingresar, contacta al administrador
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Script para auto-ocultar alertas después de 5 segundos -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Ocultar alertas después de 5 segundos
      setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
          alert.style.transition = 'opacity 0.5s ease';
          alert.style.opacity = '0';
          setTimeout(function() {
            alert.style.display = 'none';
          }, 500);
        });
      }, 5000);
      
      // Enfocar el campo de login si está vacío
      const loginInput = document.querySelector('input[name="login"]');
      if (loginInput && !loginInput.value.trim()) {
        loginInput.focus();
      }
    });
  </script>
</body>
</html>