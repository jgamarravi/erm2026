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
        Schema::create('partidos_politicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('siglas', 20)->unique();
            $table->integer('orden_cedula')->default(0);
            $table->string('logo_url')->nullable(); // Para la ruta de la insignia en Laragon
            $table->boolean('activo')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos_politicos');
    }
};
