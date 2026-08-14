<?php
// verificar-archivos.php - Sube este archivo al servidor

echo "=== VERIFICACIÓN DE ARCHIVOS EN EL SERVIDOR ===\n\n";

// 1. Verificar la ruta base
$basePath = __DIR__;
echo "1. Ruta base del proyecto:\n";
echo "   $basePath\n\n";

// 2. Verificar FPDF
$fpdfPath = $basePath . '/vendor/setasign/fpdf/fpdf.php';
echo "2. Verificando FPDF:\n";
echo "   Ruta: $fpdfPath\n";
echo "   ¿Existe? " . (file_exists($fpdfPath) ? "✅ SI" : "❌ NO") . "\n";

if (file_exists($fpdfPath)) {
    echo "   Tamaño: " . filesize($fpdfPath) . " bytes\n";
    echo "   Permisos: " . substr(sprintf('%o', fileperms($fpdfPath)), -4) . "\n";
    echo "   Contenido (primeros 100 caracteres):\n";
    $content = file_get_contents($fpdfPath, false, null, 0, 200);
    echo "   " . substr($content, 0, 100) . "...\n";
} else {
    // Buscar en otras ubicaciones
    echo "   Buscando en ubicaciones alternativas:\n";
    $alternativas = [
        '/vendor/setasign/fpdf/src/Fpdf.php',
        '/vendor/fpdf/fpdf.php',
        '/vendor/fpdf/fpdf/fpdf.php'
    ];
    foreach ($alternativas as $alt) {
        $altPath = $basePath . $alt;
        echo "     - $alt: " . (file_exists($altPath) ? "✅ SI" : "❌ NO") . "\n";
    }
}
echo "\n";

// 3. Verificar FPDI
$fpdiPath = $basePath . '/vendor/setasign/fpdi/src/Fpdi.php';
echo "3. Verificando FPDI:\n";
echo "   Ruta: $fpdiPath\n";
echo "   ¿Existe? " . (file_exists($fpdiPath) ? "✅ SI" : "❌ NO") . "\n";

if (file_exists($fpdiPath)) {
    echo "   Tamaño: " . filesize($fpdiPath) . " bytes\n";
    echo "   Permisos: " . substr(sprintf('%o', fileperms($fpdiPath)), -4) . "\n";
}
echo "\n";

// 4. Verificar estructura de carpetas
echo "4. Estructura de carpetas vendor/setasign:\n";
$setasignPath = $basePath . '/vendor/setasign';
if (is_dir($setasignPath)) {
    $items = scandir($setasignPath);
    echo "   Contenido de vendor/setasign/:\n";
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $isDir = is_dir($setasignPath . '/' . $item);
            echo "   - " . ($isDir ? "📁" : "📄") . " $item\n";
            
            // Si es fpdf, mostrar su contenido
            if ($item == 'fpdf' && $isDir) {
                $fpdfItems = scandir($setasignPath . '/fpdf');
                echo "     Contenido de fpdf/:\n";
                foreach ($fpdfItems as $subItem) {
                    if ($subItem != '.' && $subItem != '..') {
                        echo "       - $subItem\n";
                    }
                }
            }
        }
    }
} else {
    echo "   ❌ La carpeta vendor/setasign NO existe!\n";
}