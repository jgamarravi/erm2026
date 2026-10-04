<?php
// app/Livewire/ProclamacionAutoridades.php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProclamacionAutoridades extends Component
{
    // Filtros en cascada por Ubigeo
    public $tipo_cedula = 'GOBERNADOR'; 
    public $region_electoral_sel = 'LIMA PROVINCIAS'; 
    public $provincia_id = '';
    public $distrito_id = '';

    // Colecciones para renderizar los selectores
    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];

    public function mount()
    {
        $this->departamentos = DB::table('ubigeos')
            ->whereNotNull('region_electoral')
            ->distinct()
            ->pluck('region_electoral')
            ->toArray();
            
        $this->cargarProvincias();
    }

    public function updatedRegionElectoralSel($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos']);
        if (!empty($value)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $value)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->get();
        }
    }

    public function updatedProvinciaId($value)
    {
        $this->reset(['distrito_id', 'distritos']);
        if (!empty($value)) {
            $prov_code = substr($value, 0, 4);
            $this->distritos = DB::table('ubigeos')
                ->where('id', 'like', $prov_code . '%')
                ->where('id', 'not like', '%00')
                ->where(function($q) {
                    if ($this->tipo_cedula === 'DISTRITAL') {
                        $q->where('es_capital', 0)->orWhereNull('es_capital');
                    }
                })
                ->get();
        }
    }

    public function updatedTipoCedula($value)
    {
        if ($value === 'GOBERNADOR') {
            $this->reset(['provincia_id', 'distrito_id']);
        } elseif (in_array($value, ['CONSEJERO', 'PROVINCIAL'])) {
            $this->reset(['distrito_id']);
        } elseif ($value === 'DISTRITAL' && !empty($this->provincia_id)) {
            $this->reset(['distrito_id']);
            $this->cargarDistritos($this->provincia_id);
        }
    }


    private function obtenerRepartoDHondt($votos_partidos, $vacantes)
    {
        if ($vacantes <= 0 || $votos_partidos->isEmpty()) return [];
        
        $cocientes = [];
        foreach ($votos_partidos as $p) {
            for ($i = 1; $i <= $vacantes; $i++) {
                $cocientes[] = [
                    'partido_id' => $p->partido_politico_id,
                    'valor' => $p->total_votos / $i
                ];
            }
        }
        
        usort($cocientes, function ($a, $b) { return $b['valor'] <=> $a['valor']; });
        $ganadores = array_slice($cocientes, 0, $vacantes);
        
        $reparto = [];
        foreach ($ganadores as $g) {
            $reparto[$g['partido_id']] = ($reparto[$g['partido_id']] ?? 0) + 1;
        }
        return $reparto;
    }

        // ... código anterior de cargarProvincias() ...

    private function cargarProvincias()
    {
        if (!empty($this->region_electoral_sel)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $this->region_electoral_sel)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->get();
        }
    }

    // MÉTODO REQUERIDO AGREGADO PARA ELIMINAR EL ERROR
    private function cargarDistritos($provincia_id)
    {
        if (empty($provincia_id)) {
            return;
        }

        $prov_code = substr($provincia_id, 0, 4);

        $query = DB::table('ubigeos')
            ->where('id', 'like', $prov_code . '%')
            ->where('id', 'not like', '%00'); // Trae puros distritos reales

        // REGLA JNE: Si la proclamación es Distrital, excluimos por completo al distrito capital
        if ($this->tipo_cedula === 'DISTRITAL') {
            $query->where(function($q) {
                $q->where('es_capital', 0)
                  ->orWhereNull('es_capital');
            });
        }

        $this->distritos = $query->get();
    }

    public function render()
    {
        // 1. Resolver el Ubigeo de control perimetral JNE
        $ubigeo_filtro = $this->distrito_id ?: ($this->provincia_id ?: null);
        $ubigeo_base = $ubigeo_filtro ? substr($ubigeo_filtro, 0, 4) : null;

        $bloqueo_alcaldia = (
            ($this->tipo_cedula === 'PROVINCIAL' && empty($this->provincia_id)) ||
            ($this->tipo_cedula === 'DISTRITAL' && empty($this->distrito_id))
        );

        // 2. Consulta consolidada de votación
        $votos_partidos = collect();
        if (!$bloqueo_alcaldia) {
            $votos_partidos = DB::table('votos_mesas')
                ->join('mesas_sufragio', 'votos_mesas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
                ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
                ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
                ->where('votos_mesas.tipo_eleccion', $this->tipo_cedula)
                ->where('votos_mesas.voto_especial', 'REGULAR')
                ->when($this->distrito_id, function($q) { $q->where('centros_votacion.ubigeo_id', $this->distrito_id); })
                ->when(!$this->distrito_id && $this->provincia_id, function($q) use ($ubigeo_base) { $q->where('centros_votacion.ubigeo_id', 'like', $ubigeo_base . '%'); })
                ->when(!$this->distrito_id && !$this->provincia_id, function($q) { $q->where('ubigeos.region_electoral', $this->region_electoral_sel); })
                ->select('votos_mesas.partido_politico_id', DB::raw('SUM(votos_mesas.cantidad_votos) as total_votos'))
                ->groupBy('votos_mesas.partido_politico_id')
                ->orderByDesc('total_votos')
                ->get();
        }

        $autoridades_proclamadas = [];

        if ($votos_partidos->isNotEmpty()) {
            
            // ESCENARIO UNIPERSONAL: Mayoría simple para Gobernador o Alcaldes (Líderes de Lista)
            if (in_array($this->tipo_cedula, ['GOBERNADOR', 'PROVINCIAL', 'DISTRITAL'])) {
                $partido_ganador_id = $votos_partidos->first()->partido_politico_id;

                $lider = DB::table('candidatos')
                    ->join('partidos_politicos', 'candidatos.partido_politico_id', '=', 'partidos_politicos.id')
                    ->where('candidatos.partido_politico_id', $partido_ganador_id)
                    ->where('candidatos.cargo', $this->tipo_cedula)
                    ->select('candidatos.dni', 'candidatos.nombres', 'candidatos.apellidos', 'partidos_politicos.nombre as partido', 'candidatos.cargo')
                    ->first();

                if ($lider) $autoridades_proclamadas[] = $lider;
            }

            // ESCENARIO PLURIPERSONAL: Aplicación de la Cifra Repartidora D'Hondt (Regidores / Consejeros)
            $id_ubigeo_search = $this->distrito_id ?: ($this->provincia_id ?: DB::table('ubigeos')->where('region_electoral', $this->region_electoral_sel)->value('id'));
            
            // Determinamos el distrito capital para jalar las vacantes correctas provinciales de tu base de datos
            if ($this->tipo_cedula === 'PROVINCIAL') {
                $ubigeo_data = DB::table('ubigeos')->where('id', 'like', substr($this->provincia_id, 0, 4) . '%')->where('es_capital', 1)->first();
            } else {
                $ubigeo_data = DB::table('ubigeos')->where('id', $id_ubigeo_search)->first();
            }

            $vacantes = 0;
            $cargo_busqueda = '';

            if ($this->tipo_cedula === 'CONSEJERO') {
                $vacantes = $ubigeo_data->consejeros_regionales ?? 0;
                $cargo_busqueda = 'CONSEJERO';
            } elseif (in_array($this->tipo_cedula, ['PROVINCIAL', 'DISTRITAL'])) {
                $vacantes = $ubigeo_data->total_regidores ?? 0;
                $cargo_busqueda = 'REGIDOR'; // Mapeado contra tu estructura ENUM de candidatos
            }

            if ($vacantes > 0) {
                $reparto = $this->obtenerRepartoDHondt($votos_partidos, $vacantes);

                foreach ($reparto as $partido_id => $cantidad_escaños) {
                    // Extrae los candidatos de forma nominal ordenados secuencialmente según el DNI o ID interno
                    $cuerpo_colegiado = DB::table('candidatos')
                        ->join('partidos_politicos', 'candidatos.partido_politico_id', '=', 'partidos_politicos.id')
                        ->where('candidatos.partido_politico_id', $partido_id)
                        ->where('candidatos.cargo', $cargo_busqueda)
                        ->where('candidatos.ubigeo_id', 'like', substr($id_ubigeo_search, 0, 4) . '%')
                        ->select('candidatos.dni', 'candidatos.nombres', 'candidatos.apellidos', 'partidos_politicos.nombre as partido', DB::raw("'$cargo_busqueda' as cargo"))
                        ->orderBy('candidatos.id') // Orden de prelación de la lista inscrita
                        ->limit($cantidad_escaños)
                        ->get()->toArray();

                    $autoridades_proclamadas = array_merge($autoridades_proclamadas, $cuerpo_colegiado);
                }
            }
        }


        return view('livewire.proclamacion-autoridades', [
            'autoridades' => $autoridades_proclamadas,
            'bloqueado' => $bloqueo_alcaldia
        ])->layout('layouts.app');
    }
}
