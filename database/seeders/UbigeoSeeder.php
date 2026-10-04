<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Mapeamos todos los departamentos en memoria para asignarlos eficientemente
        // Buscamos los registros que terminan en '0000' (ej: 010000 -> AMAZONAS)
        $departamentos = DB::table('ubigeos')
            ->where('id', 'LIKE', '%0000')
            ->get()
            ->pluck('nombre', 'id')
            ->mapWithKeys(function ($nombre, $id) {
                // Guardamos solo los 2 primeros dígitos como clave (ej: '01' => 'AMAZONAS')
                return [substr($id, 0, 2) => $nombre];
            })
            ->toArray();

        // 2. Iniciamos la transacción para que el proceso tome pocos segundos
        DB::beginTransaction();

        // 3. Recorremos la tabla en bloques de 100 registros
        DB::table('ubigeos')->chunkById(100, function ($registros) use ($departamentos) {
            foreach ($registros as $registro) {
                
                $consejeros_regionales = 0;
                $total_regidores = 0;
                
                // Extraemos los prefijos del ID CHAR(6)
                $codigoDepartamento = substr($registro->id, 0, 2); // Primeros 2 dígitos (ej: '15')
                $codigoProvincia    = substr($registro->id, 0, 4); // Primeros 4 dígitos (ej: '1401')

                // 4. LÓGICA PARA LA REGION ELECTORAL
                $regionElectoral = '';

                if ($codigoProvincia === '1401') {
                    // Excepción: Lima Metropolitana
                    $regionElectoral = 'LIMA METROPOLITANA';
                } elseif ($codigoDepartamento === '14') {
                    // Excepción: Resto de códigos de Lima (1502 al 1510)
                    $regionElectoral = 'LIMA PROVINCIAS';
                } else {
                    // Caso general: Tomamos el nombre del departamento correspondiente
                    $regionElectoral = $departamentos[$codigoDepartamento] ?? 'SIN REGION';
                }

                // 5. LÓGICA PARA AUTORIDADES (NIVEL GEOPOLÍTICO)
                if (str_ends_with($registro->id, '0000')) {
                    
                    // CASO 1: DEPARTAMENTO
                    $consejeros_regionales = 0;
                    $total_regidores = 0;

                } elseif (str_ends_with($registro->id, '00')) {
                    
                    // CASO 2: PROVINCIA
                    $consejeros_regionales = 1;
                    $total_regidores = 0; 

                } else {
                    
                    // CASO 3: DISTRITO
                    $consejeros_regionales = 0;
                    $total_regidores = 5; 
                    
                }

                // 6. Actualizamos el registro fila por fila
                DB::table('ubigeos')
                    ->where('id', $registro->id)
                    ->update([
                        'consejeros_regionales'       => $consejeros_regionales,
                        'total_regidores'        => $total_regidores,
                        'region_electoral' => $regionElectoral, // Asegúrate de tener esta columna en tu tabla
                        'updated_at'       => now(),
                    ]);
            }
        });

        // 5. Confirmamos todos los cambios en bloque
        DB::commit();

        $this->command->info('¡Seeder ejecutado! Autoridades y regiones electorales actualizadas.');
    }
}
