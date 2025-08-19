<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $fillable = [
        'nombre',
        'cedula',
        'tipo_certificado',
        'archivo_certificado',
        'fecha_expedicion',
    ];
}
