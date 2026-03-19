<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('rayosxod', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('cedula');
        $table->date('fecha_rx');
        $table->string('nombre_archivo'); // nombre original (NO cambiar)
        $table->string('ruta'); // ruta FTP
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rayosxod');
    }
};
