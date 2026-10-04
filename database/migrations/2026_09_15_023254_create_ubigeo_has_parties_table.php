<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeo_has_parties', function (Blueprint $table) {
            $table->id();
            
            // Llaves foráneas con tipos de datos correctos
            $table->char('ubigeo_id', 6);
            $table->unsignedBigInteger('partido_politico_id');
            
            $table->timestamps();

            // Índices y restricciones de integridad cascada
            $table->foreign('ubigeo_id')->references('id')->on('ubigeos')->onDelete('cascade');
            $table->foreign('partido_politico_id')->references('id')->on('partidos_politicos')->onDelete('cascade');
            
            // Clave única compuesta para evitar que un partido se asigne dos veces al mismo distrito
            $table->unique(['ubigeo_id', 'partido_politico_id'], 'ubigeo_partido_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeo_has_parties');
    }
};
