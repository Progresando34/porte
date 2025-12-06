<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prefijo;

class PrefijosTableSeeder extends Seeder
{
    public function run(): void
    {
        $prefijos = [
            ['prefijo' => 'H', 'descripcion' => 'Historia ocupacional, ingreso, egreso, periódico'],
            ['prefijo' => 'HING', 'descripcion' => 'Historia ocupacional de ingreso en el examen ingreso/egreso'],
            ['prefijo' => 'HG', 'descripcion' => 'Historia Medicina General'],
            ['prefijo' => 'HCV', 'descripcion' => 'Historia Cardiovascular'],
            ['prefijo' => 'HNU', 'descripcion' => 'Historia de Nutrición'],
            ['prefijo' => 'C', 'descripcion' => 'Certificado de aptitud ocupacional'],
            ['prefijo' => 'CTA', 'descripcion' => 'Certificado CENS o certificado de Alturas'],
            ['prefijo' => 'CMA', 'descripcion' => 'Certificado de manipulación de alimentos'],
            ['prefijo' => 'V', 'descripcion' => 'Vertigo'],
            ['prefijo' => 'OM', 'descripcion' => 'Osteomuscular'],
            ['prefijo' => 'A', 'descripcion' => 'Audiometría'],
            ['prefijo' => 'EV', 'descripcion' => 'Examen de voz'],
            ['prefijo' => 'O', 'descripcion' => 'Optometría'],
            ['prefijo' => 'VIS', 'descripcion' => 'Visiometría'],
            ['prefijo' => 'E', 'descripcion' => 'Espirometría'],
            ['prefijo' => 'RE', 'descripcion' => 'Resultado Espirometría'],
            ['prefijo' => 'L', 'descripcion' => 'Laboratorio Clínico'],
            ['prefijo' => 'S', 'descripcion' => 'Psicología'],
            ['prefijo' => 'RT', 'descripcion' => 'Rx Torax'],
            ['prefijo' => 'R', 'descripcion' => 'RX Columna'],
            ['prefijo' => 'EKG', 'descripcion' => 'Electrocardiograma'],
            ['prefijo' => 'REM', 'descripcion' => 'Remisión a EPS'],
            ['prefijo' => 'RPYP', 'descripcion' => 'Remisión a PYP'],
            ['prefijo' => 'CV', 'descripcion' => 'Carnet de vacunas'],
            ['prefijo' => 'VF', 'descripcion' => 'Valoración Fisioterapia'],
            ['prefijo' => 'CM', 'descripcion' => 'Coordinación Motriz'],
            ['prefijo' => 'PS', 'descripcion' => 'Psicosensometrica'],
            ['prefijo' => 'CI', 'descripcion' => 'Certificado de ingreso'],
            ['prefijo' => 'TH', 'descripcion' => 'Toxicología'],
            ['prefijo' => 'ARM', 'descripcion' => 'Documento adicional'],
            ['prefijo' => 'J', 'descripcion' => 'Documento general'],
            ['prefijo' => 'TESTPSICOLOGIA', 'descripcion' => 'Test psicológico'],
            ['prefijo' => 'CILEGAL', 'descripcion' => 'Certificado legal'],
            ['prefijo' => 'CILD', 'descripcion' => 'Certificado legal documentado'],
            ['prefijo' => 'CIL', 'descripcion' => 'Certificado legal intermedio'],
        ];

        foreach ($prefijos as $prefijo) {
            Prefijo::create($prefijo);
        }
    }
}