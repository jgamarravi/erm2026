<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique(); // Ej: 'mensaje_bienvenida', 'lema_radio'
            $table->text('valor')->nullable();  // El texto libre que guardará la locutora
            $table->timestamps();
        });

        // Insertamos el mensaje por defecto inicial de Huacho para que el sistema no empiece vacío
        DB::table('configuraciones')->insert([
            'clave' => 'mensaje_bienvenida',
            'valor' => 'Conectando a toda la provincia con el ritmo y la voz que nos identifica. Participa en nuestras encuestas de opinión pública y haz escuchar tu voz en la plataforma oficial de Stereo 92 FM.',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('configuraciones');
    }
};
