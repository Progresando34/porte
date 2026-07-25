<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    use HasFactory;

    // **IMPORTANTE: Especificar el nombre correcto de la tabla**
    protected $table = 'certificados_armas';  // <- Cambiar de 'certificados' a 'certificados_armas'

    // Campos que pueden ser llenados masivamente
    protected $fillable = [
        'resultado_apto',
        'direccion_ips',
        'sede_ips',
        'nombre',
        'cedula',
        'archivo_certificado',
        'fecha_expedicion',
    ];

    // Cast de tipos de datos
    protected $casts = [
        'resultado_apto' => 'boolean',
        'fecha_expedicion' => 'date',
    ];
}