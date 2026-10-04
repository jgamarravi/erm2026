<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\Acta;
use App\Models\Admin\ActaDetalle;
use App\Models\Admin\MesaSufragio;
use App\Models\Admin\PartidoPolitico;
use Illuminate\Support\Facades\DB;

class RegistrarActa extends Component
{
    // Buscador
    public $buscar_mesa = '';
    public $mesas_sugeridas = [];
    public $mesa_seleccionada_texto = '';

    // Cabecera del Acta
    public $mesa_sufragio_id;
    public $total_votantes_mesa = 0;
    public $total_votantes_acta = 0;
    public $estado = 'procesada';
    public $es_capital = false;
    public $region_calculada = null;
    public $centro_votacion_texto = '';
    public $distrito_texto = '';
    public $acta_bloqueada = false;
    public $modo_edicion = false;

    // Votos y Partidos Dinámicos
    public $blancos_gobernador = 0, $nulos_gobernador = 0;
    public $blancos_consejero = 0, $nulos_consejero = 0;
    public $blancos_provincial = 0, $nulos_provincial = 0;
    public $blancos_distrital = 0, $nulos_distrital = 0;

    public $votos_partidos = [];
    public $partidos = []; // Ahora se cargará dinámicamente por mesa/ubigeo

    public function mount()
    {
        // Ya no cargamos PartidoPolitico::all() aquí para evitar pintar partidos sin candidatos al inicio
        $this->partidos = [];
    }

    public function updatedBuscarMesa($value)
    {
        if (strlen($value) < 2) {
            $this->mesas_sugeridas = [];
            return;
        }

        $this->mesas_sugeridas = MesaSufragio::with('centroVotacion.ubigeo')
            ->where('numero_mesa', 'like', '%' . $value . '%')
            ->take(5)
            ->get()
            ->toArray();
    }

    public function seleccionarMesa($id, $numero)
    {
        $this->mesa_sufragio_id = $id;
        $this->mesa_seleccionada_texto = "Mesa N° " . $numero;
        $this->mesas_sugeridas = [];
        $this->buscar_mesa = '';
        $this->modo_edicion = false;

        $mesa = MesaSufragio::with('centroVotacion.ubigeo')->find($id);

        if ($mesa && $mesa->centroVotacion && $mesa->centroVotacion->ubigeo) {
            $this->total_votantes_mesa = $mesa->electores_habiles ?? 0;
            $this->centro_votacion_texto = $mesa->centroVotacion->nombre;

            $ubigeo = $mesa->centroVotacion->ubigeo;
            $this->distrito_texto = $ubigeo->nombre;
            $this->es_capital = (bool) $ubigeo->es_capital;

            // Código INEI String(6)
            $codigoInei = $ubigeo->id;
            $departamento = substr($codigoInei, 0, 2);
            $provinciaId = substr($codigoInei, 0, 4); // 4 dígitos para identificar la provincia

            if ($departamento === '14') {
                $this->region_calculada = (substr($codigoInei, 2, 2) === '01') ? 'Lima Metropolitana' : 'Lima Provincias';
            } else {
                $this->region_calculada = $ubigeo->region_electoral ?? 'Otra Región';
            }

            // =========================================================================
            // REQUERIMIENTO: Cargar SOLO los partidos que tienen candidatos en este Ubigeo
            // =========================================================================

            $this->partidos = PartidoPolitico::whereHas('ubigeos', function ($q) use ($codigoInei) {
                $q->where('ubigeos.id', $codigoInei);
            })->orderBy('orden_cedula', 'asc')->get();

            // Inicializamos el contenedor de votos en base a la lista recortada y correcta
            $this->votos_partidos = [];
            foreach ($this->partidos as $partido) {
                $this->votos_partidos[$partido->id] = [
                    'gobernador' => 0,
                    'consejero' => 0,
                    'provincial' => 0,
                    'distrital' => 0,
                ];
            }

            // =========================================================================

            // Verificar si el acta ya existe para cargar datos históricos o bloquear
            $actaExistente = Acta::with('detalles')->where('mesa_sufragio_id', $id)->first();

            if ($actaExistente) {
                $this->acta_bloqueada = true;
                $this->estado = $actaExistente->estado;
                $this->total_votantes_acta = $actaExistente->total_votantes_acta;

                $this->blancos_gobernador = $actaExistente->blancos_gobernador;
                $this->nulos_gobernador = $actaExistente->nulos_gobernador;
                $this->blancos_consejero = $actaExistente->blancos_consejero;
                $this->nulos_consejero = $actaExistente->nulos_consejero;
                $this->blancos_provincial = $actaExistente->blancos_provincial;
                $this->nulos_provincial = $actaExistente->nulos_provincial;
                $this->blancos_distrital = $actaExistente->blancos_distrital;
                $this->nulos_distrital = $actaExistente->nulos_distrital;

                foreach ($actaExistente->detalles as $detalle) {
                    if (isset($this->votos_partidos[$detalle->partido_politico_id])) {
                        $this->votos_partidos[$detalle->partido_politico_id] = [
                            'gobernador' => $detalle->votos_gobernador,
                            'consejero' => $detalle->votos_consejero,
                            'provincial' => $detalle->votos_provincial,
                            'distrital' => $detalle->votos_distrital,
                        ];
                    }
                }
            }

            if ($this->es_capital) {
                $this->blancos_distrital = 0;
                $this->nulos_distrital = 0;
                foreach ($this->votos_partidos as $partido_id => $votos) {
                    $this->votos_partidos[$partido_id]['distrital'] = 0;
                }
            }
        }
    }

    public function guardarActa()
    {
        if ($this->acta_bloqueada) {
            session()->flash('error', 'No se puede modificar un acta cerrada.');
            return;
        }

        $totalActa = (int) $this->total_votantes_acta;
        if ($totalActa <= 0 || $totalActa > (int) $this->total_votantes_mesa) {
            session()->flash('error', 'El total de votantes del acta es inválido o supera al de la mesa.');
            return;
        }

        // El procesamiento de sumas solo tomará en cuenta los partidos cargados dinámicamente
        $votosGobernadorPartidos = array_map('intval', array_column($this->votos_partidos, 'gobernador'));
        $votosConsejeroPartidos = array_map('intval', array_column($this->votos_partidos, 'consejero'));
        $votosProvincialPartidos = array_map('intval', array_column($this->votos_partidos, 'provincial'));
        $votosDistritalPartidos = array_map('intval', array_column($this->votos_partidos, 'distrital'));

        $suma_gobernador = (int) $this->blancos_gobernador + (int) $this->nulos_gobernador + array_sum($votosGobernadorPartidos);
        $suma_consejero = (int) $this->blancos_consejero + (int) $this->nulos_consejero + array_sum($votosConsejeroPartidos);
        $suma_provincial = (int) $this->blancos_provincial + (int) $this->nulos_provincial + array_sum($votosProvincialPartidos);
        $suma_distrital = $this->es_capital ? $totalActa : ((int) $this->blancos_distrital + (int) $this->nulos_distrital + array_sum($votosDistritalPartidos));

        if ($suma_gobernador !== $totalActa || $suma_consejero !== $totalActa || $suma_provincial !== $totalActa || $suma_distrital !== $totalActa) {
            session()->flash('error', "Error de cuadre numérico.");
            return;
        }

        DB::beginTransaction();
        try {
            $acta = Acta::updateOrCreate(
                [
                    'mesa_sufragio_id' => $this->mesa_sufragio_id,
                    'estado' => $this->estado,
                    'user_id' => auth()->id(),
                    'resolved_by_user_id' => auth()->id(),
                    'region_electoral' => $this->region_calculada,
                    'total_votantes_acta' => $totalActa,
                    'blancos_gobernador' => $this->blancos_gobernador,
                    'nulos_gobernador' => $this->nulos_gobernador,
                    'blancos_consejero' => $this->blancos_consejero,
                    'nulos_consejero' => $this->nulos_consejero,
                    'blancos_provincial' => $this->blancos_provincial,
                    'nulos_provincial' => $this->nulos_provincial,
                    'blancos_distrital' => $this->blancos_distrital,
                    'nulos_distrital' => $this->nulos_distrital,
                ]
            );

            ActaDetalle::where('acta_id', $acta->id)->delete();

            foreach ($this->votos_partidos as $partido_id => $votos) {
                ActaDetalle::create([
                    'acta_id' => $acta->id,
                    'partido_politico_id' => $partido_id,
                    'votos_gobernador' => (int) $votos['gobernador'],
                    'votos_consejero' => (int) $votos['consejero'],
                    'votos_provincial' => (int) $votos['provincial'],
                    'votos_distrital' => $this->es_capital ? 0 : (int) $votos['distrital'],
                ]);
            }

            DB::commit();
            session()->flash('success', 'Acta guardada correctamente.');
            $this->limpiarMesa();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error en BD: ' . $e->getMessage());
        }
    }
    public function habilitarEdicion()
    {
        $this->acta_bloqueada = false;
        $this->modo_edicion = true;
    }
    public function limpiarMesa()
    {
        $this->reset([
            'mesa_sufragio_id',
            'mesa_seleccionada_texto',
            'buscar_mesa',
            'total_votantes_mesa',
            'centro_votacion_texto',
            'distrito_texto',
            'region_calculada',
            'es_capital',
            'acta_bloqueada',
            'modo_edicion',
            'total_votantes_acta',
            'blancos_gobernador',
            'nulos_gobernador',
            'blancos_consejero',
            'nulos_consejero',
            'blancos_provincial',
            'nulos_provincial',
            'blancos_distrital',
            'nulos_distrital',
            'partidos',
            'votos_partidos'
        ]);
        $this->estado = 'procesada';
    }
    public function render()
    {
        return view('livewire.admin.electoral.registrar-acta')->layout('layouts.app');
    }
}

/* class RegistrarActa extends Component
{
    // Buscador
    public $buscar_mesa = '';
    public $mesas_sugeridas = [];
    public $mesa_seleccionada_texto = '';

    // Cabecera del Acta
    public $mesa_sufragio_id;
    public $total_votantes_mesa = 0;
    public $total_votantes_acta = 0;
    public $estado = 'procesada';
    public $es_capital = false;
    public $region_calculada = null;
    public $centro_votacion_texto = '';
    public $distrito_texto = '';

    // Estados de Control
    public $acta_bloqueada = false;
    public $modo_edicion = false; // Controla si se forzó la apertura del acta

    // Votos
    public $blancos_gobernador = 0, $nulos_gobernador = 0;
    public $blancos_consejero = 0, $nulos_consejero = 0;
    public $blancos_provincial = 0, $nulos_provincial = 0;
    public $blancos_distrital = 0, $nulos_distrital = 0;
    public $votos_partidos = [];
    public $partidos = [];

    public function mount()
    {
        // REQUERIMIENTO: Listar los partidos ordenados por orden_cedula
        $this->partidos = PartidoPolitico::orderBy('orden_cedula', 'asc')->get();
        $this->inicializarVotosPartidos();
    }

    private function inicializarVotosPartidos()
    {
        foreach ($this->partidos as $partido) {
            $this->votos_partidos[$partido->id] = [
                'gobernador' => 0,
                'consejero' => 0,
                'provincial' => 0,
                'distrital' => 0,
            ];
        }
    }

    public function updatedBuscarMesa($value)
    {
        if (strlen($value) < 2) {
            $this->mesas_sugeridas = [];
            return;
        }

        $this->mesas_sugeridas = MesaSufragio::with('centroVotacion.ubigeo')
            ->where('numero_mesa', 'like', '%' . $value . '%')
            ->take(5)
            ->get()
            ->toArray();
    }

    public function seleccionarMesa($id, $numero)
    {
        $this->mesa_sufragio_id = $id;
        $this->mesa_seleccionada_texto = "Mesa N° " . $numero;
        $this->mesas_sugeridas = [];
        $this->buscar_mesa = '';
        $this->modo_edicion = false;
        $this->region_calculada ='';

        $mesa = MesaSufragio::with('centroVotacion.ubigeo')->find($id);

        if ($mesa) {
            $this->total_votantes_mesa = $mesa->electores_habiles ?? 0;

            if ($mesa->centroVotacion) {
                $this->centro_votacion_texto = $mesa->centroVotacion->nombre;
                if ($mesa->centroVotacion->ubigeo) {
                    $ubigeo = $mesa->centroVotacion->ubigeo;
                    $this->distrito_texto = $ubigeo->nombre;
                    $this->es_capital = (bool) $ubigeo->es_capital;

                    $codigoInei = $ubigeo->id;
                    $departamento = substr($codigoInei, 0, 2);
                    $provincia = substr($codigoInei, 2, 2);

                     if ($departamento === '15') {
                        $this->region_calculada = ($provincia === '01') ? 'Lima Metropolitana' : 'Lima Provincias';
                    } else {
                        $this->region_calculada = $ubigeo->region_electoral ?? 'Otra Región';

                    $this->region_calculada = $ubigeo->region_electoral ?? '';
                }
            }

            $actaExistente = Acta::with('detalles')->where('mesa_sufragio_id', $id)->first();

            if ($actaExistente) {
                $this->acta_bloqueada = true;
                $this->estado = $actaExistente->estado;
                $this->total_votantes_acta = $actaExistente->total_votantes_acta;

                $this->blancos_gobernador = $actaExistente->blancos_gobernador;
                $this->nulos_gobernador = $actaExistente->nulos_gobernador;
                $this->blancos_consejero = $actaExistente->blancos_consejero;
                $this->nulos_consejero = $actaExistente->nulos_consejero;
                $this->blancos_provincial = $actaExistente->blancos_provincial;
                $this->nulos_provincial = $actaExistente->nulos_provincial;
                $this->blancos_distrital = $actaExistente->blancos_distrital;
                $this->nulos_distrital = $actaExistente->nulos_distrital;

                foreach ($actaExistente->detalles as $detalle) {
                    if (isset($this->votos_partidos[$detalle->partido_politico_id])) {
                        $this->votos_partidos[$detalle->partido_politico_id] = [
                            'gobernador' => $detalle->votos_gobernador,
                            'consejero' => $detalle->votos_consejero,
                            'provincial' => $detalle->votos_provincial,
                            'distrital' => $detalle->votos_distrital,
                        ];
                    }
                }
            } else {
                $this->acta_bloqueada = false;
                $this->estado = 'procesada';
                $this->total_votantes_acta = 0;
                $this->blancos_gobernador = $this->nulos_gobernador = 0;
                $this->blancos_consejero = $this->nulos_consejero = 0;
                $this->blancos_provincial = $this->nulos_provincial = 0;
                $this->blancos_distrital = $this->nulos_distrital = 0;
                $this->inicializarVotosPartidos();
            }
        }
    }

    // REQUERIMIENTO: Botón para quitar el estado de cerrada y permitir edición
    public function habilitarEdicion()
    {
        $this->acta_bloqueada = false;
        $this->modo_edicion = true;
        session()->flash('success', 'El acta ha sido desbloqueada. Puede corregir los valores y volver a guardar.');
    }

    public function guardarActa()
    {
        $totalActa = (int) $this->total_votantes_acta;

        if ($totalActa <= 0) {
            session()->flash('error', 'El total de votantes en el acta debe ser mayor a cero.');
            return;
        }

        if ($totalActa > (int) $this->total_votantes_mesa) {
            session()->flash('error', 'El total de votantes del acta no puede superar al de la mesa.');
            return;
        }

        $votosGobernadorPartidos = array_map('intval', array_column($this->votos_partidos, 'gobernador'));
        $votosConsejeroPartidos = array_map('intval', array_column($this->votos_partidos, 'consejero'));
        $votosProvincialPartidos = array_map('intval', array_column($this->votos_partidos, 'provincial'));
        $votosDistritalPartidos = array_map('intval', array_column($this->votos_partidos, 'distrital'));

        $suma_gobernador = (int) $this->blancos_gobernador + (int) $this->nulos_gobernador + array_sum($votosGobernadorPartidos);
        $suma_consejero = (int) $this->blancos_consejero + (int) $this->nulos_consejero + array_sum($votosConsejeroPartidos);
        $suma_provincial = (int) $this->blancos_provincial + (int) $this->nulos_provincial + array_sum($votosProvincialPartidos);
        $suma_distrital = $this->es_capital ? $totalActa : ((int) $this->blancos_distrital + (int) $this->nulos_distrital + array_sum($votosDistritalPartidos));

        if ($suma_gobernador !== $totalActa || $suma_consejero !== $totalActa || $suma_provincial !== $totalActa || $suma_distrital !== $totalActa) {
            session()->flash('error', "Error de cuadre numérico. Total Acta: {$totalActa}. Sumas -> Gob: {$suma_gobernador}, Cons: {$suma_consejero}, Prov: {$suma_provincial}, Dist: {$suma_distrital}.");
            return;
        }

        DB::beginTransaction();

        try {
            // Modificado para soportar edición mediante updateOrCreate
            $acta = Acta::updateOrCreate(
                [
                    'mesa_sufragio_id' => $this->mesa_sufragio_id,
                    'estado' => $this->estado,
                    'region_electoral' => $this->region_calculada,
                    'total_votantes_acta' => $totalActa,
                    'blancos_gobernador' => $this->blancos_gobernador,
                    'nulos_gobernador' => $this->nulos_gobernador,
                    'blancos_consejero' => $this->blancos_consejero,
                    'nulos_consejero' => $this->nulos_consejero,
                    'blancos_provincial' => $this->blancos_provincial,
                    'nulos_provincial' => $this->nulos_provincial,
                    'blancos_distrital' => $this->blancos_distrital,
                    'nulos_distrital' => $this->nulos_distrital,
                ]
            );

            // Si es edición, limpiamos los detalles antiguos para sobrescribirlos
            ActaDetalle::where('acta_id', $acta->id)->delete();

            foreach ($this->votos_partidos as $partido_id => $votos) {
                ActaDetalle::create([
                    'acta_id' => $acta->id,
                    'partido_politico_id' => $partido_id,
                    'votos_gobernador' => (int) $votos['gobernador'],
                    'votos_consejero' => (int) $votos['consejero'],
                    'votos_provincial' => (int) $votos['provincial'],
                    'votos_distrital' => $this->es_capital ? 0 : (int) $votos['distrital'],
                ]);
            }

            DB::commit();
            session()->flash('success', 'Acta electoral guardada y actualizada correctamente.');
            $this->limpiarMesa();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error en almacenamiento de BD: ' . $e->getMessage());
        }
    }

    public function limpiarMesa()
    {
        $this->reset([
            'mesa_sufragio_id',
            'mesa_seleccionada_texto',
            'buscar_mesa',
            'total_votantes_mesa',
            'centro_votacion_texto',
            'distrito_texto',
            'region_calculada',
            'es_capital',
            'acta_bloqueada',
            'modo_edicion',
            'total_votantes_acta',
            'blancos_gobernador',
            'nulos_gobernador',
            'blancos_consejero',
            'nulos_consejero',
            'blancos_provincial',
            'nulos_provincial',
            'blancos_distrital',
            'nulos_distrital',
        ]);
        $this->estado = 'procesada';
        $this->inicializarVotosPartidos();
    }
    public function render()
    {
        return view('livewire.admin.electoral.registrar-acta')->layout('layouts.app');
    }
}
 */