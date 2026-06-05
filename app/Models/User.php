<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log; 

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
{
    static::creating(function ($user) {
        Log::info('🔄 Intentando crear usuario: ' . $user->email);
    });
    
    static::created(function ($user) {
        Log::info('✅ Usuario creado exitosamente: ' . $user->email . ' (ID: ' . $user->id . ')');
    });
    
    static::updating(function ($user) {
        Log::info('🔄 Actualizando usuario: ' . $user->email);
    });
    
    static::deleting(function ($user) {
        Log::info('🗑️ Eliminando usuario: ' . $user->email);
    });
}

    // 🔽 Relación con el perfil
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    // 🔽 NUEVO: Relación muchos a muchos con prefijos
    public function prefijos()
    {
        return $this->belongsToMany(Prefijo::class, 'usuario_prefijos', 'user_id', 'prefijo_id')
                    ->withTimestamps();
    }

    // 🔽 NUEVO: Método para verificar acceso a un prefijo específico
    public function tieneAccesoPrefijo($prefijo)
    {
        // Si es admin (profile_id = 1), tiene acceso a todo
        if ($this->profile_id == 1) { // Ajusta según tu ID de admin
            return true;
        }
        
        // Verificar si el prefijo existe en los asignados al usuario
        return $this->prefijos()->where('prefijo', $prefijo)->exists();
    }

    // 🔽 NUEVO: Método para obtener array de prefijos
public function obtenerPrefijosArray()
{
    return $this->prefijos()->where('activo', true)->pluck('prefijo')->toArray();
}

    // 🔽 NUEVO: Método para obtener IDs de prefijos
    public function obtenerPrefijosIds()
    {
        return $this->prefijos()->pluck('prefijos.id')->toArray();
    }

    // 🔽 NUEVO: Método para sincronizar prefijos
    public function asignarPrefijos(array $prefijoIds)
    {
        return $this->prefijos()->sync($prefijoIds);
    }

    // 🔽 NUEVO: Método para agregar un prefijo
    public function agregarPrefijo($prefijoId)
    {
        return $this->prefijos()->attach($prefijoId);
    }

    // 🔽 NUEVO: Método para eliminar un prefijo
    public function eliminarPrefijo($prefijoId)
    {
        return $this->prefijos()->detach($prefijoId);
    }

    // 🔽 NUEVO: Scope para filtrar usuarios por prefijo
    public function scopeConPrefijo($query, $prefijo)
    {
        return $query->whereHas('prefijos', function ($q) use ($prefijo) {
            $q->where('prefijo', $prefijo);
        });
    }

    // 🔽 NUEVO: Método para verificar si es administrador
   public function esAdministrador()
{
    return $this->profile && $this->profile->name === 'admin';
}
}