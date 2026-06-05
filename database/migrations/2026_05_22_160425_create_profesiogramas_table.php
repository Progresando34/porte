<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesiograma extends Model
{
    protected $table = 'profesiogramas';
    
    protected $fillable = [
        'empresa_nit',
        'fecha_documento',
        'archivo',
        'nombre_archivo',
        'tamanio',
        'tipo_archivo',
        'cargo',
        'descripcion'
    ];
    
    protected $dates = ['fecha_documento', 'created_at', 'updated_at'];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_nit', 'nit');
    }
}