<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrabajadoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabla principal de trabajadores
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cedula')->unique();
            $table->string('usuario')->unique();  // Nuevo campo: nombre de usuario
            $table->string('password');           // Nuevo campo: contraseña
            $table->timestamp('email_verified_at')->nullable(); // Para verificación si es necesario
            $table->rememberToken();              // Para "recuérdame"
            $table->boolean('activo')->default(true); // Para activar/desactivar trabajador
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla pivote para la relación muchos a muchos con prefijos
        Schema::create('trabajador_prefijos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')
                  ->constrained('trabajadores')
                  ->onDelete('cascade');
            $table->foreignId('prefijo_id')
                  ->constrained('prefijos')
                  ->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['trabajador_id', 'prefijo_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trabajador_prefijos');
        Schema::dropIfExists('trabajadores');
    }
}