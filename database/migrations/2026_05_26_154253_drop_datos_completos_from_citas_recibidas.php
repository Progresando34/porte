<?php
// database/migrations/xxxx_drop_datos_completos_from_citas_recibidas.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDatosCompletosFromCitasRecibidas extends Migration
{
    public function up()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->dropColumn('datos_completos');
        });
    }

    public function down()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->json('datos_completos')->nullable()->after('mision_empresa');
        });
    }
}