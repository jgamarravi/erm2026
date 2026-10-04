<?php
namespace App\Livewire;

use App\Models\Admin\MesaSufragio;
use App\Models\VotoMesa;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ResultadosDashboard extends Component
{
    public $tipo_cedula = 'GOBERNADOR';
    public $region_electoral_sel = 'LIMA PROVINCIAS';
    public $provincia_id = '';
    public $distrito_id = '';
    public $departamento_id = '';

    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];
    public $escaños_disponibles = 0;
    public $totalMesas = 0;
    public $avanceActas = [];
    public $mesasFaltan = 0;
    public $votosValidosData = [];
    public $totalEmitidosGlobal = [];
    public $totalValidosGlobal = [];

    public function mount()
    {
        // 1. Extraer los nombres únicos de la columna nativa region_electoral (Seeder)
        $this->departamentos = DB::table('ubigeos')
            ->whereNotNull('region_electoral')
            ->distinct()
            ->pluck('region_electoral')
            ->toArray();

        // 2. Ejecutar la carga inicial por defecto
        $this->cargarProvincias();
        $this->actualizarLimiteEscaños();

        $this->totalMesas = DB::table('mesas_sufragio')
            ->count();

        $this->avanceActas = DB::table('mesas_sufragio')
            ->where('estado_acta', 'COMPUTADA')
            ->count();

        $this->mesasFaltan = DB::table('mesas_sufragio')
            ->where('estado_acta', 'SIN DIGITAR')
            ->count();
        $tipos = ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL'];

        $totalMesas = MesaSufragio::count();


        foreach ($tipos as $t) {
            // ... (Mantén tu conteo de mesas procesadas anterior)
            $mesasProcesadas = VotoMesa::where('tipo_eleccion', $t)->distinct('mesa_sufragio_id')
                ->where('voto_especial', 'REGULAR')
                ->count('mesa_sufragio_id');
            $porcProcesadas = $totalMesas > 0 ? round(($mesasProcesadas / $totalMesas) * 100, 2) : 0;
            $porcPendientes = round(100 - $porcProcesadas, 2);
            $avanceActas[$t] = ['procesadas' => $porcProcesadas, 'pendientes' => $porcPendientes, 'conteo' => $mesasProcesadas];

            // Consolidar Votos por Partido
            $votosPartidos = DB::table('acta_escrutinios')
                ->join('partidos_politicos', 'acta_escrutinios.partido_politico_id', '=', 'partidos_politicos.id')
                ->where('acta_escrutinios.tipo_eleccion', $t)
                ->where('acta_escrutinios.estado_cerrado', true)
                ->select('partidos_politicos.siglas', 'partidos_politicos.nombre','partidos_politicos.logo_url', DB::raw('SUM(acta_escrutinios.votos_validos) as total_votos'))
                ->groupBy('partidos_politicos.siglas', 'partidos_politicos.nombre')
                ->get();

            $universoValidos = $votosPartidos->sum('total_votos');
            $totalValidosGlobal[$t] = $universoValidos;

            // Recuento de Especiales
            $votosEspeciales = DB::table('acta_escrutinios')->where('tipo_eleccion', $t)->where('estado_cerrado', true)
                ->select(DB::raw('SUM(votos_blancos) as blancos'), DB::raw('SUM(votos_nulos) as nulos'))->first();

            $blancos = $votosEspeciales ? (int) $votosEspeciales->blancos : 0;
            $nulos = $votosEspeciales ? (int) $votosEspeciales->nulos : 0;
            $totalEmitidosGlobal[$t] = $universoValidos + $blancos + $nulos;

            $labels = [];
            $porcentajes = [];
            $tablaFilas = [];
            foreach ($votosPartidos as $vp) {
                $labels[] = $vp->siglas;
                $porc = $universoValidos > 0 ? round(($vp->total_votos / $universoValidos) * 100, 2) : 0;
                $porcentajes[] = $porc;

                // Estructura para la tabla responsiva inferior
                $tablaFilas[] = [
                    'tipo' => 'PARTIDO',
                    'siglas' => $vp->siglas,
                    'nombre' => $vp->nombre,
                    'votos' => (int) $vp->total_votos,
                    'porcentaje' => $porc
                ];
            }

            // Inyectamos las filas de control especial al final de la matriz
            $tablaFilas[] = ['tipo' => 'BLANCO', 'siglas' => 'BLANCOS', 'nombre' => 'VOTOS EN BLANCO', 'votos' => $blancos, 'porcentaje' => 0];
            $tablaFilas[] = ['tipo' => 'NULO', 'siglas' => 'NULOS', 'nombre' => 'VOTOS NULOS / IMPUGNADOS', 'votos' => $nulos, 'porcentaje' => 0];

            $votosValidosData[$t] = [
                'labels' => $labels,
                'datasets' => $porcentajes,
                'tabla' => $tablaFilas // Pasamos la matriz a JavaScript
            ];
        }


    }

    public function updatedRegionElectoralSel($value)
    {
        // Limpiamos los selectores inferiores para evitar datos huérfanos
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos']);

        // Ejecutamos la consulta en cascada basada en el nuevo texto seleccionado
        if (!empty($value)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $value)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000') // Excluimos cabeceras departamentales
                ->get();
        }

        $this->actualizarLimiteEscaños();
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


    // Ganchos reactivos para actualizar los escaños al cambiar cualquier filtro
    public function updatedDepartamentoId($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos']);
        if (!empty($value)) {
            $dep_code = substr($value, 0, 2);
            $this->provincias = DB::table('ubigeos')
                ->where('id', 'like', $dep_code . '%00')
                ->where('id', '!=', $value)
                ->get();
        }
        $this->actualizarLimiteEscaños();
    }

    public function updatedProvinciaId($value)
    {
        $this->reset(['distrito_id', 'distritos']);
        $this->cargarDistritos($value);
        $this->actualizarLimiteEscaños();
    }



    public function updatedDistritoId()
    {
        $this->actualizarLimiteEscaños();
    }

    public function updatedTipoCedula($value)
    {
        if ($value === 'GOBERNADOR') {
            $this->reset(['provincia_id', 'distrito_id']);
        } elseif (in_array($value, ['CONSEJERO', 'PROVINCIAL'])) {
            $this->reset(['distrito_id']);
        } elseif ($value === 'DISTRITAL' && !empty($this->provincia_id)) {
            // Si cambia a Distrital y ya hay una provincia elegida, refrescamos los distritos aplicando el filtro de capital
            $this->reset(['distrito_id']);
            $this->cargarDistritos($this->provincia_id);
        }

        $this->actualizarLimiteEscaños();
    }


    private function cargarDistritos($provincia_id)
    {
        if (empty($provincia_id)) {
            return;
        }

        $prov_code = substr($provincia_id, 0, 4);

        $query = DB::table('ubigeos')
            ->where('id', 'like', $prov_code . '%')
            ->where('id', 'not like', '%00'); // Filtra para traer solo registros distritales

        // REGLA JNE: Si es elección distrital, se excluye el distrito capital del menú desplegable
        if ($this->tipo_cedula === 'DISTRITAL') {
            $query->where(function ($q) {
                $q->where('es_capital', 0)
                    ->orWhereNull('es_capital');
            });
        }

        $this->distritos = $query->get();
    }



    public function calcularDHondt($votos_partidos)
    {
        if ($this->escaños_disponibles <= 0 || $votos_partidos->isEmpty()) {
            return [];
        }

        $cocientes = [];
        // Matriz de divisiones sucesivas (Votos / 1, Votos / 2...)
        foreach ($votos_partidos as $p) {
            for ($i = 1; $i <= $this->escaños_disponibles; $i++) {
                $cocientes[] = [
                    'partido' => $p->partido,
                    'valor' => $p->total_votos / $i
                ];
            }
        }

        // Ordenar cocientes descendentemente
        usort($cocientes, function ($a, $b) {
            return $b['valor'] <=> $a['valor'];
        });

        // Tomar los ganadores según las vacantes de la BD
        $cocientes_ganadores = array_slice($cocientes, 0, $this->escaños_disponibles);

        $escaños_por_partido = [];
        foreach ($votos_partidos as $p) {
            $escaños_por_partido[$p->partido] = 0;
        }

        foreach ($cocientes_ganadores as $cg) {
            $escaños_por_partido[$cg['partido']]++;
        }

        return $escaños_por_partido;
    }

    // app/Livewire/ResultadosDashboard.php

public function render()
{
    // 1. DETERMINACIÓN DEL LÍMITE DE VACANTES (MACRO / MICRO)
    $this->escaños_disponibles = 0;

    if ($this->tipo_cedula === 'GOBERNADOR') {
        $this->escaños_disponibles = 1;
    } elseif ($this->tipo_cedula === 'CONSEJERO') {
        if (!empty($this->provincia_id)) {
            $ubigeo_prov_cabecera = str_pad(substr($this->provincia_id, 0, 4), 6, "0", STR_PAD_RIGHT);
            $this->escaños_disponibles = (int) DB::table('ubigeos')->where('id', $ubigeo_prov_cabecera)->value('consejeros_regionales') ?? 0;
        } else {
            $this->escaños_disponibles = (int) DB::table('ubigeos')
                ->where('region_electoral', $this->region_electoral_sel)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->sum('consejeros_regionales');
        }
    } elseif ($this->tipo_cedula === 'PROVINCIAL') {
        if (!empty($this->provincia_id)) {
            $prefijo_provincia = substr($this->provincia_id, 0, 4);
            $this->escaños_disponibles = (int) DB::table('ubigeos')->where('id', 'like', $prefijo_provincia . '%')->where('es_capital', 1)->value('total_regidores') ?? 0; 
        }
    } elseif ($this->tipo_cedula === 'DISTRITAL') {
        if (!empty($this->distrito_id)) {
            $this->escaños_disponibles = (int) DB::table('ubigeos')->where('id', $this->distrito_id)->value('total_regidores') ?? 0;
        }
    }

    // 2. CONTROL DE BLOQUEOS INTERACTIVOS (SÓLO PARA ALCALDías)
    $bloqueo_alcaldia_sin_ubigeo = (
        ($this->tipo_cedula === 'PROVINCIAL' && empty($this->provincia_id)) ||
        ($this->tipo_cedula === 'DISTRITAL' && empty($this->distrito_id))
    );

    // 3. LOGICA ESPECIAL PARA CONSEJEROS REGIONALES GENERAL (TODAS LAS PROVINCIAS COEXISTIENDO)
    $es_vista_macro_consejeros = ($this->tipo_cedula === 'CONSEJERO' && empty($this->provincia_id));
    $consejeros_consolidados = [];

    if ($es_vista_macro_consejeros) {
        $provincias_region = DB::table('ubigeos')
            ->where('region_electoral', $this->region_electoral_sel)
            ->where('id', 'like', '%00')
            ->where('id', 'not like', '%0000')
            ->get();

        foreach ($provincias_region as $prov) {
            $prefijo_prov = substr($prov->id, 0, 4);
            $vacantes_prov = (int)$prov->consejeros_regionales;

            // 1. Extraemos TODOS los votos de la provincia de forma regular para calcular D'Hondt con precisión real
            $votos_prov_completos = DB::table('votos_mesas')
                ->join('partidos_politicos', 'votos_mesas.partido_politico_id', '=', 'partidos_politicos.id')
                ->join('mesas_sufragio', 'votos_mesas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
                ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
                ->where('votos_mesas.tipo_eleccion', 'CONSEJERO')
                ->where('votos_mesas.voto_especial', 'REGULAR')
                ->where('centros_votacion.ubigeo_id', 'like', $prefijo_prov . '%')
                ->select('partidos_politicos.nombre as partido','partidos_politicos.logo_url', DB::raw('SUM(votos_mesas.cantidad_votos) as total_votos'))
                ->groupBy('partidos_politicos.nombre')
                ->orderByDesc('total_votos')
                ->get();

            // Calcular el total de votos válidos acumulados en esta provincia
            $suma_total_provincia = $votos_prov_completos->sum('total_votos');

            // 2. EJECUTAMOS ALGORITMO D'HONDT SOBRE LA DATA COMPLETA (Garantiza veracidad jurídica)
            $reparto_prov = [];
            if ($vacantes_prov > 0 && $votos_prov_completos->isNotEmpty()) {
                $cocientes = [];
                foreach ($votos_prov_completos as $p) {
                    for ($i = 1; $i <= $vacantes_prov; $i++) {
                        $cocientes[] = ['partido' => $p->partido, 'valor' => $p->total_votos / $i];
                    }
                }
                usort($cocientes, function ($a, $b) { return $b['valor'] <=> $a['valor']; });
                $ganadores = array_slice($cocientes, 0, $vacantes_prov);
                foreach ($votos_prov_completos as $p) { $reparto_prov[$p->partido] = 0; }
                foreach ($ganadores as $cg) { $reparto_prov[$cg['partido']]++; }
            }

            // 3. FILTRO ESTÉTICO: Agrupar en Top 3 + Otros para cuidar la UI
            $votos_prov_filtrados = collect();
            
            if ($votos_prov_completos->count() > 3) {
                // Tomamos los 3 partidos con más votación
                $top_3 = $votos_prov_completos->take(3);
                
                // Agrupamos el resto (del puesto 4 en adelante)
                $resto = $votos_prov_completos->skip(3);
                $votos_otros = $resto->sum('total_votos');

                foreach ($top_3 as $item) {
                    $item->porcentaje_provincial = $suma_total_provincia > 0 ? ($item->total_votos / $suma_total_provincia) * 100 : 0;
                    $votos_prov_filtrados->push($item);
                }

                // Insertamos la fila consolidada sintética de control al final del bucle
                if ($votos_otros > 0) {
                    $objeto_otros = (object)[
                        'partido' => 'OTROS PARTIDOS',
                        'total_votos' => $votos_otros,
                        'porcentaje_provincial' => $suma_total_provincia > 0 ? ($votos_otros / $suma_total_provincia) * 100 : 0
                    ];
                    $votos_prov_filtrados->push($objeto_otros);
                }
            } else {
                // Si la provincia tiene 3 partidos o menos inscritos, se mapea regular
                foreach ($votos_prov_completos as $item) {
                    $item->porcentaje_provincial = $suma_total_provincia > 0 ? ($item->total_votos / $suma_total_provincia) * 100 : 0;
                    $votos_prov_filtrados->push($item);
                }
            }

            $consejeros_consolidados[] = [
                'provincia_nombre' => $prov->nombre,
                'vacantes' => $vacantes_prov,
                'resultados' => $votos_prov_filtrados, // Pasamos la colección limpia acortada a la vista
                'escaños' => $reparto_prov
            ];
        }
    }

    // 4. CONSULTAS ESTÁNDAR PARA COMPORTAMIENTO INDIVIDUAL CONTRATADO
    $votos_partidos = collect();
    $especiales = (object)['blancos' => 0, 'nulos' => 0];
    $reparticion_dhondt = [];

    if (!$bloqueo_alcaldia_sin_ubigeo && !$es_vista_macro_consejeros) {
        $ubigeo_filtro = $this->distrito_id ?: ($this->provincia_id ? substr($this->provincia_id, 0, 4) : null);

        $votos_partidos = DB::table('votos_mesas')
            ->join('partidos_politicos', 'votos_mesas.partido_politico_id', '=', 'partidos_politicos.id')
            ->join('mesas_sufragio', 'votos_mesas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
            ->where('votos_mesas.tipo_eleccion', $this->tipo_cedula)
            ->where('votos_mesas.voto_especial', 'REGULAR')
            ->when($ubigeo_filtro, function($query) use ($ubigeo_filtro) {
                $query->where('centros_votacion.ubigeo_id', 'like', $ubigeo_filtro . '%');
            })
            ->when(!$ubigeo_filtro && $this->region_electoral_sel, function($query) {
                $query->where('ubigeos.region_electoral', $this->region_electoral_sel);
            })
            ->select('partidos_politicos.nombre as partido','partidos_politicos.logo_url', DB::raw('SUM(votos_mesas.cantidad_votos) as total_votos'))
            ->groupBy('partidos_politicos.nombre')
            ->orderByDesc('total_votos')
            ->get();

        $especiales = DB::table('votos_mesas')
            ->join('mesas_sufragio', 'votos_mesas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
            ->where('votos_mesas.tipo_eleccion', $this->tipo_cedula)
            ->when($ubigeo_filtro, function($query) use ($ubigeo_filtro) {
                $query->where('centros_votacion.ubigeo_id', 'like', $ubigeo_filtro . '%');
            })
            ->when(!$ubigeo_filtro && $this->region_electoral_sel, function($query) {
                $query->where('ubigeos.region_electoral', $this->region_electoral_sel);
            })
            ->select(
                DB::raw("SUM(CASE WHEN voto_especial = 'BLANCO' THEN cantidad_votos ELSE 0 END) as blancos"),
                DB::raw("SUM(CASE WHEN voto_especial = 'NULO' THEN cantidad_votos ELSE 0 END) as nulos")
            )->first();

        $reparticion_dhondt = $this->calcularDHondt($votos_partidos);
    }

    $total_validos = $votos_partidos->sum('total_votos');
    $total_emitidos = $total_validos + (int)($especiales->blancos ?? 0) + (int)($especiales->nulos ?? 0);

    $nombre_lugar = $this->region_electoral_sel;
    if ($this->distrito_id) $nombre_lugar = DB::table('ubigeos')->where('id', $this->distrito_id)->value('nombre');
    elseif ($this->provincia_id) $nombre_lugar = DB::table('ubigeos')->where('id', $this->provincia_id)->value('nombre');

    return view('livewire.resultados-dashboard', [
        'partidos' => $votos_partidos,
        'especiales' => $especiales,
        'total_validos' => $total_validos,
        'total_emitidos' => $total_emitidos,
        'escaños' => $reparticion_dhondt,
        'mostrar_alerta_seleccion' => $bloqueo_alcaldia_sin_ubigeo,
        'jurisdiccion_nombre' => $nombre_lugar,
        
        'es_macro_consejeros' => $es_vista_macro_consejeros,
        'consejeros_regionales_lista' => $consejeros_consolidados
    ])->layout('layouts.app');
}

    public function actualizarLimiteEscaños()
    {
        $this->escaños_disponibles = 0;

        // 1. Candado unipersonal para Gobernador
        if ($this->tipo_cedula === 'GOBERNADOR') {
            $this->escaños_disponibles = 1;
            return;
        }

        // 2. CORRECCIÓN CRÍTICA DE JERARQUÍA JNE
        if ($this->tipo_cedula === 'PROVINCIAL') {
            // Si la cédula es Provincial, ignoramos cualquier distrito y forzamos 
            // la lectura del Ubigeo de la Provincia (Cabecera terminada en 00, Ej: 150800)
            if (empty($this->provincia_id)) {
                return;
            }

            $ubigeo_provincial_completo = str_pad(substr($this->provincia_id, 0, 4), 6, "0", STR_PAD_RIGHT);
            $ubigeo_data = DB::table('ubigeos')->where('id', $ubigeo_provincial_completo)->first();

            if ($ubigeo_data) {
                $this->escaños_disponibles = (int) ($ubigeo_data->total_regidores ?? 0); // Devolverá 11 para Huaura
            }
            return;
        }

        // 3. Comportamiento regular para Consejeros y Distritales
        $id_consulta = $this->distrito_id ?: ($this->provincia_id ?: $this->departamento_id);
        if (!$id_consulta)
            return;

        $ubigeo_data = DB::table('ubigeos')->where('id', $id_consulta)->first();

        if ($ubigeo_data) {
            if ($this->tipo_cedula === 'CONSEJERO') {
                // Si mira toda la región, suma consejeros provinciales como ajustamos antes
                if (empty($this->provincia_id)) {
                    $this->escaños_disponibles = (int) DB::table('ubigeos')
                        ->where('region_electoral', $this->region_electoral_sel)
                        ->where('id', 'like', '%00')
                        ->where('id', 'not like', '%0000')
                        ->sum('consejeros_regionales');
                } else {
                    $this->escaños_disponibles = (int) ($ubigeo_data->consejeros_regionales ?? 0);
                }
            } elseif ($this->tipo_cedula === 'DISTRITAL') {
                // Toma los regidores específicos del distrito seleccionado
                $this->escaños_disponibles = (int) ($ubigeo_data->total_regidores ?? 0); // Devolverá 5
            }
        }
    }

}

