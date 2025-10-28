<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Certificados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 1100px; /* más ancho para permitir dos logos grandes */
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* ✅ Contenedor flexible para los logos */
        .logos-container {
            display: flex;
            justify-content: center; /* centra horizontalmente */
            align-items: center;     /* alinea verticalmente */
            flex-wrap: wrap;         /* permite que se muevan en pantallas pequeñas */
            gap: 20px;               /* espacio entre logos */
            margin-bottom: 20px;
        }

        .logos-container img {
            width: 250px;         /* tamaño fijo y proporcional */
            max-width: 40%;       /* no más del 40% del contenedor */
            height: auto;         /* mantiene proporción */
            object-fit: contain;  /* evita deformaciones */
        }

        .cedula-block {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            background: #fafafa;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            margin-bottom: 10px;
            color: #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        a {
            color: #28a745;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .download-btn {
            background: #28a745;
            margin-bottom: 20px;
        }

        /* ✅ Responsividad: cuando la pantalla sea más pequeña */
        @media (max-width: 600px) {
            .logos-container img {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ✅ Contenedor de logos -->
    <div class="logos-container">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo 1">
        <img src="{{ asset('images/hos.png') }}" alt="Logo 2">
    </div>

    <h2>Certificados encontrados</h2>

    @foreach ($resultados as $cedula => $archivos)
        <div class="cedula-block">
            <h3>Cédula: {{ $cedula }}</h3>

            <form method="GET" action="{{ route('certificados_e.descargarMultiples') }}">
                <input type="hidden" name="cedulas[]" value="{{ $cedula }}">
                <button type="submit" class="download-btn">Descargar certificados de esta cédula</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Nombre del archivo</th>
                        <th>Ver / Descargar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($archivos as $archivo)
                        <tr>
                            <td>{{ $archivo->nombre_archivo }}</td>
                            <td><a href="{{ $archivo->url }}" target="_blank">Ver PDF</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <form action="{{ route('certificados_e.index') }}" method="GET">
        <button type="submit">Volver</button>
    </form>
</div>
</body>
</html>
