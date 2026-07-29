<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    use HasFactory;

    protected $table = 'certificados_armas';

    protected $fillable = [
        'resultado_apto',
        'direccion_ips',
        'sede_ips',
        'nombre',
        'cedula',
        'archivo_certificado',
        'fecha_expedicion',
    ];

    //  ELIMINAR 'resultado_apto' DEL CAST
    protected $casts = [
        // 'resultado_apto' => 'boolean',  // ← ELIMINAR ESTA LÍNEA
        'fecha_expedicion' => 'date',
    ];

    // 🔥 ACCESSOR PARA OBTENER TEXTO DEL RESULTADO
    public function getResultadoAptoTextoAttribute()
    {
        $valor = (int) $this->attributes['resultado_apto'] ?? 0;
        
        if ($valor == 1) {
            return 'Apto';
        } elseif ($valor == 2) {
            return 'Aplazado';
        } else {
            return 'No Apto';
        }
    }

    // 🔥 ACCESSOR PARA OBTENER COLOR DEL BADGE
    public function getResultadoAptoColorAttribute()
    {
        $valor = (int) $this->attributes['resultado_apto'] ?? 0;
        
        if ($valor == 1) {
            return 'success';
        } elseif ($valor == 2) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}