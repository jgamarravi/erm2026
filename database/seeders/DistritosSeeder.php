<?php

namespace Database\Seeders;

use App\Models\Admin\Ubigeo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistritosSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/distritos.csv'); // Asegúrate de que apunte a tu archivo

        if (($handle = fopen($filePath, 'r')) !== false) {

            // Omitir cabecera si la tiene. Si no tiene, puedes borrar o comentar la siguiente línea:
            // $header = fgetcsv($handle, 1000, ';'); 

            // NOTA: Cambiamos el tercer parámetro a ';' para que detecte bien las columnas
            // Vaciar la tabla antes de insertar nuevos datos, asi como tambien los foreign key constraints si es necesario

            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            // 2. Limpiar la tabla (TRUNCATE es más rápido y reinicia contadores)
            DB::table('ubigeos')->truncate();

            // 3. VOLVER A ACTIVAR la verificación por seguridad
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {

                // Ignorar líneas vacías o incompletas (necesitamos al menos hasta el índice 9)
                if (empty($data) || count($data) < 10) {
                    continue;
                }
                $idUbigeo = trim($data[6]);
                $d = substr($idUbigeo, 0, 2) . '0000'; // Departamento
                $dtxt = trim($data[7]);
                $p = substr($idUbigeo, 0, 4) . '00'; // Provincia
                $ptxt = trim($data[8]);

                // Extraemos el nombre del ubigeo (ej: 'LA PECA')
                $nombreUbigeo = trim($data[9]);

                // Tu lógica para calcular $escapital (puedes adaptarla según tu criterio)
                $escapital = substr($idUbigeo, 4, 2) === '01' ? 1 : 0; // Ejemplo: si los dos últimos dígitos son '01', es capital

                // Inserción en la base de datos
                $search = Ubigeo::find($d);
                if (!$search) {
                    DB::table('ubigeos')->insert([
                        'id' => $d,
                        'nombre' => $dtxt,
                        'es_capital' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $search = Ubigeo::find($p);
                if (!$search) {
                    DB::table('ubigeos')->insert([
                        'id' => $p,
                        'nombre' => $ptxt,
                        'es_capital' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $search = Ubigeo::find(substr($idUbigeo, 0, 6));
                if (!$search) {
                    DB::table('ubigeos')->insert([
                        'id' => substr($idUbigeo, 0, 6),
                        'nombre' => $nombreUbigeo,
                        'es_capital' => $escapital,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            fclose($handle);
        }
    }
}


