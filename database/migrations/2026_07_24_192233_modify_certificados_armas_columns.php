<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados_armas', function (Blueprint $table) {
            $table->dropColumn('tipo_certificado');
            $table->string('archivo_certificado')->nullable()->change();
        });
    }

    public function down(): void
    {

    }
};