<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMisionNullableInCitasRecibidas extends Migration
{
    public function up()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->text('mision')->nullable()->change();
            $table->string('mision_empresa', 500)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('citas_recibidas', function (Blueprint $table) {
            $table->text('mision')->nullable(false)->change();
            $table->string('mision_empresa', 500)->nullable(false)->change();
        });
    }
}