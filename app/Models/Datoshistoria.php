<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datoshistoria extends Model
{
    use HasFactory;

    protected $table = 'datoshistoria';
    
    // Permitir todos los campos para asignación masiva
    protected $guarded = [];

    // Casts para los campos
    protected $casts = [
        'FECHA' => 'date',
        'TRABALTU' => 'boolean',
        'ESPACIOS' => 'boolean',
        'EOSTEO' => 'boolean',
    ];

    // Accessor para formatear fecha
    public function getFechaLecturaAttribute()
    {
        return $this->FECHA ? $this->FECHA->format('d/m/Y') : null;
    }

    // Scope para búsqueda por cédula
    public function scopePorCedula($query, $cedula)
    {
        return $query->where('CEDULA', $cedula);
    }

    // Scope para búsqueda por nombre
    public function scopePorNombre($query, $nombre)
    {
        return $query->where('NOMBRE', 'LIKE', "%{$nombre}%");
    }

    // Scope para búsqueda por fecha
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('FECHA', $fecha);
    }
}