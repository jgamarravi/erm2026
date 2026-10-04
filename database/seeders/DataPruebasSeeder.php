<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataPruebasSeeder extends Seeder
{
    public function run()
    {
        // 1. LIMPIEZA DE DATA PREVIA PARA EVITAR DUPLICADOS DE LLAVES FORÁNEAS
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('votos_mesas')->truncate();
        DB::table('mesas_sufragio')->truncate();
        DB::table('centros_votacion')->truncate();
        DB::table('partidos_politicos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. INSERCIÓN DE PARTIDOS POLÍTICOS INSCRITOS Y REALES
        $partidos = [
            [
                'id' => 1,
                'nombre' => 'ACCIÓN POPULAR',
                'siglas' => 'AP',
                'logo_url' => 'logos/A1790082820.png',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'nombre' => 'PARTIDO APRISTA PERUANO',
                'siglas' => 'APRA',
                'logo_url' => 'logos/apra.png',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3,
                'nombre' => 'FUERZA POPULAR',
                'siglas' => 'FP',
                'logo_url' => 'logos/fuerza_popular.png',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4,
                'nombre' => 'ALIANZA PARA EL PROGRESO',
                'siglas' => 'APP',
                'logo_url' => 'logos/app.png',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 5,
                'nombre' => 'RENOVACIÓN POPULAR',
                'siglas' => 'R',
                'logo_url' => 'logos/renovacion.png',
                'created_at' => now(), 'updated_at' => now()
            ],
        ];

        DB::table('partidos_politicos')->insert($partidos);

        // 3. INSERCIÓN DE LOCALES DE VOTACIÓN EN LA PROVINCIA DE BARRANCA
        // Se asocian directamente a los Ubigeos CHAR(6) legítimos de Barranca (150201) y Paramonga (150202)
        $locales = [
            // Distrito Capital: Barranca
            [
                'id' => 1,
                'ubigeo_id' => '150201', // Barranca Distrito (Capital)
                'nombre' => 'I.E. GUILLERMO BILLINGHURST',
                'direccion' => 'Av. Grau N° 450',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2,
                'ubigeo_id' => '150201',
                'nombre' => 'COLISEO MUNICIPAL DE BARRANCA',
                'direccion' => 'Jr. Zavala N° 210',
                'created_at' => now(), 'updated_at' => now()
            ],
            // Distrito Periférico: Paramonga
            [
                'id' => 3,
                'ubigeo_id' => '150202', // Paramonga Distrito
                'nombre' => 'I.E. NUESTRA SEÑORA DE LAS MERCEDES',
                'direccion' => 'Av. Ferrocarril S/N',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4,
                'ubigeo_id' => '150202',
                'nombre' => 'COMPLEJO DEPORTIVO PAPELERA PARAMONGA',
                'direccion' => 'Calle Lima N° 600',
                'created_at' => now(), 'updated_at' => now()
            ]
        ];

        DB::table('centros_votacion')->insert($locales);

        // 4. GENERACIÓN DE MESAS DE SUFRAGIO ASOCIADAS A LOS LOCALES
        // Cada mesa arranca con estado 'SIN_DIGITAR' y un padrón de electores hábiles aleatorio entre 200 y 300
        $mesas = [];
        $contador_mesa = 150201; // Correlativo de 6 dígitos simulando el número ONPE

        foreach ($locales as $local) {
            // Asignamos un lote de 4 mesas a cada centro de votación
            for ($i = 1; $i <= 4; $i++) {
                $mesas[] = [
                    'id' => count($mesas) + 1,
                    'centro_votacion_id' => $local['id'],
                    'numero_mesa' => (string) $contador_mesa,
                    'electores_habiles' => rand(240, 300),
                    'total_votaron' => null, // Vacío hasta que el operario digite el acta
                    'estado_acta' => 'SIN_DIGITAR', // Estado ENUM inicial de control
                    'created_at' => now(), 'updated_at' => now()
                ];
                $contador_mesa++;
            }
        }

        DB::table('mesas_sufragio')->insert($mesas);
    }
}
