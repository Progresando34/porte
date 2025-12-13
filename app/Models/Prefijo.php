<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefijo extends Model
{
    use HasFactory;

    protected $fillable = ['prefijo', 'descripcion', 'activo'];
    
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_prefijos', 'prefijo_id', 'user_id')
                    ->withTimestamps();
    }

   
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }


    public function desactivar()
    {
        $this->activo = false;
        return $this->save();
    }

   
    public function activar()
    {
        $this->activo = true;
        return $this->save();
    }
}