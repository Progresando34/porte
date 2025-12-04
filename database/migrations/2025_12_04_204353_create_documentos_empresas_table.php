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
            $table->string('cedula', 20);        // Especifica longitud máxima
            $table->string('filename', 255);     // Nombre del archivo
            $table->longBinary('filedata');      // Usar LONGBLOB para archivos grandes
            $table->string('filetype', 100)->nullable(); // Tipo MIME (opcional pero útil)
            $table->bigInteger('filesize')->default(0); // Tamaño en bytes
            $table->timestamps();                // created_at y updated_at
            
            // Índices para mejor rendimiento
            $table->index('cedula');
            $table->index('created_at');
            
            // Comentario para la tabla (opcional)
            $table->comment('Tabla para almacenar documentos de empresas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_empresas');
    }
};