<?php
// app/Livewire/MonitoreoActas.php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class MonitoreoActas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap'; // Para mantener el estilo de tus tablas
    // Filtros en cascada por Ubigeo
    public $region_electoral_sel = 'LIMA PROVINCIAS';
    public $provincia_id = '';
    public $distrito_id = '';

    // Colecciones para los Selectores
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

    // HOOK INTERACTIVO ELECTORAL (Livewire v3): Se dispara automáticamente al cambiar la región
    public function updatedRegionElectoralSel($value)
    {
        // REGLA ONPE: Limpiamos los selectores inferiores y sus listas para evitar datos huérfanos
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos']);

        // Si el usuario seleccionó una región válida, cargamos únicamente sus provincias asociadas
        if (!empty($value)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $value)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000') // Excluimos la cabecera departamental
                ->get();
        }
    }


    // Hook reactivo cuando cambia la provincia
    public function updatedProvinciaId($value)
    {
        // Limpiamos el distrito anterior y su lista desplegable
        $this->reset(['distrito_id', 'distritos']);

        if (!empty($value)) {
            $prov_code = substr($value, 0, 4);
            $this->distritos = DB::table('ubigeos')
                ->where('id', 'like', $prov_code . '%')
                ->where('id', 'not like', '%00') // Filtra para traer puros distritos reales
                ->get();
        }
    }


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

    public function render()
    {
        // 1. Determinar la demarcación perimetral exacta
        $ubigeo_filtro = null;
        if ($this->distrito_id) {
            $ubigeo_filtro = $this->distrito_id;
        } elseif ($this->provincia_id) {
            $ubigeo_filtro = substr($this->provincia_id, 0, 4); // Prefijo de 4 dígitos (Ej: 1502)
        }

        // 2. RECUENTO DE ESTADOS CON JOIN ESTRICTO ELECTORAL (REPARADO)
        $conteos = DB::table('mesas_sufragio')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id') // JOIN Mandatorio
            ->when($ubigeo_filtro, function ($query) use ($ubigeo_filtro) {
                $query->where('centros_votacion.ubigeo_id', 'like', $ubigeo_filtro . '%');
            })
            ->when(!$ubigeo_filtro && $this->region_electoral_sel, function ($query) {
                // Sincroniza de forma coercitiva con el texto del selector ("LIMA PROVINCIAS", "AMAZONAS", etc.)
                $query->where('ubigeos.region_electoral', $this->region_electoral_sel);
            })
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN estado_acta = 'SIN_DIGITAR' THEN 1 ELSE 0 END) as sin_digitar"),
                DB::raw("SUM(CASE WHEN estado_acta = 'COMPUTADA' THEN 1 ELSE 0 END) as computadas"),
                DB::raw("SUM(CASE WHEN estado_acta = 'OBSERVADA' THEN 1 ELSE 0 END) as observadas")
            )->first();

        $total_mesas = $conteos->total ?? 0;

        $pct_sin_digitar = $total_mesas > 0 ? ($conteos->sin_digitar / $total_mesas) * 100 : 0;
        $pct_computadas = $total_mesas > 0 ? ($conteos->computadas / $total_mesas) * 100 : 0;
        $pct_observadas = $total_mesas > 0 ? ($conteos->observadas / $total_mesas) * 100 : 0;

        // 3. CONSULTA DE DESGLOSE DE LOCALES PAGINADA (CORREGIDO)
        if ($total_mesas > 0) {
            $locales_avance = DB::table('mesas_sufragio')
                ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
                ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
                ->when($ubigeo_filtro, function ($query) use ($ubigeo_filtro) {
                    $query->where('centros_votacion.ubigeo_id', 'like', $ubigeo_filtro . '%');
                })
                ->when(!$ubigeo_filtro && $this->region_electoral_sel, function ($query) {
                    $query->where('ubigeos.region_electoral', $this->region_electoral_sel);
                })
                ->select(
                    'centros_votacion.nombre as local_nombre',
                    'ubigeos.nombre as distrito_nombre',
                    DB::raw("COUNT(*) as total_mesas"),
                    DB::raw("SUM(CASE WHEN estado_acta = 'COMPUTADA' THEN 1 ELSE 0 END) as mesas_computadas"),
                    DB::raw("SUM(CASE WHEN estado_acta = 'OBSERVADA' THEN 1 ELSE 0 END) as mesas_observadas")
                )
                ->groupBy('centros_votacion.id', 'centros_votacion.nombre', 'ubigeos.nombre')
                ->orderBy('ubigeos.nombre')
                ->paginate(15);
        } else {
            // EN CLAVE: Retorna una instancia de paginación vacía legítima para evitar el crash del Blade
            $locales_avance = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }
        // RESOLVEMOS EL NOMBRE DE LA CIRCUNSCRIPCIÓN PARA EL ENCABEZADO JNE
        $nombre_jurisdiccion = $this->region_electoral_sel;
        if ($this->distrito_id) {
            $nombre_jurisdiccion = DB::table('ubigeos')->where('id', $this->distrito_id)->value('nombre');
        } elseif ($this->provincia_id) {
            $nombre_jurisdiccion = DB::table('ubigeos')->where('id', $this->provincia_id)->value('nombre');
        }

        return view('livewire.monitoreo-actas', [
            'total' => $total_mesas,
            'sin_digitar' => $conteos->sin_digitar ?? 0,
            'computadas' => $conteos->computadas ?? 0,
            'observadas' => $conteos->observadas ?? 0,
            'pct_sin_digitar' => $pct_sin_digitar,
            'pct_computadas' => $pct_computadas,
            'pct_observadas' => $pct_observadas,
            'locales' => $locales_avance,
            'jurisdiccion_nombre' => $nombre_jurisdiccion // <-- ENVIAMOS ESTA VARIABLE NUEVA
        ])->layout('layouts.app');
    }
}
