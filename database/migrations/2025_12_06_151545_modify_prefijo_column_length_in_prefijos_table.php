<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::table('prefijos', function (Blueprint $table) {
    $table->string('prefijo', 20)->change();
});
    }

    public function down(): void
    {
        Schema::table('prefijos', function (Blueprint $table) {
            // Revertir a 10 caracteres
            $table->string('prefijo', 10)->unique()->change();
        });
    }
};