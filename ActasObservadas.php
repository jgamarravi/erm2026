<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\Acta;
use App\Models\Admin\MesaSufragio;
use App\Models\Admin\ActaDetalle;
use Illuminate\Support\Facades\DB;

class ActasObservadas extends Component
{
    // Propiedades de búsqueda y listado
    public $buscar = '';
    public $filtro_estado = 'todas_invalidas'; // todas_invalidas, observada, impugnada

    // Propiedades del acta bajo auditoría / resolución
    public $acta_id_seleccionada = null;
    public $acta_auditoria = null;
    public $nuevo_estado = ''; // procesada (resolver), observada, impugnada
    public $sustento_resolucion = '';

    // Reglas de validación para resolver el acta
    protected $rules = [
        'nuevo_estado' => 'required|in:procesada,observada,impugnada',
        'sustento_resolucion' => 'required|string|min:10|max:500',
    ];

    public function updatedBuscar()
    {
        $this->resetPage(); // Si usas paginación de Livewire
    }

    public function seleccionarActa($id)
    {
        $this->resetValidation();
        $this->acta_id_seleccionada = $id;
        
        // Cargamos el acta observada con todo el detalle de votos por partido, mesas y locales
        $this->acta_auditoria = Acta::with([
            'mesaSufragio.centroVotacion.ubigeo',
            'detalles.partidoPolitico'
        ])->find($id);

        if ($this->acta_auditoria) {
            $this->nuevo_estado = $this->acta_auditoria->estado;
            $this->sustento_resolucion = ''; // Limpiar sustento anterior
        }
    }

    public function cerrarAuditoria()
    {
        $this->reset(['acta_id_seleccionada', 'acta_auditoria', 'nuevo_estado', 'sustento_resolucion']);
    }

    public function resolverActa()
    {
        if ($this->acta_auditoria->estado === 'procesada') {
            session()->flash('error', 'Esta acta ya se encuentra resuelta y procesada en el sistema.');
            return;
        }

        $this->validate();

        DB::beginTransaction();
        try {
            // Actualizamos el estado definitivo del acta
            // (Opcional: Si agregas columnas 'sustento' o 'usuario_resolucion_id' a tu migración, puedes guardarlas aquí)
            $acta = Acta::find($this->acta_id_seleccionada);
            $acta->update([
                'estado' => $this->nuevo_estado
            ]);

            DB::commit();

            $mensaje = $this->nuevo_estado === 'procesada' 
                ? 'El acta ha sido subsanada, cuadrada y guardada con éxito en el cómputo oficial.' 
                : 'El dictamen o estado del acta ha sido actualizado correctamente.';

            session()->flash('success', $mensaje);
            $this->cerrarAuditoria();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al resolver acta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Query base para capturar las actas que no entraron directas al cómputo limpio
        $query = Acta::with('mesaSufragio.centroVotacion.ubigeo')
            ->join('mesas_sufragio', 'actas.mesa_sufragio_id', '=', 'mesas_sufragio.id')
            ->select('actas.*');

        // Aplicación del filtro por tipo de anomalía
        if ($this->filtro_estado === 'todas_invalidas') {
            $query->whereIn('actas.estado', ['observada', 'impugnada']);
        } else {
            $query->where('actas.estado', $this->filtro_estado);
        }

        // Buscador por número de mesa
        if (strlen($this->buscar) >= 2) {
            $query->where('mesas_sufragio.numero_mesa', 'like', '%' . $this->buscar . '%');
        }

        $actas = $query->orderBy('actas.updated_at', 'desc')->get();

        return view('livewire.admin.electoral.actas-observadas', [
            'actas' => $actas
        ])->layout('layouts.app');
    }
}
