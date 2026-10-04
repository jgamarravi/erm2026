<?php

namespace Database\Seeders;

use App\Models\Admin\Ubigeo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definir el mapeo oficial de códigos a Región Electoral
        $mapaRegiones = [
            '01' => 'REGIÓN AMAZONAS',
            '02' => 'REGIÓN ANCASH',
            '03' => 'REGIÓN APURIMAC',
            '04' => 'REGIÓN AREQUIPA',
            '05' => 'REGIÓN AYACUCHO',
            '06' => 'REGIÓN CAJAMARCA',
            '07' => 'REGIÓN CONSTITUCIONAL DEL CALLAO',
            '08' => 'REGIÓN CUSCO',
            '09' => 'REGIÓN HUANCAVELICA',
            '10' => 'REGIÓN HUANUCO',
            '11' => 'REGIÓN ICA',
            '12' => 'REGIÓN JUNIN',
            '13' => 'REGIÓN LA LIBERTAD',
            '14' => 'REGIÓN LAMBAYEQUE',
            '15' => 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)', // Sede Huacho para elecciones regionales
            '16' => 'REGIÓN LORETO',
            '17' => 'REGIÓN MADRE DE DIOS',
            '18' => 'REGIÓN MOQUEGUA',
            '19' => 'REGIÓN PASCO',
            '20' => 'REGIÓN PIURA',
            '21' => 'REGIÓN PUNO',
            '22' => 'REGIÓN SAN MARTIN',
            '23' => 'REGIÓN TACNA',
            '24' => 'REGIÓN TUMBES',
            '25' => 'REGIÓN UCAYALI',
        ];

        // 2. Cargar y decodificar el archivo JSON guardado
        $path = database_path('seeders/ubigeo.json');
        if (!file_exists($path)) {
            $this->command->error("No se encontró el archivo ubigeo.json en database/seeders/");
            return;
        }

        $rawJson = file_get_contents($path);
        $data = json_decode($rawJson, true);

        $insertData = [];
        $ahora = now();

        foreach ($data as $codigo => $item) {
            $id = str_pad($item['id'], 6, '0', STR_PAD_RIGHT);
            $prefijoDep = substr($id, 0, 2);
            $prefijoProv = substr($id, 0, 4);

            $regionElectoral = $mapaRegiones[$prefijoDep] ?? null;

            if ($prefijoDep === '14') {
                if ($id === '140000') {
                    $regionElectoral = 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)';
                } elseif ($prefijoProv === '1401') {
                    $regionElectoral = 'REGIÓN LIMA (METROPOLITANA)';
                } else {
                    $regionElectoral = 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)';
                }
            }

            $esCapital = false;
            if (!str_ends_with($id, '0000') && !str_ends_with($id, '00') && str_ends_with($id, '01')) {
                $esCapital = true;
            }

            // REGLAS PARA SIMULAR EL NÚMERO DE REGIDORES SEGÚN JURISDICCIÓN:
            $totalRegidores = 5; // Mínimo legal por ley

            if ($id === '140101') {
                $totalRegidores = 39; // Cercado de Lima / Municipalidad Metropolitana de Lima
            } elseif ($prefijoProv === '1401' && !str_ends_with($id, '00')) {
                // Distritos grandes de Lima Metropolitana (ej: Miraflores, San Borja, etc.)
                $totalRegidores = 9;
            } elseif ($esCapital) {
                // Capitales provinciales de departamentos (ej: Trujillo, San Vicente, Arequipa)
                $totalRegidores = 11;
            }

            $consejeros = 0;
            if (str_ends_with($id, '00') && !str_ends_with($id, '0000')) {
                // Es una provincia válida. Por defecto asignamos el mínimo legal.
                $consejeros = 1;
                if (in_array($id, ['140100'])) { // Provincias populosas elegidas
                    $consejeros = 0;
                }
                // Regla de variación poblacional para comicios (Ejemplos):
                if (in_array($id, ['140200', '140600', '140500', '141000', '120300', '120600'])) { // Provincias populosas elegidas
                    $consejeros = 2;
                }
                if (in_array($id, ['130700', '130900'])) { // Provincias populosas elegidas
                    $consejeros = 3;
                }
                if (in_array($id, ['040100', '130100'])) { // Provincias populosas elegidas
                    $consejeros = 6;
                }
            }

            $insertData[] = [
                'id' => $id,
                'nombre' => mb_strtoupper($item['name'], 'UTF-8'),
                'region_electoral' => $regionElectoral,
                'es_capital' => $esCapital,
                'total_regidores' => $totalRegidores, // Inyección del valor
                'created_at' => $ahora,
                'updated_at' => $ahora,
                'consejeros_regionales' => $consejeros,
            ];
        }

        // 3. Insertar en bloques (chunks) para no saturar la memoria de MySQL en Laragon
        Schema::disableForeignKeyConstraints();

        DB::table('ubigeos')->truncate(); // Limpia la tabla por seguridad

        Schema::enableForeignKeyConstraints();

        foreach (array_chunk($insertData, 200) as $chunk) {
            DB::table('ubigeos')->insert($chunk);
        }
        // --- 2. SEGUNDO PASO: ALGORITMO DINÁMICO DE CONSEJEROS REGIONALES (MINÍMO 7) ---
        $this->command->info("Calculando y asignando curules para los Consejos Regionales...");

        // Obtenemos la lista de los 25 departamentos base (excluyendo Lima Provincias/Metropolitana virtuales)
        $departamentos = DB::table('ubigeos')->where('id', 'LIKE', '%0000')->get();

        foreach ($departamentos as $dep) {
            $prefijoDep = substr($dep->id, 0, 2);
            $consejerosRegion = 0;
            // Contamos cuántas provincias reales tiene este departamento en la base de datos
            $provincias = DB::table('ubigeos')
                ->where('id', 'LIKE', $prefijoDep . '%00')
                ->where('id', '!=', $dep->id)
                ->orderBy('id', 'asc') // El orden ayuda a priorizar la capital (01)
                ->get();

            $totalProvincias = $provincias->count();

            if ($totalProvincias == 0)
                continue;

            // Regla Base: Cada provincia inicia con 1 consejero obligatorio por derecho de suelo
            $escañosAsignados = [];
            foreach ($provincias as $prov) {
                $escañosAsignados[$prov->id] = 1;
                $consejerosRegion += $prov->consejeros_regionales;
            }

            $ubi = Ubigeo::find($dep->id);
            $ubi->update(['consejeros_regionales' => $consejerosRegion <7 ? 7 : $consejerosRegion]);

            $consejerosTotalesAsignados = $totalProvincias;

            // REGLA CRÍTICA DEL JNE: Si no alcanza el mínimo de 7, añadimos los escaños faltantes
            if ($consejerosTotalesAsignados < 7) {
                $escañosFaltantes = 7 - $consejerosTotalesAsignados;

                // Repartimos los consejeros sobrantes priorizando las provincias principales (Capital, etc.)
                // En un entorno avanzado esto usaría el padrón electoral, aquí lo distribuimos equitativamente en bucle
                while ($escañosFaltantes > 0) {
                    foreach ($provincias as $prov) {
                        if ($escañosFaltantes > 0) {
                            $escañosAsignados[$prov->id]++;
                            $escañosFaltantes--;
                        }
                    }
                }
            }
        }

        $this->command->info("¡Éxito! Todos los Consejos Regionales cuentan con su cuota legal (Mínimo 7 consejeros).");

    }
}
