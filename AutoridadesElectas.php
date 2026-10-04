<?php 
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AutoridadesElectas extends Component
{
    public $tipo_cedula = 'GOBERNADOR'; 
    public $region_electoral_sel = 'LIMA PROVINCIAS'; 
    public $provincia_id = '';
    public $distrito_id = '';

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

    private function obtenerEscañosDHondt($votos_partidos, $vacantes)
    {
        if ($vacantes <= 0 || $votos_partidos->isEmpty()) return [];
        $cocientes = [];
        foreach ($votos_partidos as $p) {
            for ($i = 1; $i <= $vacantes; $i++) {
                $cocientes[] = ['partido_id' => $p->partido_politico_id, 'valor' => $p->total_votos / $i];
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

    public function render()
    {
        $ubigeo_filtro = $this->distrito_id ?: ($this->provincia_id ?: null);
        $ubigeo_base = $ubigeo_filtro ? substr($ubigeo_filtro, 0, 4) : null;

        // 1. Obtener cuadro de votación ordenado (Votos Válidos)
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

        $autoridades_electas = [];

        if ($votos_partidos->isNotEmpty()) {
            
            // A. PROCLAMACIÓN DE CARGOS UNIPERSONALES (Gobernador / Alcaldes Provinciales o Distritales)
            // Se asume mayoría simple para la asignación del líder de la lista
            $partido_ganador_id = $votos_partidos->first()->partido_politico_id;
            
            $lider = DB::table('candidatos')
                ->join('partidos_politicos', 'candidatos.partido_politico_id', '=', 'partidos_politicos.id')
                ->where('candidatos.partido_politico_id', $partido_ganador_id)
                ->where('candidatos.cargo', $this->tipo_cedula)
                ->select('candidatos.dni', 'candidatos.nombres', 'candidatos.apellidos', 'partidos_politicos.nombre as partido', 'candidatos.cargo')
                ->first();

            if ($lider) {
                $autoridades_electas[] = $lider;
            }

            // B. PROCLAMACIÓN PLURIPERSONAL (Consejeros o Regidores Municipales por D'Hondt)
            $id_ubigeo_search = $this->distrito_id ?: ($this->provincia_id ?: DB::table('ubigeos')->where('region_electoral', $this->region_electoral_sel)->value('id'));
            $ubigeo_data = DB::table('ubigeos')->where('id', $id_ubigeo_search)->first();

            $vacantes = 0;
            $cargo_busqueda = '';

            if ($this->tipo_cedula === 'CONSEJERO') {
                $vacantes = $ubigeo_data->consejeros_regionales ?? 0;
                $cargo_busqueda = 'CONSEJERO';
            } elseif (in_array($this->tipo_cedula, ['PROVINCIAL', 'DISTRITAL'])) {
                // Si la cédula es Provincial o Distrital, calculamos sus regidores independientes correspondientes
                $vacantes = $ubigeo_data->total_regidores ?? 0;
                $cargo_busqueda = 'REGIDOR'; // Mapeado contra tu ENUM o string de la tabla candidatos
            }

            if ($vacantes > 0) {
                $reparto = $this->obtenerEscañosDHondt($votos_partidos, $vacantes);

                foreach ($reparto as $partido_id => $cantidad_escaños) {
                    $cuerpo_colegiado = DB::table('candidatos')
                        ->join('partidos_politicos', 'candidatos.partido_politico_id', '=', 'partidos_politicos.id')
                        ->where('candidatos.partido_politico_id', $partido_id)
                        ->where('candidatos.cargo', $cargo_busqueda)
                        // Filtrar por el ubigeo asignado para evitar jalar regidores de otras comunas
                        ->where('candidatos.ubigeo_id', 'like', substr($id_ubigeo_search, 0, 4) . '%') 
                        ->select('candidatos.dni', 'candidatos.nombres', 'candidatos.apellidos', 'partidos_politicos.nombre as partido', DB::raw("'$cargo_busqueda' as cargo"))
                        ->limit($cantidad_escaños)
                        ->get()->toArray();

                    $autoridades_electas = array_merge($autoridades_electas, $cuerpo_colegiado);
                }
            }
        }

        // Obtener descripción geográfica para el acta formal de impresión
        $nombre_jurisdiccion = 'NACIONAL';
        if ($ubigeo_filtro) {
            $nombre_jurisdiccion = DB::table('ubigeos')->where('id', $ubigeo_filtro)->value('nombre') ?? '';
        } elseif ($this->region_electoral_sel) {
            $nombre_jurisdiccion = $this->region_electoral_sel;
        }

        return view('livewire.autoridades-electas', [
            'autoridades' => $autoridades_electas,
            'jurisdiccion' => $nombre_jurisdiccion
        ])->layout('layouts.app');
    }

    private function cargarProvincias() {
        if (!empty($this->region_electoral_sel)) {
            $this->provincias = DB::table('ubigeos')->where('region_electoral', $this->region_electoral_sel)->where('id', 'like', '%00')->where('id', 'not like', '%0000')->get();
        }
    }
}
