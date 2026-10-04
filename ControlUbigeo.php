<?php

namespace App\Livewire\Admin\Ubigeo;

use Livewire\Component;
use App\Models\Admin\Ubigeo;
use Illuminate\Support\Facades\DB;


class ControlUbigeo extends Component
{
    public $departamentos = [], $provincias = [], $distritos = [];
    public $selectedDepartamento = null;
    public $selectedProvincia = null;
    public $ubigeoFinal = null;

    // Propiedades informativas
    public $regionElectoralActual = null;
    public $esCapitalActual = false;
    public $regidoresActual = 0;
    public $consejerosActual = 0;
    public $consejerosRegion = 0;


    // Campos del Formulario
    public $nuevoConsejeros = 0;
    public $nuevoRegidores = 0;

    // Estados de activación de paneles individuales
    public $modoEdicionProvincia = false;
    public $modoEdicionDistrito = false;

    public function mount()
    {
        // Limpiamos selectores
        $this->departamentos = [];

        // Forzamos la carga de las 26 opciones político-electorales
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

    public function listarRegionesElectorales()
    {
        $deps = Ubigeo::where('id', 'LIKE', '%0000')->orderBy('nombre')->get();
        $regionesCompletas = [];

        foreach ($deps as $d) {
            if ($d->id === '140000') {
                $regionesCompletas[] = [
                    'id' => '14_PROVINCIAS',
                    'nombre' => 'LIMA PROVINCIAS (GOBIERNOS REGIONALES)'
                ];
                $regionesCompletas[] = [
                    'id' => '14_METROPOLITANA',
                    'nombre' => 'REGIÓN LIMA (METROPOLITANA)'
                ];
            } else {
                $regionesCompletas[] = [
                    'id' => $d->id,
                    'nombre' => 'REGIÓN ' . $d->nombre
                ];
            }
        }
        $this->departamentos = $regionesCompletas;
    }

    // Condicionamos la cascada de provincias según la región electoral virtual elegida
    public function cambiarDepartamento($id)
    {
        $this->selectedDepartamento = $id;
        $this->reset(['selectedProvincia', 'ubigeoFinal', 'regionElectoralActual', 'esCapitalActual', 'regidoresActual', 'consejerosActual', 'consejerosRegion', 'distritos', 'modoEdicionProvincia', 'modoEdicionDistrito']);

        if (empty($id))
            return;

        if ($id === '14_PROVINCIAS') {
            // Carga las 9 provincias periféricas, ocultando Lima Centro (1501)
            $this->provincias = Ubigeo::where('id', 'LIKE', '14%00')
                ->where('id', '!=', '140000')
                ->where('id', '!=', '140100')
                ->orderBy('nombre')->get();
        } elseif ($id === '14_METROPOLITANA') {
            // Para Lima Metropolitana la única provincia que elige regidores metropolitanos es Lima (140100)
            $this->provincias = Ubigeo::where('id', '140100')->get();
        } else {
            // Comportamiento estándar para el resto del país
            $prefijo = substr($id, 0, 2);
            $this->provincias = Ubigeo::where('id', 'LIKE', $prefijo . '%00')
                ->where('id', '!=', $id)
                ->orderBy('nombre')->get();
        }
    }
    public function cambiarProvincia($id)
    {
        $this->selectedProvincia = $id;
        $this->reset(['ubigeoFinal', 'regionElectoralActual', 'esCapitalActual', 'regidoresActual', 'modoEdicionDistrito']);
        $this->modoEdicionProvincia = false;

        if (!empty($id)) {
            $provinciaInfo = Ubigeo::find($id);
            $prefijo = substr($id,0,2) . '0000';
            $prov = Ubigeo::find($prefijo);
            $consejerosRegion = $prov->consejeros_regionales ?? 0;

            $this->consejerosActual = $provinciaInfo ? (int) $provinciaInfo->consejeros_regionales : 0;
            $this->nuevoConsejeros = $this->consejerosActual;
            $this->consejerosRegion = DB::table('ubigeos')
                        ->where('region_electoral',$provinciaInfo->region_electoral)
                        ->sum('consejeros_regionales') - $consejerosRegion;
                        // Se activa el formulario de consejeros inmediatamente a nivel provincial
            $this->modoEdicionProvincia = true;

            $prefijo = substr($id, 0, 4);
            $this->distritos = Ubigeo::where('id', 'LIKE', $prefijo . '%')
                ->where('id', '!=', $id)
                ->orderBy('nombre')->get();
        } else {
            $this->distritos = [];
            $this->consejerosActual = 0;
        }
    }

    public function cambiarDistrito($id)
    {
        $this->ubigeoFinal = $id;
        $this->modoEdicionDistrito = false;

        if (!empty($id)) {
            $distritoInfo = Ubigeo::find($id);
            if ($distritoInfo) {
                $this->regionElectoralActual = $distritoInfo->region_electoral;
                $this->esCapitalActual = (bool) $distritoInfo->es_capital;
                $this->regidoresActual = (int) $distritoInfo->total_regidores;
                $this->nuevoRegidores = $this->regidoresActual;

                // Se activa el formulario de regidores a nivel distrital
                $this->modoEdicionDistrito = true;
            }
        } else {
            $this->regionElectoralActual = '';
            $this->esCapitalActual = false;
            $this->regidoresActual = 0;
        }
    }

    // ACCIÓN 1: ACTUALIZACIÓN MASIVA DE CONSEJEROS (NIVEL PROVINCIAL)
    public function guardarConsejerosProvinciales()
    {
        $this->validate(['nuevoConsejeros' => 'required|numeric|min:0|max:10']);

        if ($this->selectedProvincia) {
            $prefijoProv = substr($this->selectedProvincia, 0, 4);

            // ACTUALIZACIÓN EN CASCADA AUTOMÁTICA:
            // Modifica la provincia madre y a TODOS sus distritos dependientes en un solo bloque SQL
            Ubigeo::where('id', 'LIKE', $prefijoProv . '%')->update([
                'consejeros_regionales' => (int) $this->nuevoConsejeros
            ]);

            $this->consejerosActual = (int) $this->nuevoConsejeros;
            session()->flash('success_prov', 'Cuota de Consejeros actualizada en todos los distritos de la provincia.');
        }
    }

    // ACCIÓN 2: ACTUALIZACIÓN INDIVIDUAL DE REGIDORES (NIVEL DISTRITAL)
    public function guardarRegidoresDistritales()
    {
        $this->validate(['nuevoRegidores' => 'required|numeric|min:5|max:50']);

        if ($this->ubigeoFinal) {
            Ubigeo::where('id', $this->ubigeoFinal)->update([
                'total_regidores' => (int) $this->nuevoRegidores
            ]);

            $this->regidoresActual = (int) $this->nuevoRegidores;
            session()->flash('success_dist', 'Cuota de Regidores actualizada para este municipio.');
        }
    }

    public function render()
    {
        return view('livewire.admin.ubigeo.control-ubigeo')->layout('layouts.app');
    }
}
