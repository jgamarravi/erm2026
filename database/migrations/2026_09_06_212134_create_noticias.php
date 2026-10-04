<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->string('titular');            // El título de la noticia o crónica
            $table->text('contenido');            // El cuerpo redactado por la locutora
            $table->string('locutora_firma')->nullable();
            $table->string('imagen_path')->nullable(); // La fotografía destacada
            
            // RELACIÓN OPCIONAL: Si la nota se basa en una encuesta, guardamos el ID. Si no, se queda NULL.
            $table->foreignId('encuesta_id')->nullable()->constrained('encuestas')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('noticias');
    }
};
