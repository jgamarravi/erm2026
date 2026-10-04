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
        Schema::create('mesas_sufragio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_votacion_id')->constrained('centros_votacion')->onDelete('cascade');
            $table->char('numero_mesa', 6)->unique(); // Código de 6 dígitos de la ONPE (ej: 035241)
            $table->integer('electores_habiles')->default(300); // Promedio estándar por mesa
            $table->integer('total_votaron')->nullable();
            $table->enum('estado_acta', ['SIN_DIGITAR', 'COMPUTADA', 'OBSERVADA'])->default('SIN_DIGITAR');
            $table->string('tipo_observacion', 100)->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas_sufragio');
    }
};
