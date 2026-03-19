<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RayosX extends Model
{
    protected $table = 'rayosxod';

    protected $fillable = [
        'nombre',
        'cedula',
        'fecha_rx',
        'nombre_archivo',
        'ruta'
    ];
}