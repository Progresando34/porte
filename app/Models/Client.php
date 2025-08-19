<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // 👇 Esto le dice a Laravel que use la tabla 'certificados'
    protected $table = 'armas';
}
