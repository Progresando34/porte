<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_prefijos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('prefijo_id');
            $table->timestamps();
            
            // Claves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('prefijo_id')->references('id')->on('prefijos')->onDelete('cascade');
            
            // Para evitar duplicados
            $table->unique(['user_id', 'prefijo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_prefijos');
    }
};