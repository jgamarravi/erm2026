<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\Ubigeo;
use App\Models\Admin\ActaEscrutinio;
use Illuminate\Support\Facades\DB;

class ReporteEscrutinio extends Component
{
    public $departamentos = [], $provincias = [], $distritos = [];
    public $selectedDep = '', $selectedProv = '', $selectedDist = '';
    
    // Ajustado a las 4 categorías oficiales de escrutinio
    public $tipo_reporte = 'GOBERNADOR'; 
    public $escañosAEliger = 1; 

    public function mount()
    {
        $this->listarDepartamentosElectorales();
    }

    public function listarDepartamentosElectorales()
    {
        $deps = Ubigeo::where('id', 'LIKE', '%0000')->orderBy('nombre')->get();
        $regionesCompletas = [];

        foreach ($deps as $d) {
            if ($d->id === '140000') {
                $regionesCompletas[] = ['id' => '14_PROVINCIAS', 'nombre' => 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)'];
                $regionesCompletas[] = ['id' => '14_METROPOLITANA', 'nombre' => 'REGIÓN LIMA (METROPOLITANA)'];
            } else {
                $regionesCompletas[] = ['id' => $d->id, 'nombre' => 'REGIÓN ' . $d->nombre];
            }
        }
        $this->departamentos = $regionesCompletas;
    }

    public function updatedTipoReporte()
    {
        // Resetea las selecciones al alternar entre Gobernador, Consejero, etc.
        $this->reset(['selectedDep', 'selectedProv', 'selectedDist', 'provincias', 'distritos']);
        $this->listarDepartamentosElectorales();
    }

    public function updatedSelectedDep($id)
    {
        $this->reset(['provincias', 'distritos', 'selectedProv', 'selectedDist']);
        if (empty($id)) return;

        if ($id === '14_PROVINCIAS') {
            $this->provincias = Ubigeo::where('id', 'LIKE', '14%00')
                ->where('id', '!=', '140000')
                ->where('id', '!=', '140100')
                ->orderBy('nombre')->get();
        } elseif ($id === '14_METROPOLITANA') {
            $this->provincias = Ubigeo::where('id', '140100')->get();
        } else {
            $this->provincias = Ubigeo::where('id', 'LIKE', substr($id, 0, 2) . '%00')
                ->where('id', '!=', $id)
                ->orderBy('nombre')->get();
        }
    }

    public function updatedSelectedProv($id)
    {
        $this->reset(['distritos', 'selectedDist']);
        if (!empty($id)) {
            $prefijo = substr($id, 0, 4);
            
            $query = Ubigeo::where('id', 'LIKE', $prefijo . '%')->where('id', '!=', $id);
            
            // Si es Alcalde Distrital, ocultamos la Capital de Provincia del select por duplicidad
            if ($this->tipo_reporte === 'ALCALDE_DISTRITAL') {
                $query->where('es_capital', false);
            }

            $this->distritos = $query->orderBy('nombre')->get();
        }
    }

    public function render()
    {
        $tablaReparto = [];
        $ubigeoFiltro = '';
        $procesarCalculo = false;

        // EVALUACIÓN JURIDICCIONAL SEGÚN LAS 4 CATEGORÍAS
        if ($this->tipo_reporte === 'GOBERNADOR' && !empty($this->selectedDep)) {
            $procesarCalculo = true;
            $ubigeoFiltro = $this->selectedDep;
            $this->escañosAEliger = 1; // Solo se elige 1 Gobernador por región
        } 
        elseif ($this->tipo_reporte === 'CONSEJERO' && !empty($this->selectedProv)) {
            $procesarCalculo = true;
            $ubigeoFiltro = $this->selectedProv;
            $provInfo = Ubigeo::find($this->selectedProv);
            $this->escañosAEliger = $provInfo ? (int)$provInfo->consejeros_regionales : 1;
        } 
        elseif ($this->tipo_reporte === 'ALCALDE_PROVINCIAL' && !empty($this->selectedProv)) {
            $procesarCalculo = true;
            $ubigeoFiltro = $this->selectedProv;
            $this->escañosAEliger = ($this->selectedProv === '140100') ? 39 : 11; // Regidores Provinciales
        } 
        elseif ($this->tipo_reporte === 'ALCALDE_DISTRITAL' && !empty($this->selectedDist)) {
            $procesarCalculo = true;
            $ubigeoFiltro = $this->selectedDist;
            $distInfo = Ubigeo::find($this->selectedDist);
            $this->escañosAEliger = $distInfo ? (int)$distInfo->total_regidores : 5; // Regidores Distritales
        }

        if ($procesarCalculo) {
            $votosValidos = DB::table('acta_escrutinios')
                ->join('partidos_politicos', 'acta_escrutinios.partido_politico_id', '=', 'partidos_politicos.id')
                ->join('mesas_sufragio', 'acta_escrutinios.mesa_sufragio_id', '=', 'mesas_sufragio.id')
                ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
                ->when($this->tipo_reporte === 'GOBERNADOR', function ($query) {
                    if ($this->selectedDep === '14_PROVINCIAS') return $query->where('centros_votacion.ubigeo_id', 'LIKE', '14%')->where('centros_votacion.ubigeo_id', 'NOT LIKE', '1401%');
                    if ($this->selectedDep === '14_METROPOLITANA') return $query->where('centros_votacion.ubigeo_id', 'LIKE', '1401%');
                    return $query->where('centros_votacion.ubigeo_id', 'LIKE', substr($this->selectedDep, 0, 2) . '%');
                })
                ->when($this->tipo_reporte === 'CONSEJERO' || $this->tipo_reporte === 'ALCALDE_PROVINCIAL', function ($query) {
                    return $query->where('centros_votacion.ubigeo_id', 'LIKE', substr($this->selectedProv, 0, 4) . '%');
                })
                ->when($this->tipo_reporte === 'ALCALDE_DISTRITAL', function ($query) {
                    return $query->where('centros_votacion.ubigeo_id', $this->selectedDist);
                })
                ->where('acta_escrutinios.tipo_eleccion', $this->tipo_reporte) // Mapeo idéntico con la base de datos
                ->where('acta_escrutinios.estado_cerrado', true)
                ->select('partidos_politicos.id', 'partidos_politicos.siglas', 'partidos_politicos.nombre', DB::raw('SUM(acta_escrutinios.votos_validos) as total_votos'))
                ->groupBy('partidos_politicos.id', 'partidos_politicos.siglas', 'partidos_politicos.nombre')
                ->orderBy('total_votos', 'desc')
                ->get();

            if ($votosValidos->count() > 0) {
                // Gobernador y Consejero aplican D'Hondt estricto; Alcaldes aplican el mixto (Premio Mayoría)
                $tablaReparto = (in_array($this->tipo_reporte, ['GOBERNADOR', 'CONSEJERO'])) 
                    ? $this->calcularDHondtEstricto($votosValidos, $this->escañosAEliger)
                    : $this->calcularDHondtMixtoRegidores($votosValidos, $this->escañosAEliger);
            }
        }

        return view('livewire.admin.electoral.reporte-escrutinio', ['resultados' => $tablaReparto])->layout('layouts.app');
    }

    private function calcularDHondtEstricto($listas, $escaños) {
        $cocientes = []; $escañosAsignados = [];
        foreach ($listas as $l) { $escañosAsignados[$l->id] = 0; for ($i = 1; $i <= $escaños; $i++) { $cocientes[] = ['partido_id' => $l->id, 'valor' => (int)$l->total_votos / $i]; } }
        usort($cocientes, function($a, $b) { return $b['valor'] <=> $a['valor']; });
        for ($i = 0; $i < min($escaños, count($cocientes)); $i++) { $escañosAsignados[$cocientes[$i]['partido_id']]++; }
        $resultadoFinal = []; foreach ($listas as $l) { $resultadoFinal[] = ['siglas' => $l->siglas, 'nombre' => $l->nombre, 'votos' => $l->total_votos, 'escaños' => $escañosAsignados[$l->id]]; }
        return $resultadoFinal;
    }

    private function calcularDHondtMixtoRegidores($listas, $escaños) {
        $escañosAsignados = []; foreach ($listas as $l) { $escañosAsignados[$l->id] = 0; }
        $ganador = $listas->first(); $premioMayoría = (int) floor($escaños / 2) + 1; $escañosAsignados[$ganador->id] = $premioMayoría;
        $escañosRestantes = $escaños - $premioMayoría;
        if ($escañosRestantes > 0) { $cocientes = []; foreach ($listas as $l) { for ($i = 1; $i <= $escañosRestantes; $i++) { $cocientes[] = ['partido_id' => $l->id, 'valor' => (int)$l->total_votos / $i]; } }
            usort($cocientes, function($a, $b) { return $b['valor'] <=> $a['valor']; });
            for ($i = 0; $i < min($escañosRestantes, count($cocientes)); $i++) { $escañosAsignados[$cocientes[$i]['partido_id']]++; } }
        $resultadoFinal = []; foreach ($listas as $l) { $resultadoFinal[] = ['siglas' => $l->siglas, 'nombre' => $l->nombre, 'votos' => $l->total_votos, 'escaños' => $escañosAsignados[$l->id]]; }
        return $resultadoFinal;
    }

        // MÉTODO PARA EXPORTAR EL REPARTO D'HONDT A EXCEL / CSV
    public function exportarActaDHondt()
    {
        // 1. Re-ejecutar el query actual para capturar los datos vigentes en pantalla
        $ubigeoFiltro = '';
        $tipoBusquedaActa = '';
        $procesarCalculo = false;

        if ($this->tipo_reporte === 'CONSEJO_REGIONAL' && !empty($this->selectedDep)) {
            $procesarCalculo = true; $tipoBusquedaActa = 'REGIONAL'; $ubigeoFiltro = $this->selectedDep;
        } elseif ($this->tipo_reporte === 'CONCEJO_PROVINCIAL' && !empty($this->selectedProv)) {
            $procesarCalculo = true; $tipoBusquedaActa = 'MUNICIPAL_PROVINCIAL'; $ubigeoFiltro = $this->selectedProv;
        } elseif ($this->tipo_reporte === 'CONCEJO_DISTRITAL' && !empty($this->selectedDist)) {
            $procesarCalculo = true; $tipoBusquedaActa = 'MUNICIPAL_DISTRITAL'; $ubigeoFiltro = $this->selectedDist;
            $distInfo = Ubigeo::find($this->selectedDist);
            if ($distInfo && $distInfo->es_capital) { $tipoBusquedaActa = 'MUNICIPAL_PROVINCIAL'; }
        }

        if (!$procesarCalculo) return;

        $votosValidos = DB::table('acta_escrutinios')
            ->join('partidos_politicos', 'acta_escrutinios.partido_politico_id', '=', 'partidos_politicos.id')
            ->join('mesas_sufragio', 'acta_escrutinios.mesa_sufragio_id', '=', 'mesas_sufragio.id')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->when($this->tipo_reporte === 'CONSEJO_REGIONAL', function ($query) {
                if ($this->selectedDep === '14_PROVINCIAS') return $query->where('centros_votacion.ubigeo_id', 'LIKE', '14%')->where('centros_votacion.ubigeo_id', 'NOT LIKE', '1401%');
                if ($this->selectedDep === '14_METROPOLITANA') return $query->where('centros_votacion.ubigeo_id', 'LIKE', '1401%');
                return $query->where('centros_votacion.ubigeo_id', 'LIKE', substr($this->selectedDep, 0, 2) . '%');
            })
            ->when($this->tipo_reporte === 'CONCEJO_PROVINCIAL', function ($query) {
                return $query->where('centros_votacion.ubigeo_id', 'LIKE', substr($this->selectedProv, 0, 4) . '%');
            })
            ->when($this->tipo_reporte === 'CONCEJO_DISTRITAL', function ($query) {
                return $query->where('centros_votacion.ubigeo_id', $this->selectedDist);
            })
            ->where('acta_escrutinios.tipo_eleccion', $tipoBusquedaActa)
            ->where('acta_escrutinios.estado_cerrado', true)
            ->select('partidos_politicos.id', 'partidos_politicos.siglas', 'partidos_politicos.nombre', DB::raw('SUM(acta_escrutinios.votos_validos) as total_votos'))
            ->groupBy('partidos_politicos.id', 'partidos_politicos.siglas', 'partidos_politicos.nombre')
            ->orderBy('total_votos', 'desc')
            ->get();

        $resultados = ($this->tipo_reporte === 'CONSEJO_REGIONAL') 
            ? $this->calcularDHondtEstricto($votosValidos, $this->escañosAEliger)
            : $this->calcularDHondtMixtoRegidores($votosValidos, $this->escañosAEliger);

                // 2. CONFIGURACIÓN Y CREACIÓN DEL ARCHIVO TEMPORAL
        $nombreArchivo = "acta_proclamacion_dhondt_" . strtolower($this->tipo_reporte) . "_" . date('Ymd_His') . ".csv";
        $rutaTemporal = storage_path('app/public/' . $nombreArchivo);

        $file = fopen($rutaTemporal, 'w');
        // Forzar el BOM UTF-8 para evitar errores de tildes en Excel de Windows
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezado Oficial del Reporte
        fputcsv($file, ["ACTA DE PROCLAMACIÓN Y ADJUDICACIÓN DE ESCAÑOS - ERM 2026"]);
        fputcsv($file, ["Tipo de Proceso:", str_replace('_', ' ', $this->tipo_reporte)]);
        fputcsv($file, ["Total de Curules/Regidurías en concurso:", $this->escañosAEliger]);
        fputcsv($file, ["Fecha de Generación:", date('d/m/Y H:i:s')]);
        fputcsv($file, []); 

        // Columnas de la sábana de datos
        fputcsv($file, ["Posicion", "Organizacion Politica", "Siglas", "Votos Obtenidos", "Porcentaje Valido", "Escanos Adjudicados"]);

        $totalVotosValidos = array_sum(array_column($resultados, 'votos'));

        foreach ($resultados as $indice => $res) {
            $porcentaje = $totalVotosValidos > 0 ? round(($res['votos'] / $totalVotosValidos) * 100, 2) . '%' : '0%';
            fputcsv($file, [
                "#" . ($indice + 1),
                $res['nombre'],
                $res['siglas'],
                $res['votos'],
                $porcentaje,
                $res['escaños']
            ]);
        }
        fclose($file);

        // 3. DESCARGA BINARIA COMPATIBLE AL 100% CON LIVEWIRE
        return response()->download($rutaTemporal, $nombreArchivo)->deleteFileAfterSend(true);
    }
}