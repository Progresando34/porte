<?php
// test-db.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Test Base de Datos - documentos_empresas</h2>";

// 1. Verificar estructura de la tabla
echo "<h3>1. Estructura de la tabla:</h3>";
try {
    $columns = DB::select('DESCRIBE documentos_empresas');
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col->Field}</td>";
        echo "<td>{$col->Type}</td>";
        echo "<td>{$col->Null}</td>";
        echo "<td>{$col->Key}</td>";
        echo "<td>{$col->Default}</td>";
        echo "<td>{$col->Extra}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// 2. Verificar datos de la cédula 1111662604
echo "<h3>2. Datos para cédula 1111662604:</h3>";
try {
    $documentos = DB::table('documentos_empresas')
        ->where('cedula', '1111662604')
        ->get();
    
    echo "Total documentos: " . $documentos->count() . "<br>";
    
    if ($documentos->count() > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Cédula</th><th>Nombre Archivo</th><th>File Data</th><th>Creado</th></tr>";
        
        foreach ($documentos as $doc) {
            $hasFileData = isset($doc->file_data) && strlen($doc->file_data) > 0 ? 'SÍ (' . strlen($doc->file_data) . ' bytes)' : 'NO';
            echo "<tr>";
            echo "<td>{$doc->id}</td>";
            echo "<td>{$doc->cedula}</td>";
            echo "<td>" . ($doc->nombre_archivo ?? 'NULL') . "</td>";
            echo "<td>{$hasFileData}</td>";
            echo "<td>" . ($doc->created_at ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay documentos para esta cédula<br>";
        
        // Mostrar algunas cédulas disponibles
        $cedulas = DB::table('documentos_empresas')
            ->select('cedula', DB::raw('COUNT(*) as total'))
            ->groupBy('cedula')
            ->limit(10)
            ->get();
        
        if ($cedulas->count() > 0) {
            echo "<h4>Cédulas disponibles en la base de datos:</h4>";
            echo "<ul>";
            foreach ($cedulas as $c) {
                echo "<li>{$c->cedula} ({$c->total} documentos)</li>";
            }
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// 3. Verificar rutas disponibles
echo "<h3>3. Rutas disponibles:</h3>";
echo "<ul>";
echo "<li><a href='/certificados-empresariales'>/certificados-empresariales</a></li>";
echo "<li><a href='/documento/1/ver'>/documento/1/ver</a> (ejemplo con ID 1)</li>";
echo "<li><a href='/documento/1/descargar'>/documento/1/descargar</a></li>";
echo "</ul>";