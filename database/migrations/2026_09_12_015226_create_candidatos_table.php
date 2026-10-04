<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidatos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('partido_politico_id')->constrained('partidos_politicos')->onDelete('cascade');
        $table->string('dni', 8)->unique();
        $table->string('nombres', 100);
        $table->string('apellidos', 100);
        
        // Clasificación del cargo según la Ley de Elecciones
        $table->enum('cargo', ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL', 'REGIDOR']);
        
        // El UBIGEO se asocia según el nivel del cargo:
        // - GOBERNADOR/CONSEJERO: Código del departamento o provincia electoral (ej: 140000 o 140500)
        // - ALCALDE/REGIDOR: Código específico del distrito o provincia municipal
        $table->char('ubigeo_id', 6); 
        
        $table->timestamps();

        $table->foreign('ubigeo_id')->references('id')->on('ubigeos')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};
