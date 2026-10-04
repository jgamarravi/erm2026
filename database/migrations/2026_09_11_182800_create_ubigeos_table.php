<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeos', function (Blueprint $table) {
        $table->char('id', 6)->primary();
        $table->string('nombre', 100);
        $table->string('region_electoral', 100)->nullable();
        $table->boolean('es_capital')->default(false);
        $table->integer('total_regidores')->default(5); // Nueva columna para regidores
        $table->integer('consejeros_regionales')->default(0); // Añadir campo
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};
