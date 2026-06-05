<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consolidados', function (Blueprint $table) {
            $table->id();
            $table->string('empresa_nit', 20)->nullable();
            $table->date('fecha_documento')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->string('nombre_archivo', 255)->nullable();
            $table->integer('tamanio')->nullable();
            $table->string('tipo_archivo', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
     
            $table->index('empresa_nit');
            $table->index('fecha_documento');
            $table->foreign('empresa_nit')->references('nit')->on('empresas')->onDelete('set null');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('consolidados');
    }
};