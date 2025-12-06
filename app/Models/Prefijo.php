<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefijo extends Model
{
    use HasFactory;

    protected $fillable = ['prefijo', 'descripcion', 'activo'];
    
    // Relación con usuarios
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_prefijos', 'prefijo_id', 'user_id')
                    ->withTimestamps();
    }

    // Scope para prefijos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Método para desactivar prefijo
    public function desactivar()
    {
        $this->activo = false;
        return $this->save();
    }

    // Método para activar prefijo
    public function activar()
    {
        $this->activo = true;
        return $this->save();
    }
}