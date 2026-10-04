<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\Candidato;
use App\Models\Admin\Ubigeo;
use Livewire\WithPagination;

class ControlCandidatos extends Component
{
    use WithFileUploads;

    use WithPagination;
    protected $paginationTheme = 'bootstrap'; // Mantiene el estilo AdminLTE


    // Listados dinámicos
    public $partidos = [], $departamentos = [], $provincias = [], $distritos = [];
    public $selectedDep = '', $selectedProv = '', $selectedDist = '';

    // Formulario Partido
    public $nombre_partido, $siglas_partido, $orden_cedula, $logo_partido;
    public $partido_id_editando = null, $modo_edicion = false;

    // Formulario Candidato (Campos mapeados)
    public $partido_id, $dni, $nombres, $apellidos, $cargo, $ubigeo_final;
    public $candidato_id_editando = null;
    public $modo_edicion_candidato = false; // Estado de control para candidatos

    public function mount()
    {
        $this->listarDepartamentosElectorales();
        $this->listarPartidos();
    }

    public function updating()
    {
        $this->resetPage();
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

    public function listarPartidos()
    {
        $this->partidos = PartidoPolitico::withCount('candidatos')->orderBy('orden_cedula', 'asc')->get();
    }

    // =========================================================================
    // ACCIONES DE EDICIÓN Y CONTROL PARA CANDIDATOS
    // =========================================================================
    public function editarCandidato($id)
    {
        $candidato = Candidato::findOrFail($id);
        $this->candidato_id_editando = $candidato->id;
        $this->partido_id = $candidato->partido_politico_id;
        $this->dni = $candidato->dni;
        $this->nombres = $candidato->nombres;
        $this->apellidos = $candidato->apellidos;
        $this->cargo = $candidato->cargo;
        $this->modo_edicion_candidato = true;

        // Desarmamos el ubigeo_id guardado en MySQL para re-encender los selectores en cascada de la vista
        $ubigeo = $candidato->ubigeo_id;
        if ($ubigeo === '140000') {
            $this->selectedDep = '14_PROVINCIAS';
        } elseif (str_starts_with($ubigeo, '1401')) {
            $this->selectedDep = '14_METROPOLITANA';
            $this->updatedSelectedDep('14_METROPOLITANA');
            $this->selectedProv = '140100';
            $this->updatedSelectedProv('140100');
            $this->selectedDist = $ubigeo;
        } else {
            $this->selectedDep = substr($ubigeo, 0, 2) . '0000';
            $this->updatedSelectedDep($this->selectedDep);
            if (!str_ends_with($ubigeo, '0000')) {
                $this->selectedProv = substr($ubigeo, 0, 4) . '00';
                $this->updatedSelectedProv($this->selectedProv);
                if (!str_ends_with($ubigeo, '00')) {
                    $this->selectedDist = $ubigeo;
                }
            }
        }
    }

    public function cancelarEdicionCandidato()
    {
        $this->reset(['partido_id', 'dni', 'nombres', 'apellidos', 'cargo', 'ubigeo_final', 'selectedDep', 'selectedProv', 'selectedDist', 'provincias', 'distritos', 'candidato_id_editando', 'modo_edicion_candidato']);
        $this->resetErrorBag();
    }

    public function guardarCandidato()
    {
        // Definición de Ubigeo síncrona
        if ($this->selectedDep === '14_PROVINCIAS') {
            $ubigeoMapeado = '140000';
        } elseif ($this->selectedDep === '14_METROPOLITANA') {
            $ubigeoMapeado = '140100';
        } else {
            $ubigeoMapeado = $this->selectedDep;
        }

        if (in_array($this->cargo, ['GOBERNADOR', 'CONSEJERO'])) {
            $this->ubigeo_final = $ubigeoMapeado;
        } elseif ($this->cargo == 'ALCALDE_PROVINCIAL') {
            $this->ubigeo_final = $this->selectedProv;
        } else {
            $this->ubigeo_final = $this->selectedDist;
        }

        $reglaDni = $this->modo_edicion_candidato
            ? 'required|numeric|digits:8|unique:candidatos,dni,' . $this->candidato_id_editando
            : 'required|numeric|digits:8|unique:candidatos,dni';

        $this->validate([
            'partido_id' => 'required',
            'dni' => $reglaDni,
            'nombres' => 'required|max:100',
            'apellidos' => 'required|max:100',
            'cargo' => 'required',
            'ubigeo_final' => 'required',
        ], [
            'dni.digits' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Este ciudadano ya se encuentra registrado en otra postulación.',
            'ubigeo_final.required' => 'Falta delimitar geográficamente la postulación.'
        ]);

        if ($this->modo_edicion_candidato) {
            $candidato = Candidato::findOrFail($this->candidato_id_editando);
            $candidato->update([
                'partido_politico_id' => $this->partido_id,
                'dni' => $this->dni,
                'nombres' => mb_strtoupper($this->nombres, 'UTF-8'),
                'apellidos' => mb_strtoupper($this->apellidos, 'UTF-8'),
                'cargo' => $this->cargo,
                'ubigeo_id' => $this->ubigeo_final,
            ]);
            session()->flash('success_candidato', 'Datos del candidato actualizados de forma conforme.');
        } else {
            Candidato::create([
                'partido_politico_id' => $this->partido_id,
                'dni' => $this->dni,
                'nombres' => mb_strtoupper($this->nombres, 'UTF-8'),
                'apellidos' => mb_strtoupper($this->apellidos, 'UTF-8'),
                'cargo' => $this->cargo,
                'ubigeo_id' => $this->ubigeo_final,
            ]);
            session()->flash('success_candidato', 'Candidato inscrito exitosamente.');
        }

        $this->cancelarEdicionCandidato();
        $this->listarPartidos();
    }

    public function eliminarCandidato($id)
    {
        $candidato = Candidato::findOrFail($id);
        $candidato->delete();
        $this->listarPartidos();
        session()->flash('success_candidato', 'Postulante retirado de las listas del proceso.');
    }

    // =========================================================================
    // ENRUTAMIENTOS GEOGRÁFICOS Y PARTIDOS (MANTENER IGUAL)
    // =========================================================================
    public function updatedSelectedDep($id)
    {
        $this->provincias = !empty($id) ? Ubigeo::where('id', 'LIKE', substr($id, 0, 2) . '%00')->where('id', '!=', '140000')->where('id', '!=', '140100')->orderBy('nombre')->get() : [];
        if ($id === '14_PROVINCIAS') {
            $this->provincias = Ubigeo::where('id', 'LIKE', '14%00')->where('id', '!=', '140000')->where('id', '!=', '140100')->orderBy('nombre')->get();
        }
        if ($id === '14_METROPOLITANA') {
            $this->provincias = Ubigeo::where('id', '140100')->get();
        }
        $this->reset(['distritos', 'selectedProv', 'selectedDist', 'ubigeo_final']);
    }
    public function updatedSelectedProv($id)
    {
        $this->distritos = !empty($id) ? Ubigeo::where('id', 'LIKE', substr($id, 0, 4) . '%')->where('id', '!=', $id)->orderBy('nombre')->get() : [];
        $this->reset(['selectedDist', 'ubigeo_final']);
    }
    public function editarPartido($id)
    {
        $partido = PartidoPolitico::findOrFail($id);
        $this->partido_id_editando = $partido->id;
        $this->nombre_partido = $partido->nombre;
        $this->siglas_partido = $partido->siglas;
        $this->orden_cedula = $partido->orden_cedula;
        $this->modo_edicion = true;
    }
    public function cancelarEdicion()
    {
        $this->reset(['nombre_partido', 'siglas_partido', 'orden_cedula', 'logo_partido', 'partido_id_editando', 'modo_edicion']);
        $this->resetErrorBag();
    }
    public function eliminarPartido($id)
    {
        $partido = PartidoPolitico::findOrFail($id);
        $partido->delete();
        $this->listarPartidos();
        session()->flash('success_partido', 'Organización removida.');
    }
    public function guardarPartido()
    {
        $reglaNombre = $this->modo_edicion ? 'required|max:150|unique:partidos_politicos,nombre,' . $this->partido_id_editando : 'required|unique:partidos_politicos,nombre|max:150';
        $reglaSiglas = $this->modo_edicion ? 'required|max:20|unique:partidos_politicos,siglas,' . $this->partido_id_editando : 'required|unique:partidos_politicos,siglas|max:20';
        $reglaCedula = $this->modo_edicion ? 'required|numeric|min:1|unique:partidos_politicos,orden_cedula,' . $this->partido_id_editando : 'required|numeric|min:1|unique:partidos_politicos,orden_cedula';
        $this->validate(['nombre_partido' => $reglaNombre, 'siglas_partido' => $reglaSiglas, 'orden_cedula' => $reglaCedula, 'logo_partido' => 'nullable|image|max:1024']);
        if ($this->modo_edicion) {
            $partido = PartidoPolitico::findOrFail($this->partido_id_editando);
            if ($this->logo_partido) {
                $nombreArchivo = $this->siglas_partido . '' . time() . '.' . $this->logo_partido->getClientOriginalExtension();
                $partido->logo_url = $this->logo_partido->storeAs('logos', $nombreArchivo, 'public');
            }
            $partido->update(['nombre' => mb_strtoupper($this->nombre_partido, 'UTF-8'), 'siglas' => mb_strtoupper($this->siglas_partido, 'UTF-8'), 'orden_cedula' => (int) $this->orden_cedula,]);
            session()->flash('success_partido', 'Organización modificada.');
        } else {
            $rutaLogo = null;
            if ($this->logo_partido) {
                $nombreArchivo = $this->siglas_partido . '' . time() . '.' . $this->logo_partido->getClientOriginalExtension();
                $rutaLogo = $this->logo_partido->storeAs('logos', $nombreArchivo, 'public');
            }
            PartidoPolitico::create(['nombre' => mb_strtoupper($this->nombre_partido, 'UTF-8'), 'siglas' => mb_strtoupper($this->siglas_partido, 'UTF-8'), 'orden_cedula' => (int) $this->orden_cedula, 'logo_url' => $rutaLogo]);
            session()->flash('success_partido', 'Organización registrada.');
        }
        $this->cancelarEdicion();
        $this->listarPartidos();
    }
    public function render()
    {
        return view('livewire.admin.electoral.control-candidatos', [
            // 3. CAMBIAR POR CONSULTA PAGINADA DE ALTO RENDIMIENTO
            'todosLosCandidatos' => Candidato::with('partido')
                ->orderBy('id', 'desc')
                ->paginate(12) 
        ])->layout('layouts.app');
    }
}