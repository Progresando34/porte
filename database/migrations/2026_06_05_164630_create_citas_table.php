<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->text('fecha')->nullable();
            $table->text('hora')->nullable();
            $table->text('empresa')->nullable();
            $table->text('mision')->nullable();
            $table->text('empfactu')->nullable();
            $table->text('cedula')->nullable();
            $table->text('nombre')->nullable();
            $table->text('cargo')->nullable();
            $table->text('examen')->nullable();
            $table->text('trabaltu')->nullable();
            $table->text('espacios')->nullable();
            $table->text('eosteo')->nullable();
            $table->text('manipula')->nullable();
            $table->text('medico')->nullable();
            $table->text('cardio')->nullable();
            $table->text('audiometri')->nullable();
            $table->text('evoz')->nullable();
            $table->text('optometria')->nullable();
            $table->text('visiometri')->nullable();
            $table->text('espirometr')->nullable();
            $table->text('sicologia')->nullable();
            $table->text('earmas')->nullable();
            $table->text('licencia')->nullable();
            $table->text('rxtorax')->nullable();
            $table->text('rxcolumna')->nullable();
            $table->text('electrocar')->nullable();
            $table->text('psico')->nullable();
            $table->text('observacio')->nullable();
            $table->text('doctor')->nullable();

            $table->text('nmedico')->nullable();
            $table->text('nmanipula')->nullable();
            $table->text('naudiometr')->nullable();
            $table->text('noptometri')->nullable();
            $table->text('nespiromet')->nullable();
            $table->text('nsicologia')->nullable();
            $table->text('nelectroca')->nullable();
            $table->text('nrxcolumna')->nullable();
            $table->text('nrxtorax')->nullable();
            $table->text('npsico')->nullable();
            $table->text('nlaborator')->nullable();
            $table->text('laboratori')->nullable();

            $table->text('vacuna')->nullable();
            $table->text('nvacuna')->nullable();
            $table->text('dvacuna')->nullable();
            $table->text('motriz')->nullable();
            $table->text('nmotriz')->nullable();
            $table->text('rxpies')->nullable();
            $table->text('nrxpies')->nullable();
            $table->text('ecografia')->nullable();
            $table->text('necografia')->nullable();

            $table->text('atencion')->nullable();
            $table->text('educap')->nullable();
            $table->text('cmed')->nullable();
            $table->text('caud')->nullable();
            $table->text('copt')->nullable();
            $table->text('cesp')->nullable();
            $table->text('cpsi')->nullable();
            $table->text('cpse')->nullable();
            $table->text('cele')->nullable();

            $table->text('crxc')->nullable();
            $table->text('crxt')->nullable();
            $table->text('crxp')->nullable();
            $table->text('ceco')->nullable();
            $table->text('cvac')->nullable();
            $table->text('tipodoc')->nullable();

            $table->text('vfisio')->nullable();
            $table->text('cvfi')->nullable();
            $table->text('nvfisiote')->nullable();

            $table->text('curtepra')->nullable();
            $table->text('ncurtepra')->nullable();
            $table->text('cctp')->nullable();

            $table->text('vnutri')->nullable();
            $table->text('nvnutri')->nullable();
            $table->text('cvnu')->nullable();

            $table->text('resmag')->nullable();
            $table->text('crm')->nullable();
            $table->text('nresmag')->nullable();
            $table->text('dresmag')->nullable();

            $table->text('fev')->nullable();
            $table->text('audiocli')->nullable();
            $table->text('manedefe')->nullable();
            $table->text('enfasis')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};