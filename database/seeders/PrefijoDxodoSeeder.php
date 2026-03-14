<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prefijo;

class PrefijoDxodoSeeder extends Seeder
{
    public function run(): void
    {
        Prefijo::firstOrCreate(
            ['prefijo' => 'dxodo'],
            [
                'descripcion' => 'RX ODONTOLOGIA ODO',
                'activo' => true
            ]
        );
    }
}