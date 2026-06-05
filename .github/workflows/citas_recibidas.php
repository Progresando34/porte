
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citas_recibidas extends Model
{
    protected $table = 'Citas_recibidas';
    
    protected $fillable = [
        'cedula', 'nombre', 'fecha', 'mision', 
        'nit_empresa', 'nombre_empresa', 'mision_empresa', 'datos_completos'
    ];
    
    protected $casts = [
        'fecha' => 'date',
        'datos_completos' => 'array'
    ];
}