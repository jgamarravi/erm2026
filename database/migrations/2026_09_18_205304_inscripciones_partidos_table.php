<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inscripciones_partidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partido_politico_id')->constrained('partidos_politicos');
            $table->string('ubigeo_id', 6); // 2 dígitos (Región), 4 (Provincia), 6 (Distrito)
            $table->enum('tipo_eleccion', ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL', 'DISTRITAL']);
            $table->timestamps();

            $table->unique(['partido_politico_id', 'ubigeo_id', 'tipo_eleccion'], 'partido_ubigeo_tipo_unique');
        });

        Schema::create('votos_mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_sufragio_id')->constrained('mesas_sufragio');
            $table->foreignId('partido_politico_id')->nullable();
            $table->enum('tipo_eleccion', ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL', 'DISTRITAL']);
            $table->enum('voto_especial', ['REGULAR', 'BLANCO', 'NULO']);
            $table->integer('cantidad_votos')->default(0);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones_partidos');
        Schema::dropIfExists('votos_mesas');
    }
};
