<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ ESTA ES LA IMPORTANTE
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_id', // Asegúrate de agregar esto si vas a llenar este campo al registrar
    'avatar', // 👈 solo el nombre de la columna
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

    // 🔽 Agrega esta función:
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
