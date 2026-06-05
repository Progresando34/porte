<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar tabla si existe
        Schema::dropIfExists('empresas');
        
        Schema::create('empresas', function (Blueprint $table) {
            $table->string('nit', 50)->primary();
            $table->timestamps();
            
            // Campos opcionales (todos nullable para evitar errores)
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 255)->nullable();
            $table->date('fechacon')->nullable();
            $table->string('formaconta', 50)->nullable();
            $table->string('empfactu', 255)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('correo', 255)->nullable();
            $table->string('correoa', 255)->nullable();
            $table->string('aeconomica', 100)->nullable();
            $table->string('razoncial', 255)->nullable();
            $table->string('contacto1', 100)->nullable();
            $table->string('cargo1', 100)->nullable();
            $table->string('telefono1', 50)->nullable();
            $table->string('email1', 255)->nullable();
            $table->string('contacto2', 100)->nullable();
            $table->string('cargo2', 100)->nullable();
            $table->string('telefono2', 50)->nullable();
            $table->string('email2', 255)->nullable();
            $table->string('contacto3', 100)->nullable();
            $table->string('cargo3', 100)->nullable();
            $table->string('telefono3', 50)->nullable();
            $table->string('email3', 255)->nullable();
            $table->text('observacio')->nullable();
            $table->text('requisitos')->nullable();
            $table->string('estado', 20)->nullable();
            $table->string('caracteris', 50)->nullable();
            $table->string('gestion', 50)->nullable();
            $table->string('formapago', 50)->nullable();
            $table->string('estadocar', 20)->nullable();
            $table->string('arl', 50)->nullable();
            $table->date('fimpre')->nullable();
            $table->string('concanti', 20)->nullable();
            $table->text('recsug')->nullable();
            $table->string('correoa1', 255)->nullable();
            $table->string('correoa2', 255)->nullable();
            $table->string('correofe', 255)->nullable();
            $table->date('fechacorte')->nullable();
            $table->string('certificau', 10)->nullable();
            $table->string('nocodigo', 10)->nullable();
            $table->string('verimc', 10)->nullable();
            $table->string('uniroc', 10)->nullable();
            $table->string('usu_cre', 50)->nullable();
            $table->dateTime('fec_cre')->nullable();
            $table->string('usu_mod', 50)->nullable();
            $table->dateTime('fec_mod')->nullable();
            $table->string('imphis', 10)->nullable();
            $table->string('impopto', 10)->nullable();
            $table->string('impaudio', 10)->nullable();
            $table->string('impespi', 10)->nullable();
            $table->string('imprexcol', 10)->nullable();
            $table->string('impekG', 10)->nullable();
            $table->string('impevoz', 10)->nullable();
            $table->string('impvfisi', 10)->nullable();
            $table->string('impcvacu', 10)->nullable();
            
            // Índices para búsquedas
            $table->index('nombre');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};