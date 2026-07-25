<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Infotenencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-toggle {
            min-width: 120px;
            transition: all 0.3s ease;
        }
        .field-disabled {
            background-color: #f8f9fa;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.4);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>Registro Infotenencia
                        </h4>
                        <a href="{{ route('registro-infotenencia.listar') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-list me-1"></i>Ver Registros
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Por favor corrige los siguientes errores:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('registro-infotenencia.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Resultado Apto -->
                            <div class="mb-3">
                                <label class="form-label fw-bold required">Resultado Apto</label>
                                <div>
                                    <button type="button" 
                                            class="btn btn-toggle btn-outline-secondary" 
                                            id="btnResultado"
                                            onclick="toggleResultado()">
                                        <span id="resultadoTexto">Falso</span>
                                    </button>
                                    <input type="hidden" name="resultado_apto" id="resultado_apto" value="0">
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle"></i> Haz clic en el botón para cambiar el estado
                                    </small>
                                </div>
                                @error('resultado_apto')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dirección IPS -->
                            <div class="mb-3">
                                <label for="direccion_ips" class="form-label fw-bold required">Dirección IPS</label>
                                <input type="text" 
                                       class="form-control field-disabled" 
                                       id="direccion_ips" 
                                       name="direccion_ips" 
                                       value="CL 21 A 0 B 75 BRR EL ROSAL"
                                       readonly>
                                <small class="text-muted">
                                    <i class="fas fa-lock"></i> Campo fijo según configuración
                                </small>
                                @error('direccion_ips')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sede IPS -->
                            <div class="mb-3">
                                <label for="sede_ips" class="form-label fw-bold required">Sede IPS</label>
                                <input type="text" 
                                       class="form-control field-disabled" 
                                       id="sede_ips" 
                                       name="sede_ips" 
                                       value="Sede Cucuta"
                                       readonly>
                                <small class="text-muted">
                                    <i class="fas fa-lock"></i> Campo fijo según configuración
                                </small>
                                @error('sede_ips')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold required">Nombre</label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}"
                                       placeholder="Ingrese el nombre completo"
                                       required>
                                @error('nombre')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cédula -->
                            <div class="mb-3">
                                <label for="cedula" class="form-label fw-bold required">Cédula</label>
                                <input type="text" 
                                       class="form-control @error('cedula') is-invalid @enderror" 
                                       id="cedula" 
                                       name="cedula" 
                                       value="{{ old('cedula') }}"
                                       placeholder="Ingrese el número de cédula"
                                       required>
                                @error('cedula')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Archivo Certificado -->
                            <div class="mb-3">
                                <label for="archivo_certificado" class="form-label fw-bold">Archivo Certificado</label>
                                <input type="file" 
                                       class="form-control @error('archivo_certificado') is-invalid @enderror" 
                                       id="archivo_certificado" 
                                       name="archivo_certificado"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">
                                    <i class="fas fa-file-upload"></i> Formatos permitidos: PDF, JPG, JPEG, PNG (Máx. 2MB)
                                </small>
                                @error('archivo_certificado')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha Expedición -->
                            <div class="mb-3">
                                <label for="fecha_expedicion" class="form-label fw-bold required">Fecha Expedición</label>
                                <input type="date" 
                                       class="form-control @error('fecha_expedicion') is-invalid @enderror" 
                                       id="fecha_expedicion" 
                                       name="fecha_expedicion" 
                                       value="{{ old('fecha_expedicion') }}"
                                       required>
                                @error('fecha_expedicion')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Registro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleResultado() {
            const hiddenInput = document.getElementById('resultado_apto');
            const textoResultado = document.getElementById('resultadoTexto');
            const btn = document.getElementById('btnResultado');
            
            if (hiddenInput.value === '0') {
                hiddenInput.value = '1';
                textoResultado.textContent = 'Verdadero ✅';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
            } else {
                hiddenInput.value = '0';
                textoResultado.textContent = 'Falso ❌';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }
        }

        // Mantener el estado si hay error de validación
        document.addEventListener('DOMContentLoaded', function() {
            const valorActual = document.getElementById('resultado_apto').value;
            if (valorActual === '1') {
                const textoResultado = document.getElementById('resultadoTexto');
                const btn = document.getElementById('btnResultado');
                textoResultado.textContent = 'Verdadero ✅';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>