<?php
// database/migrations/xxxx_add_ruta_resultados_to_citas_recibidas.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRutaResultadosToCitasRecibidas extends Migration
{
    public function up()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->string('ruta_resultados', 500)->nullable()->after('mision_empresa');
            $table->boolean('carpeta_copiada')->default(false)->after('ruta_resultados');
            $table->timestamp('fecha_copia')->nullable()->after('carpeta_copiada');
        });
    }

    public function down()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->dropColumn(['ruta_resultados', 'carpeta_copiada', 'fecha_copia']);
        });
    }
}