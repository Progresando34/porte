
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados_armas', function (Blueprint $table) {
            $table->boolean('resultado_apto')->nullable()->after('id');
            $table->string('direccion_ips')->nullable()->after('resultado_apto');
            $table->string('sede_ips')->nullable()->after('direccion_ips');
        });
    }

    public function down(): void
    {
        Schema::table('certificados_armas', function (Blueprint $table) {
            $table->dropColumn(['resultado_apto', 'direccion_ips', 'sede_ips']);
        });
    }
};