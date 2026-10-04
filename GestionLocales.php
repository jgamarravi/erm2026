<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\Ubigeo;
use App\Models\Admin\CentroVotacion;
use App\Models\Admin\MesaSufragio;

class GestionLocales extends Component
{
    public $departamentos = [], $provincias = [], $distritos = [], $locales = [];
    public $selectedDep = '', $selectedProv = '', $selectedDist = '', $selectedCentro = '';

    // Formulario Locales
    public $nombre_local, $direccion_local;
    public $local_id_editando = null;
    public $modo_edicion_local = false;

    // Formulario Mesas
    public $numero_mesa, $electores_habiles = 300;
    public $mesa_id_editando = null;
    public $modo_edicion_mesa = false;

    // Buscador Rápido
    public $buscarMesa = '';
    public $resultadoBusqueda = null;
    public $total_votaron=0;
    public $largoChar = 6;

    public function mount()
    {
        $this->departamentos = Ubigeo::where('id', 'LIKE', '%0000')->orderBy('nombre')->get();
        $this->calcularSiguienteValor();

    }

    // =========================================================================
    // ACCIONES PARA CENTROS DE VOTACIÓN (LOCALES)
    // =========================================================================
    public function editarLocal($id)
    {
        $local = CentroVotacion::findOrFail($id);
        $this->local_id_editando = $local->id;
        $this->nombre_local = $local->nombre;
        $this->direccion_local = $local->direccion;
        $this->modo_edicion_local = true;
    }

    public function cancelarEdicionLocal()
    {
        $this->reset(['nombre_local', 'direccion_local', 'local_id_editando', 'modo_edicion_local']);
        $this->resetErrorBag();
    }

    public function guardarLocal()
    {
        $reglaNombre = $this->modo_edicion_local ? 'required|min:5|max:150' : 'required|min:5|max:150';

        $this->validate([
            'selectedDist' => 'required',
            'nombre_local' => $reglaNombre,
            'direccion_local' => 'required|min:5|max:200',
        ]);

        if ($this->modo_edicion_local) {
            $local = CentroVotacion::findOrFail($this->local_id_editando);
            $local->update([
                'nombre' => mb_strtoupper($this->nombre_local, 'UTF-8'),
                'direccion' => mb_strtoupper($this->direccion_local, 'UTF-8'),
            ]);
            session()->flash('success_local', 'Centro de votación modificado con éxito.');
        } else {
            CentroVotacion::create([
                'ubigeo_id' => $this->selectedDist,
                'nombre' => mb_strtoupper($this->nombre_local, 'UTF-8'),
                'direccion' => mb_strtoupper($this->direccion_local, 'UTF-8'),
            ]);
            session()->flash('success_local', 'Centro de votación registrado con éxito.');
        }

        $this->cancelarEdicionLocal();
        $this->listarLocales();
    }

    public function eliminarLocal($id)
    {
        $local = CentroVotacion::findOrFail($id);
        $local->delete(); //MySQL elimina en cascada las mesas asociadas por constraint
        if ($this->selectedCentro == $id) {
            $this->reset(['selectedCentro', 'modo_edicion_mesa', 'mesa_id_editando']);
        }
        $this->listarLocales();
        session()->flash('success_local', 'Local de votación removido del sistema.');
    }

    // =========================================================================
    // ACCIONES PARA MESAS DE SUFRAGIO
    // =========================================================================
    public function editarMesa($id)
    {
        $mesa = MesaSufragio::findOrFail($id);
        $this->mesa_id_editando = $mesa->id;
        $this->numero_mesa = $mesa->numero_mesa;
        $this->electores_habiles = $mesa->electores_habiles;
        $this->total_votaron = $mesa->total_votaron ?? 0;
        $this->modo_edicion_mesa = true;
    }

    public function cancelarEdicionMesa()
    {
        $this->reset(['electores_habiles', 'mesa_id_editando', 'modo_edicion_mesa']);

        $this->resetErrorBag();
    }

    public function guardarMesa()
    {
        $reglaMesa = $this->modo_edicion_mesa
            ? 'required|digits:6|unique:mesas_sufragio,numero_mesa,' . $this->mesa_id_editando
            : 'required|digits:6|unique:mesas_sufragio,numero_mesa';

        $this->validate([
            'selectedCentro' => 'required',
            'numero_mesa' => $reglaMesa,
            'electores_habiles' => 'required|numeric|min:10|max:500',
        ]);

        if ($this->modo_edicion_mesa) {
            $mesa = MesaSufragio::findOrFail($this->mesa_id_editando);
            $mesa->update([
                'numero_mesa' => $this->numero_mesa,
                'electores_habiles' => $this->electores_habiles,
                'total_votaron' => $this->total_votaron,
            ]);
            session()->flash('success_mesa', 'Mesa de sufragio actualizada correctamente.');
        } else {
            MesaSufragio::create([
                'centro_votacion_id' => $this->selectedCentro,
                'numero_mesa' => $this->numero_mesa,
                'electores_habiles' => $this->electores_habiles,
                'total_votaron' => $this->total_votaron,
            ]);
            // ajustar a 6 caracteres con ceros a la izquierda
            $this->calcularSiguienteValor();

            session()->flash('success_mesa', 'Mesa de sufragio añadida al local.');
        }

        $this->cancelarEdicionMesa();
    }

    public function eliminarMesa($id)
    {
        $mesa = MesaSufragio::findOrFail($id);
        $mesa->delete();
        session()->flash('success_mesa', 'Mesa de sufragio eliminada.');
    }

    // =========================================================================
    // MÉTODOS DE BÚSQUEDA Y JURISDICCIÓN
    // =========================================================================
    public function updatedBuscarMesa($value)
    {
        $this->resultadoBusqueda = null;
        if (strlen($value) === 6) {
            $mesa = MesaSufragio::where('numero_mesa', $value)->with('centroVotacion')->first();
            
            if ($mesa && $mesa->centroVotacion()) {
                $distrito = Ubigeo::find($mesa->centroVotacion->ubigeo_id);
                if ($distrito) {
                    $dep = Ubigeo::find(substr($distrito->id, 0, 2) . '0000');
                    $prov = Ubigeo::find(substr($distrito->id, 0, 4) . '00');
                    $this->resultadoBusqueda = [
                        'mesa' => $mesa->numero_mesa,
                        'electores' => $mesa->electores_habiles,
                        'local' => $mesa->centroVotacion->nombre,
                        'direccion' => $mesa->centroVotacion->direccion,
                        'distrito' => $distrito->nombre,
                        'provincia' => $prov ? $prov->nombre : '',
                        'departamento' => $dep ? $dep->nombre : '',
                    ];
                }
            } else {
                session()->flash('search_error', 'Mesa no encontrada.');
            }
        }
    }

    public function limpiarBusqueda()
    {
        $this->reset(['buscarMesa', 'resultadoBusqueda']);
    }
    public function updatedSelectedDep($id)
    {
        $this->provincias = !empty($id) ? Ubigeo::where('id', 'LIKE', substr($id, 0, 2) . '%00')->where('id', '!=', $id)->orderBy('nombre')->get() : [];
        $this->reset(['distritos', 'locales', 'selectedProv', 'selectedDist', 'selectedCentro', 'buscarMesa', 'resultadoBusqueda']);
        $this->cancelarEdicionLocal();
        $this->cancelarEdicionMesa();
    }
    public function updatedSelectedProv($id)
    {
        $this->distritos = !empty($id) ? Ubigeo::where('id', 'LIKE', substr($id, 0, 4) . '%')->where('id', '!=', $id)->orderBy('nombre')->get() : [];
        $this->reset(['locales', 'selectedDist', 'selectedCentro', 'buscarMesa', 'resultadoBusqueda']);
        $this->cancelarEdicionLocal();
        $this->cancelarEdicionMesa();
    }
    public function updatedSelectedDist($id)
    {
        $this->selectedDist = $id;
        $this->reset(['buscarMesa', 'resultadoBusqueda']);
        $this->cancelarEdicionLocal();
        $this->cancelarEdicionMesa();
        $this->listarLocales();
    }
    public function listarLocales()
    {
        $this->locales = !empty($this->selectedDist) ? CentroVotacion::where('ubigeo_id', $this->selectedDist)->withCount('mesaSufragio')->get() : [];
        $this->reset(['selectedCentro']);
    }

    public function render()
    {
        $mesasActuales = !empty($this->selectedCentro) ? MesaSufragio::where('centro_votacion_id', $this->selectedCentro)->get() : [];
        return view('livewire.admin.electoral.gestion-locales', ['mesas' => $mesasActuales])->layout('layouts.app');
    }

    public function calcularSiguienteValor()
    {
        // 1. Buscamos el valor más alto convirtiendo el CHAR a número en la consulta SQL
        $ultimoRegistro = MesaSufragio::query()
            ->selectRaw('CAST(numero_mesa AS UNSIGNED) as numero_entero')
            ->orderBy('numero_entero', 'desc')
            ->first();

        // 2. Si existe un registro previo, tomamos su número, si no, empezamos en 0
        $ultimoId = $ultimoRegistro ? $ultimoRegistro->numero_entero : 0;
        $nuevoNumero = $ultimoId + 1;

        // 3. Convertimos el número de vuelta a texto rellenando con ceros a la izquierda
        // Ejemplo con largo 5: 6 se convierte en "00006"
        $this->numero_mesa = str_pad($nuevoNumero, $this->largoChar, "0", STR_PAD_LEFT);
    }
}
