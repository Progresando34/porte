<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Trabajador extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'trabajadores';

    protected $fillable = [
        'nombre',
        'cedula',
        'usuario',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Para autenticación con campo personalizado
    public function getAuthIdentifierName()
    {
        return 'usuario'; // O 'id' si prefieres
    }

    // Sobreescribir para usar 'usuario' en lugar de 'email'
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    // Relación con prefijos
    public function prefijos()
    {
        return $this->belongsToMany(
            Prefijo::class,
            'trabajador_prefijos',
            'trabajador_id',
            'prefijo_id'
        )->withTimestamps();
    }

    public function obtenerPrefijosIds()
    {
        if ($this->relationLoaded('prefijos')) {
            return $this->prefijos->pluck('id')->toArray();
        }
        
        return $this->prefijos()->pluck('prefijos.id')->toArray();
    }

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }
}