<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElectoralTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. POBLAR PARTIDOS POLÍTICOS REALES
        $partidos = [
            ['nombre' => 'ALIANZA PARA EL PROGRESO',
            'siglas'=> 'app'],
            ['nombre' => 'FUERZA POPULAR','siglas'=> 'fp'],
            ['nombre' => 'PERU LIBRE','siglas'=> 'pl'],
            ['nombre' => 'RENOVACION POPULAR','siglas'=> 'rp'],
            ['nombre' => 'PODEMOS PERU','siglas'=> 'pp'],
        ];

        foreach ($partidos as $p) {
            DB::table('partidos_politicos')->updateOrInsert(['nombre' => $p['nombre']], $p);
        }

        // Obtener IDs asignados
        $app_id = DB::table('partidos_politicos')->where('nombre', 'ALIANZA PARA EL PROGRESO')->value('id');
        $fp_id  = DB::table('partidos_politicos')->where('nombre', 'FUERZA POPULAR')->value('id');
        $pl_id  = DB::table('partidos_politicos')->where('nombre', 'PERU LIBRE')->value('id');

        // 2. POBLAR UBIGEOS (BARRANCA, LIMA)
        $ubigeos = [
            ['id' => '140000', 'nombre' => 'LIMA'], // Departamento
            ['id' => '140200', 'nombre' => 'BARRANCA'], // Provincia
            ['id' => '140201', 'nombre' => 'BARRANCA (DISTRITO METROPOLITANO)'],
            ['id' => '140202', 'nombre' => 'PARAMONGA'],
            ['id' => '140203', 'nombre' => 'PATIVILCA'],
            ['id' => '140204', 'nombre' => 'SUPE'],
            ['id' => '140205', 'nombre' => 'SUPE PUERTO'],
        ];

        foreach ($ubigeos as $u) {
            DB::table('ubigeos')->updateOrInsert(['id' => $u['id']], $u);
        }

        // 3. CREAR CENTRO DE VOTACIÓN Y MESA DE PRUEBA (Mesa N° 038472 en Paramonga)
        $centro_id = DB::table('centros_votacion')->insertGetId([
            'nombre' => 'I.E. FISCALIZADO CLORINDA MATTO DE TURNER',
            'ubigeo_id' => '140202', // Paramonga
            'direccion' => 'calle 50',
            'created_at' => now()
        ]);

        DB::table('mesas_sufragio')->updateOrInsert(
            ['numero_mesa' => '038472'],
            [
                'centro_votacion_id' => $centro_id,
                'electores_habiles' => 300,
                'total_votaron' => null,
                'created_at' => now()
            ]
        );

        // 4. CONFIGURAR ESCENARIO DE PRUEBA (Pregunta inicial)
        // Fuerza Popular e Inscribe en GOBERNADOR y CONSEJERO (Región Lima - 14)
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $fp_id, 'ubigeo_id' => '140000', 'tipo_eleccion' => 'GOBERNADOR']);
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $fp_id, 'ubigeo_id' => '140000', 'tipo_eleccion' => 'CONSEJERO']);
        
        // Inscribe Alcalde Provincial en Barranca (1402)
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $fp_id, 'ubigeo_id' => '140200', 'tipo_eleccion' => 'PROVINCIAL']);
        
        // Pero NO inscribe lista distrital en Paramonga (140202). Quedará en "No Postula".

        // Para contrastar, Alianza Para el Progreso SI postula a todo en Paramonga
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $app_id, 'ubigeo_id' => '140000', 'tipo_eleccion' => 'GOBERNADOR']);
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $app_id, 'ubigeo_id' => '140000', 'tipo_eleccion' => 'CONSEJERO']);
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $app_id, 'ubigeo_id' => '140200', 'tipo_eleccion' => 'PROVINCIAL']);
        DB::table('inscripciones_partidos')->updateOrInsert(['partido_politico_id' => $app_id, 'ubigeo_id' => '140202', 'tipo_eleccion' => 'DISTRITAL']);
    }
}
