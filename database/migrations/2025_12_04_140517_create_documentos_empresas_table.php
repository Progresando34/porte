<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 30);
            $table->string('filename', 255);
            $table->longBlob('filedata');
            $table->dateTime('created_at');
            
            $table->unique(['cedula', 'filename'], 'unq_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_empresas');
    }
};
