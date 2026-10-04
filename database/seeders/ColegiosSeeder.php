<?php

namespace Database\Seeders;

use App\Models\Admin\Ubigeo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class ColegiosSeeder extends Seeder
{
    public function run(): void
    {
        // Ruta del archivo CSV (debe estar en storage/app/locales.csv)
        $rutaArchivo = database_path('seeders/locales.csv');
        //$path = database_path('seeders/ubigeo.json');

        if (!file_exists($rutaArchivo)) {
            $this->command->error("El archivo locales.csv no existe en storage/app/");
            return;
        }

        $this->command->info('Iniciando la importación de centros de votación...');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            // 2. Limpiar la tabla (TRUNCATE es más rápido y reinicia contadores)
            DB::table('centros_votacion')->truncate();

            // 3. VOLVER A ACTIVAR la verificación por seguridad
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        // Usamos LazyCollection para leer línea por línea de manera eficiente
        LazyCollection::make(function () use ($rutaArchivo) {
            $handle = fopen($rutaArchivo, 'r');

            // Omitir la primera línea si contiene las cabeceras
            $headers = fgetcsv($handle, 0, ';');

            while (($fila = fgetcsv($handle, 0, ';')) !== false) {
                yield $fila;
            }

            fclose($handle);
        })
            ->chunk(500) // Insertar en bloques de 500 para optimizar el rendimiento
            ->each(function ($lineas) {
                $datosAInsertar = [];

                foreach ($lineas as $fila) {
                    // Asegurar que la fila tenga columnas suficientes
                    if (count($fila) < 15) {
                        continue;
                    }

                    // Mapeo exacto basado en las columnas de tu CSV (separado por punto y coma ';')
                    // 6: UBIGEO, 11: NOMBRE DEL LOCAL, 12: DIRECCIÓN DEL LOCAL, 14: ELECTORES
                    $distrito = $fila[9];
                    $ubigeo = Ubigeo::where('nombre', $distrito)
                        ->where(substr('id',-2),'!=','00')
                        ->first();
                    if(!$ubigeo){
                        continue;

                    }

                    $datosAInsertar[] = [
                        'ubigeo_id' => $ubigeo->id,
                        'nombre' => mb_convert_encoding($fila[11], 'UTF-8', 'UTF-8'), // Evita problemas de tildes
                        'direccion' => mb_convert_encoding($fila[12], 'UTF-8', 'UTF-8'),
                        'electores' => (int) filter_var($fila[14], FILTER_SANITIZE_NUMBER_INT), // Limpia si trae comas o puntos
                    ];
                }

                // Inserción masiva en la tabla centros_votacion
                DB::table('centros_votacion')->insert($datosAInsertar);
            });

        $this->command->info('¡Importación completada con éxito!');
    }
}
