<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class certificados_e extends Model
{
    // 👇 Agrega esta línea
    protected $table = 'certificados_e';

    protected $fillable = ['cedula', 'nombre_archivo', 'ruta'];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->ruta);
    }
}
