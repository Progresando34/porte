<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dcondicione extends Model
{
    protected $table = 'dcondiciones';
    
    protected $fillable = [
        'empresa_nit',
        'fecha_documento',
        'archivo',
        'nombre_archivo',
        'tamanio',
        'tipo_archivo',
        'tipo_condicion',
        'descripcion'
    ];
    
    protected $casts = [
        'fecha_documento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_nit', 'nit');
    }
}