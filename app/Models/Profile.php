<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relación con usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Métodos útiles
    public function esAdministrador()
    {
        return $this->id == 1; // Ajusta según tu ID de admin
    }

    public function esUsuarioNormal()
    {
        return $this->id == 2; // Ajusta según tus necesidades
    }
}