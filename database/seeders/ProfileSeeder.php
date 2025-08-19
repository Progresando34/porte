<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ✅ Importar DB

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('profiles')->insert([
            ['name' => 'admin'],
            ['name' => 'cliente'],
            ['name' => 'empleado'],
            ['name' => 'sanidad'],
        ]);
    }
}
