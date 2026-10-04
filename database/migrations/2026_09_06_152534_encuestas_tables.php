<?php 
// database/migrations/2026_09_06_000005_create_encuestas_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('pregunta');
            $table->string('mensaje_bienvenida')->nullable();
            $table->boolean('activa')->default(true);
            $table->text('nota_prensa')->nullable();
            $table->string('imagen_path')->nullable();
            $table->timestamps();
        });

        Schema::create('encuesta_opciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->string('opcion');
            $table->integer('votos')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('encuesta_opciones');
        Schema::dropIfExists('encuestas');
    }
};
