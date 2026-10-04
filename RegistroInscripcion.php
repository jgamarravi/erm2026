<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Admin\PartidoPolitico;
use Illuminate\Support\Facades\DB;

class RegistroInscripcion extends Component
{
    public $partido_id;
    public $tipo_eleccion = 'GOBERNADOR';

    // Selectores de Ubigeo
    public $activar_distritos_dependientes = false;
    public $departamento_id = '';
    public $provincia_id = '';
    public $distrito_id = '';

    // Colecciones para los elementos de interfaz
    public $partidos = [];
    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];

    public function mount()
{
    $this->partidos = PartidoPolitico::all();
    
    // Agrupamos por la región electoral real guardada por tu seeder
    $this->departamentos = DB::table('ubigeos')
        ->select('region_electoral', DB::raw('MIN(id) as id'))
        ->whereNotNull('region_electoral')
        ->groupBy('region_electoral')
        ->get();
}



    // Monitorea cambios en el Departamento para cargar Provincias
    public function updatedDepartamentoId($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos']);

        if (!empty($value)) {
            $dep_code = substr($value, 0, 2);
            // Provincias: Mismo prefijo de dpto, terminan en '00' y no son el dpto base
            $this->provincias = DB::table('ubigeos')
                ->where('id', 'like', $dep_code . '%00')
                ->where('id', '!=', $value)
                ->get();
        }
    }

    // Monitorea cambios en la Provincia para cargar Distritos
    public function updatedProvinciaId($value)
    {
        $this->reset(['distrito_id', 'distritos']);

        if (!empty($value)) {
            $prov_code = substr($value, 0, 4);
            // Distritos: Mismo prefijo de prov y no terminan en '00'
            $this->distritos = DB::table('ubigeos')
                ->where('id', 'like', $prov_code . '%')
                ->where('id', 'not like', '%00')
                ->get();
        }
    }

    // Resetear selectores si cambia el tipo de elección para evitar inconsistencias
    public function updatedTipoEleccion()
    {
        $this->reset(['departamento_id', 'provincia_id', 'distrito_id', 'provincias', 'distritos']);
    }

public function guardar()
{
    $this->validate([
        'partido_id' => 'required',
        'tipo_eleccion' => 'required',
        'departamento_id' => 'required',
        'provincia_id' => 'required_if:tipo_eleccion,PROVINCIAL,DISTRITAL',
        'distrito_id' => 'required_if:tipo_eleccion,DISTRITAL',
    ]);

    // 1. Candado para distritos capitales
    if ($this->tipo_eleccion === 'DISTRITAL') {
        $ubigeo_distrito = DB::table('ubigeos')->where('id', $this->distrito_id)->first();
        if ($ubigeo_distrito && (int)$ubigeo_distrito->es_capital === 1) {
            $this->addError('distrito_id', 'Regla JNE: No se puede inscribir una lista DISTRITAL en un distrito Capital de Provincia.');
            return;
        }
    }

    DB::transaction(function () {
        // Extraemos los 2 primeros dígitos del departamento seleccionado
        $codigo_dep = substr($this->departamento_id, 0, 2);

        // 2. CANDADO HISTÓRICO DE LIMA PROVINCIAS (RESOLUCIÓN JNE)
        if (($this->tipo_eleccion === 'GOBERNADOR' || $this->tipo_eleccion === 'CONSEJERO') && $codigo_dep === '14') {
            
            // Buscamos matemáticamente las 9 provincias de Lima Provincias
            // Criterio: Empiezan con 15, son cabeceras provinciales (%00) y EXCLUYEN a Lima Metropolitana (150100)
            $provincias_validas = DB::table('ubigeos')
                ->where('id', 'like', '14%00')
                ->where('id', '!=', '140100') // Exclusión estricta de Lima Metropolitana
                ->where('id', '!=', '140000') // Exclusión de la cabecera departamental
                ->pluck('id');

            // Registramos la lista en cada una de las 9 provincias legítimas de Lima Provincias
            foreach ($provincias_validas as $ubigeo_provincial) {
                DB::table('inscripciones_partidos')->updateOrInsert([
                    'partido_politico_id' => $this->partido_id,
                    'ubigeo_id' => $ubigeo_provincial, // Guardará: 150200, 150300, 150400... hasta 151000
                    'tipo_eleccion' => $this->tipo_eleccion
                ]);
            }

        } else {
            // 3. COMPORTAMIENTO REGULAR PARA EL RESTO DE DEPARTAMENTOS Y CARGOS MUNICIPALES
            if ($this->tipo_eleccion === 'GOBERNADOR' || $this->tipo_eleccion === 'CONSEJERO') {
                $ubigeo_final = str_pad(substr($this->departamento_id, 0, 2), 6, "0", STR_PAD_RIGHT);
            } elseif ($this->tipo_eleccion === 'PROVINCIAL') {
                $ubigeo_final = str_pad(substr($this->provincia_id, 0, 4), 6, "0", STR_PAD_RIGHT);
            } else {
                $ubigeo_final = str_pad($this->distrito_id, 6, "0", STR_PAD_RIGHT);
            }

            DB::table('inscripciones_partidos')->updateOrInsert([
                'partido_politico_id' => $this->partido_id,
                'ubigeo_id' => $ubigeo_final,
                'tipo_eleccion' => $this->tipo_eleccion
            ]);
        }

        // 4. Automatización masiva de Alcaldía Provincial hacia Distritos dependientes
        if ($this->tipo_eleccion === 'PROVINCIAL' && $this->activar_distritos_dependientes) {
            $prov_prefix = substr($this->provincia_id, 0, 4);

            $distritos_hijos = DB::table('ubigeos')
                ->where('id', 'like', $prov_prefix . '%')
                ->where('id', 'not like', '%00') // Excluye cabecera
                ->where(function($q) {
                    $q->where('es_capital', 0)->orWhereNull('es_capital'); // Excluye distrito capital
                })
                ->pluck('id');

            foreach ($distritos_hijos as $ubigeo_distrito) {
                DB::table('inscripciones_partidos')->updateOrInsert([
                    'partido_politico_id' => $this->partido_id,
                    'ubigeo_id' => $ubigeo_distrito,
                    'tipo_eleccion' => 'DISTRITAL'
                ]);
            }
        }
    });

    session()->flash('message', 'Inscripción autorizada con éxito para la circunscripción real.');
    $this->reset(['departamento_id', 'provincia_id', 'distrito_id', 'provincias', 'distritos', 'activar_distritos_dependientes']);
}


    public function render()
    {
        return view('livewire.registro-inscripcion')->layout('layouts.app');
    }
}
