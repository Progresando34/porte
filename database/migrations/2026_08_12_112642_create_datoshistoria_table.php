<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datoshistoria', function (Blueprint $table) {
            // ============ ID Y TIMESTAMPS ============
            $table->id();
            $table->timestamps();
            
            // ============ CAMPOS SOLICITADOS ============
            $table->string('TIPODOC', 5)->nullable();
            $table->string('CEDULA', 15)->nullable();
            $table->string('DE', 30)->nullable();
            $table->date('FECHA')->nullable();
            $table->string('EXAMEN', 25)->nullable();
            
            $table->boolean('TRABALTU')->nullable();
            $table->boolean('ESPACIOS')->nullable();
            $table->boolean('EOSTEO')->nullable();
            
            $table->string('ENFASIS', 20)->nullable();
            $table->string('EMPRESA', 15)->nullable();
            $table->string('MISION', 50)->nullable();
            $table->string('NOMBRE', 50)->nullable();
            $table->string('CARGO', 5)->nullable();
            
            $table->string('CVALINEA', 20)->nullable();
            $table->text('ESPIROME')->nullable();
            $table->text('AUDIOME')->nullable();
            $table->text('EVOZ')->nullable();
            $table->text('OPTOMETRA')->nullable();
            $table->text('VISIOMETRA')->nullable();
            $table->text('SICOLOGIA')->nullable();
            $table->text('RAYOSXTO')->nullable();
            $table->text('RAYOSXCV')->nullable();
            $table->text('EKG')->nullable();
            $table->text('PSICOSEN')->nullable();
            $table->text('CMOTRIZ')->nullable();
            $table->text('LABORATO')->nullable();
            $table->text('LABORATOC')->nullable();

            // ============ ÍNDICES ============
            $table->index('CEDULA');
            $table->index('NOMBRE');
            $table->index('FECHA');
            $table->index('DE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datoshistoria');
    }
};