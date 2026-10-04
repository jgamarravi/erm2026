<?php

namespace Database\Seeders;

use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\Ubigeo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbigeoHasPartiesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla antes de sembrar para evitar duplicados
        DB::table('ubigeo_has_parties')->truncate();

        $partidos = PartidoPolitico::all();
        // Cargamos únicamente los distritos (excluyendo cabeceras regionales/provinciales que terminan en 00)
        $distritos = Ubigeo::where('id', 'not like', '%00')->get();

        if ($partidos->isEmpty() || $distritos->isEmpty()) {
            $this->command->warn('Aviso: Asegúrese de poblar primero las tablas partidos_politicos y ubigeos antes de correr este Seeder.');
            return;
        }

        $dataToInsert = [];

        foreach ($distritos as $distrito) {
            // Determinamos aleatoriamente cuántos partidos compiten en este distrito específico (ej. entre 3 y 6 partidos)
            $cantidadPartidos = rand(3, min(6, $partidos->count()));
            
            // Tomamos una muestra aleatoria de partidos para esta jurisdicción
            $partidosSeleccionados = $partidos->random($cantidadPartidos);

            foreach ($partidosSeleccionados as $partido) {
                $dataToInsert[] = [
                    'ubigeo_id' => $distrito->id,
                    'partido_politico_id' => $partido->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insertamos en bloques (chunks) de 500 registros para optimizar el rendimiento y memoria en MySQL
            if (count($dataToInsert) >= 500) {
                DB::table('ubigeo_has_parties')->insert($dataToInsert);
                $dataToInsert = []; // Vaciamos el bloque
            }
        }

        // Insertar los registros remanentes si existen
        if (count($dataToInsert) > 0) {
            DB::table('ubigeo_has_parties')->insert($dataToInsert);
        }

        $this->command->info('¡Seeder completado con éxito! Se mapearon los partidos habilitados por distrito.');
    }
}
