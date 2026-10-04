<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\MesaSufragio;
use App\Models\Admin\PartidoPolitico;
use App\Models\Admin\ActaEscrutinio;
use App\Models\Admin\Ubigeo;

class ActaMesa extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $numeroMesaBusqueda = '';
    public $mesaActual = null;
    public $partidos = [];

    // Selectores de navegación interna
    public $tipo_eleccion = 'GOBERNADOR';
    public $esCapitalMesa = false;
    public $actaYaCerrada = false;

    // Matriz bidimensional temporal para retener los votos en memoria antes de guardar
    // Estructura: $votosMemoria[TIPO_ELECCION][PARTIDO_ID]
    public $votosMemoria = [];

    // Totales especiales por cada tipo de elección
    public $blancos = [];
    public $nulos = [];
    public $estadoVerificacionMesa = 'CONFORME';


    // Método centralizado para buscar y cargar los votos de la base de datos
    public function cargarVotosMesa()
    {
        if (!$this->mesaActual) return;

        // BLINDAJE CRÍTICO: Inicializamos las llaves de los 4 estamentos en la matriz de memoria
        $tiposElectorales = ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL'];
        foreach ($tiposElectorales as $t) {
            if (!isset($this->votosMemoria[$t])) {
                $this->votosMemoria[$t] = [];
            }
        }

        // Limpiamos la lectura de la pestaña activa actual por seguridad
        $this->blancos[$this->tipo_eleccion] = 0;
        $this->nulos[$this->tipo_eleccion] = 0;

        foreach ($this->partidos as $p) {
            // Buscamos si existe un registro real guardado en MySQL para esta mesa y pestaña activa
            $votoPrevio = ActaEscrutinio::where('mesa_sufragio_id', $this->mesaActual->id)
                ->where('partido_politico_id', $p->id)
                ->where('tipo_eleccion', $this->tipo_eleccion)
                ->first();
            
            $this->votosMemoria[$this->tipo_eleccion][$p->id] = $votoPrevio ? $votoPrevio->votos_validos : 0;
            
            if ($votoPrevio) {
                $this->blancos[$this->tipo_eleccion] = $votoPrevio->votos_blancos;
                $this->nulos[$this->tipo_eleccion] = $votoPrevio->votos_nulos;
            }
        }
    }


        public function cargarVotosMesaGlobal()
    {
        if (!$this->mesaActual) return;

        $tipos = ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL'];

        foreach ($tipos as $t) {
            foreach ($this->partidos as $p) {
                $votoPrevio = ActaEscrutinio::where('mesa_sufragio_id', $this->mesaActual->id)
                    ->where('partido_politico_id', $p->id)
                    ->where('tipo_eleccion', $t)
                    ->first();

                if ($votoPrevio) {
                    $this->votosMemoria[$t][$p->id] = $votoPrevio->votos_validos;
                    $this->blancos[$t] = $votoPrevio->votos_blancos;
                    $this->nulos[$t] = $votoPrevio->votos_nulos;
                }
            }
        }
    }


    // MÉTODO PARA DESBLOQUEAR EL ACTA DE FORMA MANUAL
    public function abrirActaParaModificacion()
    {
        if (!$this->mesaActual)
            return;

        // Cambiamos el estado a 0 (borrador) para todas las cédulas asociadas a esta mesa en MySQL
        ActaEscrutinio::where('mesa_sufragio_id', $this->mesaActual->id)
            ->update(['estado_cerrado' => false]);

        // Sincronizamos las variables del componente para reactivar la interfaz
        $this->actaYaCerrada = false;

        // Limpiamos bolsas de errores previas y recargamos los inputs
        $this->resetErrorBag();
        $this->cargarVotosMesa();

        session()->flash('success_acta', 'El acta se ha desbloqueado correctamente. Los campos están listos para corrección.');
        $this->dispatch('$refresh');
    }

        public function updatedNumeroMesaBusqueda($value)
    {
        $this->reset(['mesaActual', 'partidos', 'votosMemoria', 'blancos', 'nulos', 'esCapitalMesa', 'actaYaCerrada', 'estadoVerificacionMesa']);
        $this->tipo_eleccion = 'GOBERNADOR';

        if (strlen($value) === 6) {
            $this->mesaActual = MesaSufragio::where('numero_mesa', $value)->with('centro')->first();
            
            if ($this->mesaActual) {
                // 1. Validar si el distrito es Capital de Provincia
                $distritoInfo = Ubigeo::find($this->mesaActual->centro->ubigeo_id);
                $this->esCapitalMesa = $distritoInfo ? (bool)$distritoInfo->es_capital : false;

                // Cargar partidos ordenados por cédula
                $this->partidos = PartidoPolitico::orderBy('orden_cedula', 'asc')->get();

                // 2. BLINDAJE ABSOLUTO: Inicializar TODA la matriz bidimensional con ceros por defecto
                $tipos = ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL'];
                
                foreach ($tipos as $t) {
                    $this->blancos[$t] = 0;
                    $this->nulos[$t] = 0;
                    $this->votosMemoria[$t] = []; // Inicializa el sub-arreglo
                    
                    foreach ($this->partidos as $p) {
                        // Ponemos 0 por defecto para que la llave EXISTA siempre en MySQL/Livewire
                        $this->votosMemoria[$t][$p->id] = 0;
                    }
                }

                // 3. Verificar si el acta ya está oficialmente cerrada
                $this->actaYaCerrada = ActaEscrutinio::where('mesa_sufragio_id', $this->mesaActual->id)
                    ->where('estado_cerrado', true)
                    ->exists();

                $actaBase = ActaEscrutinio::where('mesa_sufragio_id', $this->mesaActual->id)->first();
                $this->estadoVerificacionMesa = $actaBase ? $actaBase->estado_verificacion : 'BORRADOR';

                if ($this->actaYaCerrada) {
                    session()->flash('info_acta', 'MODO AUDITORÍA: El acta oficial se encuentra firmada, transmitida y cerrada.');
                }

                // 4. Cargar los votos reales que ya existan guardados en la base de datos
                $this->cargarVotosMesaGlobal();
            } else {
                session()->flash('error_mesa', 'El número de mesa ingresado no existe.');
            }
        }
    }



    public function updatedTipoEleccion()
    {
        $this->resetErrorBag();
    }

    // PROCESAMIENTO GENERAL CON VALIDACIÓN HORIZONTAL INTEGRAL
        public function procesarYFirmaActa()
    {
        if ($this->actaYaCerrada) return;

        $tiposAEvaluar = $this->esCapitalMesa 
            ? ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL'] 
            : ['GOBERNADOR', 'CONSEJERO', 'ALCALDE_PROVINCIAL', 'ALCALDE_DISTRITAL'];

        $totalesPorEleccion = [];
        $maxHabiles = $this->mesaActual->electores_habiles;

        // 1. Calcular totales individuales
                // 1. Calcular totales individuales controlando llaves ausentes
        foreach ($tiposAEvaluar as $t) {
            // Aseguramos que la llave exista en la matriz antes de sumar, de lo contrario asignamos un arreglo vacío
            $partidosCuerpo = $this->votosMemoria[$t] ?? [];
            $sumaVotosPartido = is_array($partidosCuerpo) ? array_sum($partidosCuerpo) : 0;
            
            $votosBlancos = (int)($this->blancos[$t] ?? 0);
            $votosNulos = (int)($this->nulos[$t] ?? 0);
            
            $totalCuerpo = $sumaVotosPartido + $votosBlancos + $votosNulos;
            $totalesPorEleccion[$t] = $totalCuerpo;

            if ($totalCuerpo > $maxHabiles) {
                $nombreEleccion = str_replace('_', ' ', $t);
                $this->addError('votos_excedidos', "CONTRADICCIÓN EN $nombreEleccion: La suma ($totalCuerpo) excede los electores hábiles ($maxHabiles).");
                return;
            }
        }


        // 2. COMPROBACIÓN DE CONSISTENCIA HORIZONTAL
        $totalPivote = $totalesPorEleccion['GOBERNADOR'];
        $actaCuadrada = true;

        foreach ($tiposAEvaluar as $t) {
            if ($totalesPorEleccion[$t] !== $totalPivote) {
                $actaCuadrada = false;
                break;
            }
        }

        // 3. DETERMINAR ESTADOS SEGÚN EL CUADRANTE
        if ($actaCuadrada) {
            $nuevoEstadoCierre = true; // Se cierra definitivamente
            $nuevoEstadoVerificacion = 'CONFORME';
            session()->flash('success_acta', '¡Éxito! El acta se ha cuadrado horizontalmente con ' . $totalPivote . ' ciudadanos y se cerró con éxito.');
        } else {
            $nuevoEstadoCierre = false; // Se queda ABIERTA para corrección
            $nuevoEstadoVerificacion = 'OBSERVADA'; // ¡Aquí se gatilla la observación en la base de datos!
            session()->flash('warning_acta', 'Acta Grabada como OBSERVADA: Se detectó un descuadre matemático entre los niveles. Enviada al módulo de Auditoría.');
        }

        // 4. GUARDAR MASIVAMENTE EN MYSQL (Así esté descuadrada, guardamos para auditar)
        foreach ($tiposAEvaluar as $t) {
            foreach ($this->votosMemoria[$t] as $partidoId => $cantVotos) {
                ActaEscrutinio::updateOrCreate(
                    [
                        'mesa_sufragio_id' => $this->mesaActual->id,
                        'partido_politico_id' => $partidoId,
                        'tipo_eleccion' => $t
                    ],
                    [
                        'votos_validos' => (int)$cantVotos,
                        'votos_blancos' => (int)$this->blancos[$t],
                        'votos_nulos' => (int)$this->nulos[$t],
                        'estado_cerrado' => $nuevoEstadoCierre,
                        'estado_verificacion' => $nuevoEstadoVerificacion
                    ]
                );
            }
        }

        // Sincronizar la interfaz del semáforo en tiempo real
        $this->estadoVerificacionMesa = $nuevoEstadoVerificacion;
        $this->actaYaCerrada = $nuevoEstadoCierre;
        $this->dispatch('$refresh');
    }


    public function render()
    {
        return view('livewire.admin.electoral.acta-mesa', [
            'historico' => ActaEscrutinio::with(['mesa.centro', 'partido'])->orderBy('id', 'desc')->paginate(15)
        ])->layout('layouts.app');
    }

    
    
}
