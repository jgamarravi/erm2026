<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Admin\MesaSufragio; // Apunta a tu modelo de mesaSufragio
use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\Acta;
use App\Models\Admin\ActaDetalle;
use Illuminate\Support\Facades\DB;

class EscrutinioController extends Component
{
    public $numeroMesaBuscar = '';
    public $mesaActual = null;
    public $bloqueado = false;
    public $esDistritoCapital = false;

    // Campos Únicos de la Cabecera (Tabla: actas)
    public $total_votantes_acta = 0;
    public $observaciones = '';
    public $estado = 'procesada';

    // Votos Inválidos Globales de la Mesa
    public $blancos_gobernador = 0, $nulos_gobernador = 0;
    public $blancos_consejero = 0, $nulos_consejero = 0;
    public $blancos_provincial = 0, $nulos_provincial = 0;
    public $blancos_distrital = 0, $nulos_distrital = 0;

    // Arreglos de Votos Válidos indexados por [partido_id] (Tabla: acta_detalles)
    public $votosGobernador = [];
    public $votosConsejero = [];
    public $votosProvincial = [];
    public $votosDistrital = [];

    public function buscarMesa()
    {
        $this->validate(['numeroMesaBuscar' => 'required|numeric']);

        // Buscamos la mesa cargando su acta y el ubigeo del distrito
        $mesa = MesaSufragio::with(['acta.detalles', 'centroVotacion.distrito'])
            ->where('numero_mesa', $this->numeroMesaBuscar)->first();

        if (!$mesa) {
            $this->reset(['mesaActual', 'bloqueado']);
            $this->addError('numeroMesaBuscar', 'La mesa de sufragio no se encuentra registrada.');
            return;
        }

        $this->mesaActual = $mesa;
        $this->esDistritoCapital = (bool)$mesa->centroVotacion->distrito->es_capital;

        // Oferta electoral completa
        $todosLosPartidos = PartidoPolitico::orderBy('nombre')->get();

        // Limpieza e inicialización de arreglos
        $this->votosGobernador = []; $this->votosConsejero = []; $this->votosProvincial = []; $this->votosDistrital = [];

        if ($mesa->acta) {
            // SI EL ACTA YA EXISTE: Cargamos la cabecera
            $this->bloqueado = ($mesa->acta->estado === 'procesada'); 
            $this->estado = $mesa->acta->estado;
            $this->total_votantes_acta = $mesa->acta->total_votantes_acta;
            $this->observaciones = $mesa->acta->observaciones;
            
            $this->blancos_gobernador = $mesa->acta->blancos_gobernador;
            $this->nulos_gobernador = $mesa->acta->nulos_gobernador;
            $this->blancos_consejero = $mesa->acta->blancos_consejero;
            $this->nulos_consejero = $mesa->acta->nulos_consejero;
            $this->blancos_provincial = $mesa->acta->blancos_provincial;
            $this->nulos_provincial = $mesa->acta->nulos_provincial;
            $this->blancos_distrital = $mesa->acta->blancos_distrital;
            $this->nulos_distrital = $mesa->acta->nulos_distrital;

            // Mapeamos los detalles indexados por partido_id
            $detallesMap = $mesa->acta->detalles->keyBy('partido_id');
            foreach ($todosLosPartidos as $p) {
                $this->votosGobernador[$p->id] = $detallesMap[$p->id]->votos_gobernador ?? 0;
                $this->votosConsejero[$p->id] = $detallesMap[$p->id]->votos_consejero ?? 0;
                $this->votosProvincial[$p->id] = $detallesMap[$p->id]->votos_provincial ?? 0;
                $this->votosDistrital[$p->id] = $detallesMap[$p->id]->votos_distrital ?? 0;
            }
        } else {
            // SI ES UNA MESA NUEVA: Valores limpios en cero
            $this->bloqueado = false;
            $this->estado = 'procesada';
            $this->total_votantes_acta = 0;
            $this->observaciones = '';
            
            $this->reset([
                'blancos_gobernador', 'nulos_gobernador', 'blancos_consejero', 'nulos_consejero',
                'blancos_provincial', 'nulos_provincial', 'blancos_distrital', 'nulos_distrital'
            ]);

            foreach ($todosLosPartidos as $p) {
                $this->votosGobernador[$p->id] = 0;
                $this->votosConsejero[$p->id] = 0;
                $this->votosProvincial[$p->id] = 0;
                $this->votosDistrital[$p->id] = 0;
            }
        }
    }

    public function guardarActa()
    {
        // 1. VALIDACIÓN MATEMÁTICA PARALELA COMPLETA (ONPE)
        $sumaGobernador = array_sum($this->votosGobernador) + (int)$this->blancos_gobernador + (int)$this->nulos_gobernador;
        if ($sumaGobernador !== (int)$this->total_votantes_acta) {
            $this->addError('total_votantes_acta', "Descuadre GOBERNADOR: Suma {$sumaGobernador} de {$this->total_votantes_acta} firmas.");
            return;
        }

        $sumaConsejero = array_sum($this->votosConsejero) + (int)$this->blancos_consejero + (int)$this->nulos_consejero;
        if ($sumaConsejero !== (int)$this->total_votantes_acta) {
            $this->addError('total_votantes_acta', "Descuadre CONSEJERO: Suma {$sumaConsejero} de {$this->total_votantes_acta} firmas.");
            return;
        }

        $sumaProvincial = array_sum($this->votosProvincial) + (int)$this->blancos_provincial + (int)$this->nulos_provincial;
        if ($sumaProvincial !== (int)$this->total_votantes_acta) {
            $this->addError('total_votantes_acta', "Descuadre PROVINCIAL: Suma {$sumaProvincial} de {$this->total_votantes_acta} firmas.");
            return;
        }

        if (!$this->esDistritoCapital) {
            $sumaDistrital = array_sum($this->votosDistrital) + (int)$this->blancos_distrital + (int)$this->nulos_distrital;
            if ($sumaDistrital !== (int)$this->total_votantes_acta) {
                $this->addError('total_votantes_acta', "Descuadre DISTRITAL: Suma {$sumaDistrital} de {$this->total_votantes_acta} firmas.");
                return;
            }
        }

        // 2. TRANSACCIÓN ATÓMICA DE GUARDADO EN COMPONENTES SEPARADOS
        DB::transaction(function () {
            // Guardamos o actualizamos la Cabecera Única
            $acta = Acta::updateOrCreate(
                ['mesa_id' => $this->mesaActual->id],
                [
                    'estado' => $this->estado,
                    'blancos_gobernador' => (int)$this->blancos_gobernador,
                    'nulos_gobernador' => (int)$this->nulos_gobernador,
                    'blancos_consejero' => (int)$this->blancos_consejero,
                    'nulos_consejero' => (int)$this->nulos_consejero,
                    'blancos_provincial' => (int)$this->blancos_provincial,
                    'nulos_provincial' => (int)$this->nulos_provincial,
                    'blancos_distrital' => $this->esDistritoCapital ? 0 : (int)$this->blancos_distrital,
                    'nulos_distrital' => $this->esDistritoCapital ? 0 : (int)$this->nulos_distrital,
                    'total_votantes_acta' => (int)$this->total_votantes_acta,
                    'observaciones' => $this->observaciones
                ]
            );

            // Guardamos en paralelo los detalles de todos los partidos
            foreach ($this->votosProvincial as $partidoId => $votosProv) {
                ActaDetalle::updateOrCreate(
                    ['acta_id' => $acta->id, 'partido_id' => $partidoId],
                    [
                        'votos_gobernador' => (int)($this->votosGobernador[$partidoId] ?? 0),
                        'votos_consejero' => (int)($this->votosConsejero[$partidoId] ?? 0),
                        'votos_provincial' => (int)$votosProv,
                        'votos_distrital' => $this->esDistritoCapital ? 0 : (int)($this->votosDistrital[$partidoId] ?? 0)
                    ]
                );
            }
        });

        $this->bloqueado = ($this->estado === 'procesada');
        $this->dispatch('notificar-escrutinio', mensaje: 'Cómputo general de las 3 cédulas guardado con éxito.');
    }

    #[Layout('adminlte::page')] // Vinculación directa al layout de AdminLTE para corregir el error previo
    public function render()
    {
        return view('livewire.admin.electoral.escrutinio-controller');
    }
}
