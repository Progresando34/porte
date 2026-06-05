<?php
// app/Models/CitaRecibida.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitaRecibida extends Model
{
    use HasFactory;

    protected $table = 'citas_recibidas';

    protected $fillable = [
        'cedula',
        'nombre',
        'fecha',
        'mision',
        'nit_empresa',
        'nombre_empresa',
        'mision_empresa',
        'ruta_resultados',      // NUEVO
        'carpeta_copiada',      // NUEVO
        'fecha_copia'           // NUEVO
    ];

    protected $casts = [
        'fecha' => 'date',
        'carpeta_copiada' => 'boolean',
        'fecha_copia' => 'datetime'
    ];
}