<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

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
    ];

    // Relación con prefijos
    public function prefijos()
    {
        return $this->belongsToMany(
            Prefijo::class,
            'trabajador_prefijos', // nombre de la tabla pivot
            'trabajador_id',       // foreign key en pivot
            'prefijo_id'           // related key en pivot
        )->withTimestamps();
    }

    // Método para obtener IDs de prefijos
    public function obtenerPrefijosIds()
    {
        // Si ya está cargada la relación, usa cache
        if ($this->relationLoaded('prefijos')) {
            return $this->prefijos->pluck('id')->toArray();
        }
        
        // Si no está cargada, consulta solo los IDs (más eficiente)
        return $this->prefijos()->pluck('prefijos.id')->toArray();
    }

    // Mutador para hashear automáticamente la contraseña
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    // Método necesario para autenticación
    public function getAuthIdentifierName()
    {
        return 'usuario';
    }

    // Método para verificar si el trabajador está activo
    public function isActive()
    {
        return $this->activo === true;
    }
    
    // Si necesitas un scope para trabajadores activos
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }
}