<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\ActaDetalle;
use App\Models\Admin\Ubigeo;
use Illuminate\Support\Facades\DB;

class ReporteConsolidado extends Component
{
    // Filtros de Cascada Territoriales
    public $tipo_eleccion = 'gobernador';
    public $region_id = '';
    public $provincia_id = '';
    public $distrito_id = '';

    // Colecciones reactivas secundarias
    public $provincias = [];
    public $distritos = [];

    public $escanos_repartir = 9;

    public function mount()
    {
        $this->provincias = [];
        $this->distritos = [];
    }

    public function updatedTipoEleccion()
    {
        // Al cambiar de elección, reseteamos provincia y distrito de forma segura
        $this->reset(['provincia_id', 'distrito_id', 'distritos']);

        // Carga por defecto del número de escaños según cargo
        if ($this->tipo_eleccion === 'gobernador') {
            $this->escanos_repartir = 0;
        } elseif ($this->tipo_eleccion === 'consejero') {
            $this->escanos_repartir = 1; // Base de consejeros provinciales
        } else {
            $this->escanos_repartir = 9; // Base municipal
        }

        // Si ya hay región seleccionada, forzamos recarga de provincias bajo el nuevo contexto
        if ($this->region_id) {
            $this->cargarProvincias($this->region_id);
        }
    }

    public function updatedRegionId($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'distritos']);
        if ($value) {
            $this->cargarProvincias($value);
        }
    }

    private function cargarProvincias($regionName)
    {
        if ($regionName === 'Lima Metropolitana') {
            $this->provincias = Ubigeo::where('id', 'like', '1401%')
                ->where('id', 'like', '%00')
                ->select(DB::raw('SUBSTRING(id, 1, 4) as id'), DB::raw('"Lima" as nombre'))
                ->groupBy(DB::raw('SUBSTRING(id, 1, 4)'))
                ->get()->toArray();
        } else {
            $this->provincias = Ubigeo::where('region_electoral', $regionName)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->select(DB::raw('SUBSTRING(id, 1, 4) as id'), 'nombre')
                ->groupBy(DB::raw('SUBSTRING(id, 1, 4)'), 'nombre')
                ->orderBy('nombre', 'asc')
                ->get()->map(function ($item) {
                    $item->nombre = trim(str_replace(' - ', '', $item->nombre));
                    return $item;
                })->toArray();
        }
    }

    public function updatedProvinciaId($value)
    {
        $this->distrito_id = '';

        if (!$value) {
            $this->distritos = [];
            return;
        }

        // Cargamos los distritos filtrando por la provincia seleccionada (4 caracteres iniciales)
        $query = Ubigeo::where('id', 'like', $value . '%')
            ->where('id', 'not like', '%00');

        // REQUERIMIENTO EXTRA: Si es Provincial o Distrital, ocultamos las capitales del select
        if (in_array($this->tipo_eleccion, ['provincial', 'distrital'])) {
            $query->where('es_capital', '!=', 1);
        }

        $this->distritos = $query->orderBy('nombre', 'asc')->get()->toArray();

        // Auto-configuración de escaños desde la base de datos usando el ubigeo capital de la provincia
        $capitalProvincia = Ubigeo::where('id', 'like', $value . '%')->where('es_capital', 1)->first();
        if ($capitalProvincia) {
            if ($this->tipo_eleccion === 'provincial') {
                $this->escanos_repartir = $capitalProvincia->total_regidores ?? 9;
            } elseif ($this->tipo_eleccion === 'consejero') {
                $this->escanos_repartir = $capitalProvincia->consejeros_regionales ?? 1;
            }
        }
    }

    public function render()
{
    $votosPartidos = collect();
    $totalValidos = 0;
    $repartoDhondt = [];
    $esPluripersonal = in_array($this->tipo_eleccion, ['consejero', 'provincial', 'distrital']);
    
    // Reglas estrictas de validación de cascada para activar el panel
    $filtroValido = false;
    if ($this->tipo_eleccion === 'gobernador' && $this->region_id) {
        $filtroValido = true;
    } elseif ($this->tipo_eleccion === 'consejero' && $this->region_id && $this->provincia_id) {
        $filtroValido = true;
    } elseif ($this->tipo_eleccion === 'provincial' && $this->region_id && $this->provincia_id) {
        $filtroValido = true; 
    } elseif ($this->tipo_eleccion === 'distrital' && $this->region_id && $this->provincia_id && $this->distrito_id) {
        $filtroValido = true;
    }

    if ($filtroValido) {
        // Aislamos variables para el contexto de la consulta
        $regionFiltro = $this->region_id;
        $provinciaFiltro = $this->provincia_id;
        $distritoFiltro = $this->distrito_id;
        $tipoEleccionActual = $this->tipo_eleccion;

        // 1. OBTENER PARTIDOS POLÍTICOS FILTRADOS POR ÁMBITO DE INSCRIPCIÓN
        $queryPartidos = PartidoPolitico::query();

        // REGLA DE CORRECCIÓN: Solo filtramos partidos inscritos si es elección municipal (Provincial o Distrital)
        if (in_array($tipoEleccionActual, ['provincial', 'distrital'])) {
            $queryPartidos->whereHas('ubigeos', function($q) use ($tipoEleccionActual, $provinciaFiltro, $distritoFiltro) {
                if ($tipoEleccionActual === 'distrital' && $distritoFiltro) {
                    $q->where('ubigeos.id', $distritoFiltro);
                } elseif ($tipoEleccionActual === 'provincial' && $provinciaFiltro) {
                    // Si es provincial, el partido compite si está inscrito en cualquier distrito de esa provincia
                    $q->where('ubigeos.id', 'like', $provinciaFiltro . '%');
                }
            });
        }

        $votosPartidos = $queryPartidos->orderBy('orden_cedula', 'asc')->get()->map(function($partido) use ($regionFiltro, $provinciaFiltro, $distritoFiltro, $tipoEleccionActual) {
            
            // Query base de sumatoria de actas procesadas
            $queryVotos = ActaDetalle::where('partido_politico_id', $partido->id)
                ->join('actas', 'acta_detalles.acta_id', '=', 'actas.id')
                ->where('actas.estado', 'procesada') // Solo actas válidas cerradas
                ->join('mesas_sufragio', 'actas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
                ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
                ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id');

            // Filtrado Geográfico por Ubigeo de forma segura
            if ($regionFiltro) {
                $queryVotos->where('ubigeos.region_electoral', $regionFiltro);
            }

            if ($tipoEleccionActual === 'provincial' && $provinciaFiltro) {
                $queryVotos->where('ubigeos.id', 'like', $provinciaFiltro . '%');
            } else {
                if ($provinciaFiltro) {
                    $queryVotos->where('ubigeos.id', 'like', $provinciaFiltro . '%');
                }
                if ($distritoFiltro) {
                    $queryVotos->where('ubigeos.id', $distritoFiltro);
                }
            }

            // Columna dinámica a sumar
            $columna = 'votos_' . $tipoEleccionActual;
            $partido->votos_totales = (int)$queryVotos->sum('acta_detalles.' . $columna);

            return $partido;
        });

        // Calculamos el universo total de votos útiles válidos
        $totalValidos = $votosPartidos->sum('votos_totales');

        // 2. PROCESAMIENTO DEL REPARTO PLURIPERSONAL D'HONDT
        if ($esPluripersonal && $totalValidos > 0 && $this->escanos_repartir > 0) {
            $cocientes = collect();

            foreach ($votosPartidos as $partido) {
                if ($partido->votos_totales > 0) {
                    for ($i = 1; $i <= $this->escanos_repartir; $i++) {
                        $cocientes->push([
                            'partido_id' => $partido->id,
                            'nombre'     => $partido->nombre,
                            'logo_url'   => $partido->logo_url,
                            'valor'      => $partido->votos_totales / $i
                        ]);
                    }
                }
            }

            // Ordenamiento por cociente con criterio de desempate técnico estable por ID
            $ganadores = $cocientes->sortByDesc(function($item) {
                return [$item['valor'], $item['partido_id']];
            })->take($this->escanos_repartir);

            foreach ($votosPartidos as $partido) {
                $escanos = $ganadores->where('partido_id', $partido->id)->count();
                if ($escanos > 0) {
                    $repartoDhondt[] = [
                        'nombre'   => $partido->nombre,
                        'logo_url' => $partido->logo_url,
                        'votos'    => $partido->votos_totales,
                        'escanos'  => $escanos
                    ];
                }
            }
            $repartoDhondt = collect($repartoDhondt)->sortByDesc('escanos')->all();
        }
    }

    // Mantenimiento de la persistencia del combo de regiones
    $regionesConsultadas = Ubigeo::select('region_electoral')
        ->whereNotNull('region_electoral')
        ->where(DB::raw('TRIM(region_electoral)'), '!=', '')
        ->groupBy('region_electoral')
        ->orderBy('region_electoral', 'asc')
        ->get();

    return view('livewire.admin.electoral.reporte-consolidado', [
        'votosPartidos'   => $votosPartidos,
        'totalValidos'    => $totalValidos ?: 1, 
        'repartoDhondt'    => $repartoDhondt,
        'esPluripersonal' => $esPluripersonal,
        'filtroValido'    => $filtroValido,
        'regiones'        => $regionesConsultadas
    ])->layout('layouts.app');
}

}
