<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('certificados', 'certificados_armas');
    }

    public function down(): void
    {
        Schema::rename('certificados_armas', 'certificados');
    }
};