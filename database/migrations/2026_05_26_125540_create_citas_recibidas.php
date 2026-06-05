<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitasRecibidas extends Migration
{
    public function up()
    {
        Schema::create('citas_recibidas', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20);
            $table->string('nombre', 255);
            $table->date('fecha');
            $table->text('mision')->nullable();  
            $table->string('nit_empresa', 20);
            $table->string('nombre_empresa', 255);
            $table->string('mision_empresa', 500)->nullable();  
            $table->json('datos_completos')->nullable();
            $table->timestamp('recibido_en')->useCurrent();
            $table->timestamps();
            
         
            $table->string('ruta_resultados', 500)->nullable()->after('mision_empresa');
            $table->boolean('carpeta_copiada')->default(false)->after('ruta_resultados');
            $table->timestamp('fecha_copia')->nullable()->after('carpeta_copiada');
            
       
            $table->index('cedula');
            $table->index('fecha');
            $table->index('nit_empresa');
        });
    }

    public function down()
    {
        Schema::dropIfExists('citas_recibidas');
    }
}