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
        Schema::create('acta_escrutinios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_sufragio_id')->constrained('mesas_sufragio')->onDelete('cascade');
            $table->foreignId('partido_politico_id')->constrained('partidos_politicos')->onDelete('cascade');

            // Clasificación del tipo de acta para separar los escrutinios de la ONPE
            $table->enum('tipo_eleccion', ['GOBERNADOR','CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL']);
            $table->integer('votos_validos')->default(0);
            $table->integer('votos_blancos')->default(0);
            $table->integer('votos_nulos')->default(0);
            $table->boolean('estado_cerrado')->default(false); 
            $table->string('estado_verificacion', 30)->default('CONFORME');

            $table->timestamps();

            // Evita duplicar el mismo tipo de elección para un partido en una sola mesa
            $table->unique(['mesa_sufragio_id', 'partido_politico_id', 'tipo_eleccion'], 'acta_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acta_escrutinios');
    }
};
