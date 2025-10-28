<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificados_e', function (Blueprint $table) {
            $table->id();
            $table->string('cedula');
            $table->string('nombre_archivo');
            $table->string('ruta'); // ruta dentro del storage o public
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados_e');
    }
};
