<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Documentos - Empresas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .results-container {
            max-width: 600px;
            margin: 20px auto;
        }
        .company-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .lupa-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-container">
            <h2 class="text-center mb-4">
                <i class="fas fa-search"></i> Buscar Empresa
            </h2>
            <div class="position-relative">
                <input type="text" 
                       id="searchEmpresa" 
                       class="form-control form-control-lg" 
                       placeholder="Buscar por NIT o nombre de empresa..."
                       autocomplete="off">
                <div class="lupa-icon">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div id="resultados"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let searchTimeout;
        
        $('#searchEmpresa').on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            
            if(query.length < 2) {
                $('#resultados').html('');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: '{{ route("documentos.buscar") }}',
                    method: 'GET',
                    data: { q: query },
                    success: function(empresas) {
                        if(empresas.length > 0) {
                            let html = '<div class="results-container">';
                            empresas.forEach(empresa => {
                                html += `
                                    <div class="card company-card mb-2" onclick="window.location.href='{{ url("documentos/empresa") }}/${empresa.nit}'">
                                        <div class="card-body">
                                            <h5 class="card-title">${empresa.nombre}</h5>
                                            <p class="card-text text-muted">NIT: ${empresa.nit}</p>
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            $('#resultados').html(html);
                        } else {
                            $('#resultados').html('<div class="alert alert-warning mt-3">No se encontraron empresas</div>');
                        }
                    }
                });
            }, 300);
        });
    </script>
</body>
</html>