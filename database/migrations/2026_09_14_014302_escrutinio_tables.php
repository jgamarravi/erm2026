<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Cabecera o Estado del Acta por Mesa
        Schema::create('actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_sufragio_id')->unique()->constrained
            ('mesas_sufragio')->onDelete('cascade');
            // Usuario que registró el acta originalmente
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('region_electoral',100)->default('');
            $table->enum('estado', ['procesada', 'observada', 'impugnada'])->default('procesada');
            // Usuario que cambió el estado en el módulo de observadas
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            // Texto del sustento de la resolución Jurídica
            $table->text('sustento_resolucion')->nullable();
            $table->integer('blancos_gobernador')->default(0);
            $table->integer('nulos_gobernador')->default(0);
            $table->integer('blancos_consejero')->default(0);
            $table->integer('nulos_consejero')->default(0);
            $table->integer('blancos_distrital')->default(0);
            $table->integer('nulos_distrital')->default(0);
            $table->integer('blancos_provincial')->default(0);
            $table->integer('nulos_provincial')->default(0);
            $table->integer('total_votantes_acta')->default(0); // Total de firmas en el padrón
            
            
            
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // 2. Detalle de Votos Válidos por cada Partido en esa Mesa
        Schema::create('acta_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('votos_gobernador')->default(0);
            $table->integer('votos_consejero')->default(0);
            $table->integer('votos_provincial')->default(0);
            $table->integer('votos_distrital')->default(0);
            $table->foreignId('acta_id')->constrained('actas')->onDelete('cascade');
            $table->foreignId('partido_politico_id')->constrained('partidos_politicos')->onDelete('cascade');
            $table->timestamps();

            // Un partido solo puede tener una fila de votos por acta
            $table->unique(['acta_id', 'partido_politico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_detalles');
        Schema::dropIfExists('actas');
    }
};
