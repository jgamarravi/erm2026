<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\Ubigeo;
use Illuminate\Support\Facades\DB;

class GestionPartidosUbigeo extends Component
{
    // Filtros de Cascada Territorial para ubicar el distrito
    public $region_id = '';
    public $provincia_id = '';
    public $distrito_id = '';

    // Colecciones para los selectores
    public $provincias = [];
    public $distritos = [];

    // Propiedades de asignación
    public $partidos_disponibles = [];
    public $partido_seleccionado_id = ''; // Para agregar uno nuevo

    public function updatedRegionId($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'distritos', 'partidos_disponibles']);
        if (!$value) {
            $this->provincias = [];
            return;
        }

        if ($value === 'Lima Metropolitana') {
            $this->provincias = Ubigeo::where('id', 'like', '1401%')
                ->where('id', 'like', '%00')
                ->select(DB::raw('SUBSTRING(id, 1, 4) as id'), DB::raw('"Lima" as nombre'))
                ->groupBy(DB::raw('SUBSTRING(id, 1, 4)'))
                ->get()->toArray();
        } else {
            $this->provincias = Ubigeo::where('region_electoral', $value)
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
        $this->reset(['partidos_disponibles']);
        if (!$value) {
            $this->distritos = [];
            return;
        }

        // Listamos todos los distritos pertenecientes a la provincia elegida
        $this->distritos = Ubigeo::where('id', 'like', $value . '%')
            ->where('id', 'not like', '%00')
            ->orderBy('nombre', 'asc')
            ->get()->toArray();
    }

    public function updatedDistritoId()
    {
        $this->resetValidation();
        $this->partido_seleccionado_id = '';
        $this->cargarPartidosDisponiblesParaAsignar();
    }

    // Carga los partidos que NO están asignados actualmente a este distrito
    public function cargarPartidosDisponiblesParaAsignar()
    {
        if (!$this->distrito_id) {
            $this->partidos_disponibles = [];
            return;
        }

        $this->partidos_disponibles = PartidoPolitico::whereDoesntHave('ubigeos', function ($q) {
            $q->where('ubigeos.id', $this->distrito_id);
        })->orderBy('orden_cedula', 'asc')->get();
    }

    // ACCIÓN: CREAR / ASOCIAR PARTIDO AL UBIGEO
    public function asignarPartido()
    {
        $this->validate([
            'distrito_id' => 'required|string|size:6',
            'partido_seleccionado_id' => 'required|exists:partidos_politicos,id'
        ]);

        DB::beginTransaction();
        try {
            $distritoSeleccionado = Ubigeo::find($this->distrito_id);

            if (!$distritoSeleccionado) {
                session()->flash('error', 'El ubigeo seleccionado no es válido.');
                return;
            }

            // 1. Asignación obligatoria al distrito actual seleccionado
            // syncWithoutDetaching evita duplicados si por algún motivo ya estaba guardado
            $distritoSeleccionado->partidos()->syncWithoutDetaching([$this->partido_seleccionado_id]);

            $mensajeAdicional = '';

            // 2. REQUERIMIENTO: Si es Capital de Provincia, distribuir en cascada para TODOS los distritos
            if ((bool) $distritoSeleccionado->es_capital) {
                // Extraemos los primeros 4 dígitos del INEI que identifican a la provincia (ej: "1508" para Huaura)
                $provinciaCodigo = substr($this->distrito_id, 0, 4);

                // Buscamos todos los distritos hermanos de la misma provincia (ej: Sayán, Hualmay, etc.)
                $distritosHermanos = Ubigeo::where('id', 'like', $provinciaCodigo . '%')
                    ->where('id', 'not like', '%00') // Excluye cabeceras
                    ->get();

                foreach ($distritosHermanos as $distrito) {
                    // Registramos la relación en la tabla intermedia ubigeo_has_parties
                    $distrito->partidos()->syncWithoutDetaching([$this->partido_seleccionado_id]);
                }

                $mensajeAdicional = " por tratarse de una Capital Provincial. Se replicó la inscripción en " . $distritosHermanos->count() . " distritos automáticamente.";
            }

            DB::commit();

            session()->flash('success', 'Organización política vinculada con éxito' . $mensajeAdicional);
            $this->partido_seleccionado_id = '';
            $this->cargarPartidosDisponiblesParaAsignar();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar la vinculación: ' . $e->getMessage());
        }
    }



    // ACCIÓN: ELIMINAR / QUITAR PARTIDO DEL UBIGEO
    public function desasignarPartido($partidoId)
    {
        if (!$this->distrito_id)
            return;

        try {
            $distrito = Ubigeo::find($this->distrito_id);

            // Rompemos la relación en la tabla intermedia ubigeo_has_parties
            $distrito->partidos()->detach($partidoId);

            session()->flash('success', 'Habilitación de partido removida del distrito correctamente.');
            $this->cargarPartidosDisponiblesParaAsignar();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al remover la asignación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Obtenemos los partidos que SÍ compiten actualmente en el distrito seleccionado
        $partidosAsignados = collect();
        if ($this->distrito_id) {
            $distrito = Ubigeo::with('partidos')->find($this->distrito_id);
            if ($distrito) {
                $partidosAsignados = $distrito->partidos()->orderBy('orden_cedula', 'asc')->get();
            }
        }

        // Reconstrucción del casillero de Regiones
        $regiones = Ubigeo::select('region_electoral')
            ->whereNotNull('region_electoral')->where(DB::raw('TRIM(region_electoral)'), '!=', '')
            ->groupBy('region_electoral')->orderBy('region_electoral', 'asc')->get();

        return view('livewire.admin.electoral.gestion-partidos-ubigeo', [
            'regiones' => $regiones,
            'partidosAsignados' => $partidosAsignados
        ])->layout('layouts.app');
    }
}
