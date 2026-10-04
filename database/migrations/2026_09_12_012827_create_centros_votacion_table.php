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
        Schema::create('centros_votacion', function (Blueprint $table) {
        $table->id();
        $table->char('ubigeo_id', 6); // Vinculado al distrito
        $table->string('nombre', 150);
        $table->string('direccion', 200);
        $table->integer('electores')->default(0);
        $table->timestamps();

        // Relación con tu tabla de ubigeos fijos
        $table->foreign('ubigeo_id')->references('id')->on('ubigeos')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_votacion');
    }
};
