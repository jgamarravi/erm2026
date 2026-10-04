<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DigitacionActa extends Component
{
    public $numero_mesa;
    public $mesa = null;
    public $personas_votaron = '';

    // Estado de seguridad de congelamiento
    public $acta_bloqueada = false;
    public $busqueda_mesa = '';
    public $sugerencias_mesas = [];
    public $nombre_ubigeo_completo = '';
    public $partidos_maestros = [];
    public $inscritos_gobernador = [], $inscritos_consejero = [], $inscritos_provincial = [], $inscritos_distrital = [];

    public $votos_g = [], $votos_c = [], $votos_p = [], $votos_d = [];
    public $blancos = ['GOBERNADOR' => 0, 'CONSEJERO' => 0, 'PROVINCIAL' => 0, 'DISTRITAL' => 0];
    public $nulos = ['GOBERNADOR' => 0, 'CONSEJERO' => 0, 'PROVINCIAL' => 0, 'DISTRITAL' => 0];

    public $col_g_activa = false, $col_c_activa = false, $col_p_activa = false, $col_d_activa = false;
    public $total_g = 0, $total_c = 0, $total_p = 0, $total_d = 0;
    public $alerta_g = false, $alerta_c = false, $alerta_p = false, $alerta_d = false;

    public function buscarMesa()
    {
        $this->resetValidation();
        $this->validate(['numero_mesa' => 'required']);

        $this->mesa = DB::table('mesas_sufragio')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
            ->select(
                'mesas_sufragio.id',
                'mesas_sufragio.electores_habiles',
                'mesas_sufragio.total_votaron',
                'mesas_sufragio.estado_acta', // Evaluamos el estado guardado
                'centros_votacion.ubigeo_id',
                'centros_votacion.nombre as centro_nombre',
                'ubigeos.region_electoral',
                'ubigeos.nombre as distrito_nombre',
                'ubigeos.es_capital'
            )
            ->where('mesas_sufragio.numero_mesa', $this->numero_mesa)
            ->first();

        if (!$this->mesa) {
            // Si la mesa no existe, limpiamos el esqueleto para evitar que se quede pegada la grilla anterior
            $this->reset(['mesa', 'personas_votaron', 'acta_bloqueada', 'partidos_maestros']);
            session()->flash('error', 'Mesa de votación no localizada.');
            return;
        }

        // CONTROL DE CONGELAMIENTO AUTOMÁTICO REQUERIDO
        $this->personas_votaron = $this->mesa->total_votaron ?? '';
        $this->acta_bloqueada = ($this->mesa->estado_acta === 'COMPUTADA');

        $ubigeo_dist = $this->mesa->ubigeo_id;
        $ubigeo_prov = substr($ubigeo_dist, 0, 4) . '00';
        $prov_nombre = DB::table('ubigeos')->where('id', $ubigeo_prov)->value('nombre') ?? '';
        $this->nombre_ubigeo_completo = "{$this->mesa->region_electoral} / {$prov_nombre} / {$this->mesa->distrito_nombre}";

        if ($this->mesa->region_electoral === 'LIMA METROPOLITANA') {
            $this->inscritos_gobernador = [];
            $this->inscritos_consejero = [];
        } else {
            $this->inscritos_gobernador = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'GOBERNADOR')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();
            $this->inscritos_consejero = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'CONSEJERO')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();
        }

        $this->inscritos_provincial = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'PROVINCIAL')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();

        if ((int) $this->mesa->es_capital === 1) {
            $this->inscritos_distrital = [];
        } else {
            $this->inscritos_distrital = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'DISTRITAL')->where('ubigeo_id', $ubigeo_dist)->pluck('partido_politico_id')->toArray();
        }

        $this->col_g_activa = count($this->inscritos_gobernador) > 0;
        $this->col_c_activa = count($this->inscritos_consejero) > 0;
        $this->col_p_activa = count($this->inscritos_provincial) > 0;
        $this->col_d_activa = count($this->inscritos_distrital) > 0;

        $partidos_ids = array_unique(array_merge($this->inscritos_gobernador, $this->inscritos_consejero, $this->inscritos_provincial, $this->inscritos_distrital));
        $this->partidos_maestros = DB::table('partidos_politicos')->whereIn('id', $partidos_ids)->select('id', 'nombre', 'logo_url')->orderBy('orden_cedula')->get()->toArray();

        $votos_existentes = DB::table('votos_mesas')->where('mesa_sufragio_id', $this->mesa->id)->get();

        foreach ($this->partidos_maestros as $partido) {
            $reg_g = $votos_existentes->where('tipo_eleccion', 'GOBERNADOR')->where('partido_politico_id', $partido->id)->first();
            $this->votos_g[$partido->id] = in_array($partido->id, $this->inscritos_gobernador) ? ($reg_g->cantidad_votos ?? 0) : null;

            $reg_c = $votos_existentes->where('tipo_eleccion', 'CONSEJERO')->where('partido_politico_id', $partido->id)->first();
            $this->votos_c[$partido->id] = in_array($partido->id, $this->inscritos_consejero) ? ($reg_c->cantidad_votos ?? 0) : null;

            $reg_p = $votos_existentes->where('tipo_eleccion', 'PROVINCIAL')->where('partido_politico_id', $partido->id)->first();
            $this->votos_p[$partido->id] = in_array($partido->id, $this->inscritos_provincial) ? ($reg_p->cantidad_votos ?? 0) : null;

            $reg_d = $votos_existentes->where('tipo_eleccion', 'DISTRITAL')->where('partido_politico_id', $partido->id)->first();
            $this->votos_d[$partido->id] = in_array($partido->id, $this->inscritos_distrital) ? ($reg_d->cantidad_votos ?? 0) : null;
        }

        $tipos = ['GOBERNADOR', 'CONSEJERO', 'PROVINCIAL', 'DISTRITAL'];
        foreach ($tipos as $tipo) {
            $blanco_bd = $votos_existentes->where('tipo_eleccion', $tipo)->where('voto_especial', 'BLANCO')->where('partido_politico_id', null)->first();
            $this->blancos[$tipo] = $blanco_bd->cantidad_votos ?? 0;

            $nulo_bd = $votos_existentes->where('tipo_eleccion', $tipo)->where('voto_especial', 'NULO')->where('partido_politico_id', null)->first();
            $this->nulos[$tipo] = $nulo_bd->cantidad_votos ?? 0;
        }

        $this->calcularTotales();
    }

    // ACCIÓN ADMINISTRATIVA DE REAPERTURA (NUEVO)
    public function desbloquearActa()
    {
        DB::table('mesas_sufragio')->where('id', $this->mesa->id)->update([
            'estado_acta' => 'SIN_DIGITAR'
        ]);
        $this->acta_bloqueada = false;
        session()->flash('message', 'Acta liberada. Los campos se encuentran habilitados para edición.');
    }


    // =========================================================================
    // DESDE EL MÉTODO updated() EN ADELANTE
    // =========================================================================

    public function updated($propertyName)
    {
        // Aborta cualquier hook reactivo si el acta se encuentra cerrada/bloqueada
        if ($this->acta_bloqueada) {
            return;
        }

        // 1. Validar techo de electores hábiles si cambia la asistencia inicial
        if ($propertyName === 'personas_votaron' && !empty($this->personas_votaron)) {
            if ($this->personas_votaron > $this->mesa->electores_habiles) {
                $this->addError('personas_votaron', "Excede la capacidad de la mesa ({$this->mesa->electores_habiles}).");
            } else {
                $this->resetValidation('personas_votaron');
            }
        }

        // 2. VALIDADOR DE MÁXIMO POR CELDA INDIVIDUAL
        if (preg_match('/^(votos_g|votos_c|votos_p|votos_d|blancos|nulos)\./', $propertyName)) {
            $valor_celda = (int) $this->getPropertyValue($propertyName);
            $limite = !empty($this->personas_votaron) ? (int) $this->personas_votaron : (int) $this->mesa->electores_habiles;

            if ($valor_celda > $limite) {
                $this->addError($propertyName, "No puede superar el total de votantes ({$limite}).");
            } else {
                $this->resetValidation($propertyName);
            }
        }

        // Recalcular la sumatoria dinámica de las 4 columnas
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        $asistencia = !empty($this->personas_votaron) ? (int) $this->personas_votaron : 0;

        $suma_partidos_g = array_sum(array_filter($this->votos_g));
        $suma_partidos_c = array_sum(array_filter($this->votos_c));
        $suma_partidos_p = array_sum(array_filter($this->votos_p));
        $suma_partidos_d = array_sum(array_filter($this->votos_d));

        // Activar alertas amarillas si la pura suma de partidos ya superó al padrón
        $this->alerta_g = ($asistencia > 0 && $suma_partidos_g > $asistencia);
        $this->alerta_c = ($asistencia > 0 && $suma_partidos_c > $asistencia);
        $this->alerta_p = ($asistencia > 0 && $suma_partidos_p > $asistencia);
        $this->alerta_d = ($asistencia > 0 && $suma_partidos_d > $asistencia);

        // Sumatorias transversales de control (Partidos + Blancos + Nulos)
        $this->total_g = $this->col_g_activa ? ($suma_partidos_g + (int) $this->blancos['GOBERNADOR'] + (int) $this->nulos['GOBERNADOR']) : $asistencia;
        $this->total_c = $this->col_c_activa ? ($suma_partidos_c + (int) $this->blancos['CONSEJERO'] + (int) $this->nulos['CONSEJERO']) : $asistencia;
        $this->total_p = $this->col_p_activa ? ($suma_partidos_p + (int) $this->blancos['PROVINCIAL'] + (int) $this->nulos['PROVINCIAL']) : $asistencia;
        $this->total_d = $this->col_d_activa ? ($suma_partidos_d + (int) $this->blancos['DISTRITAL'] + (int) $this->nulos['DISTRITAL']) : $asistencia;
    }

    public function guardarActa()
    {
        // Seguridad preventiva: si ya está bloqueada, no procesa la transacción
        if ($this->acta_bloqueada) {
            return;
        }

        $this->resetValidation();

        $this->validate([
            'personas_votaron' => 'required|integer|min:1'
        ]);

        if ($this->personas_votaron > $this->mesa->electores_habiles) {
            $this->addError('personas_votaron', "El número de votantes excede la capacidad total de la mesa.");
            return;
        }

        if ($this->getErrorBag()->any()) {
            session()->flash('error_cuadre', "Corrija las celdas marcadas en rojo antes de guardar.");
            return;
        }

        // VALIDACIÓN DE CONSISTENCIA JNE CONTRA ASISTENCIA DEL PADRÓN
        $asistencia = (int) $this->personas_votaron;
        if ($this->total_g !== $asistencia || $this->total_c !== $asistencia || $this->total_p !== $asistencia || $this->total_d !== $asistencia) {
            session()->flash('error_cuadre', "Error Matemático: Una o más columnas activas no igualan las {$asistencia} firmas registradas.");
            return;
        }

        // TRANSACCIÓN DE PERSISTENCIA Y CIERRE ELECTORAL DE MESA
        DB::transaction(function () {
            // Actualizamos la asistencia y cerramos la mesa cambiando el estado a COMPUTADA
            DB::table('mesas_sufragio')->where('id', $this->mesa->id)->update([
                'total_votaron' => $this->personas_votaron,
                'estado_acta' => 'COMPUTADA'
            ]);

            $config = [
                'GOBERNADOR' => ['matriz' => $this->votos_g, 'activa' => $this->col_g_activa],
                'CONSEJERO' => ['matriz' => $this->votos_c, 'activa' => $this->col_c_activa],
                'PROVINCIAL' => ['matriz' => $this->votos_p, 'activa' => $this->col_p_activa],
                'DISTRITAL' => ['matriz' => $this->votos_d, 'activa' => $this->col_d_activa],
            ];

            foreach ($config as $tipo => $datos) {
                if (!$datos['activa']) {
                    continue;
                }

                // Guardar los votos de los partidos políticos regulares
                foreach ($datos['matriz'] as $partido_id => $votos) {
                    if ($votos !== null && $votos !== '') {
                        DB::table('votos_mesas')->updateOrInsert(
                            ['mesa_sufragio_id' => $this->mesa->id, 'partido_politico_id' => $partido_id, 'tipo_eleccion' => $tipo],
                            ['cantidad_votos' => (int) $votos, 'voto_especial' => 'REGULAR']
                        );
                    }
                }

                // Guardar Votos en Blanco
                DB::table('votos_mesas')->updateOrInsert(
                    ['mesa_sufragio_id' => $this->mesa->id, 'partido_politico_id' => null, 'tipo_eleccion' => $tipo, 'voto_especial' => 'BLANCO'],
                    ['cantidad_votos' => (int) ($this->blancos[$tipo] ?? 0)]
                );

                // Guardar Votos Nulos
                DB::table('votos_mesas')->updateOrInsert(
                    ['mesa_sufragio_id' => $this->mesa->id, 'partido_politico_id' => null, 'tipo_eleccion' => $tipo, 'voto_especial' => 'NULO'],
                    ['cantidad_votos' => (int) ($this->nulos[$tipo] ?? 0)]
                );
            }
        });

        // Congelar controles en el front-end inmediatamente tras el éxito
        $this->acta_bloqueada = true;
        session()->flash('message', 'Acta guardada, cuadrada y CERRADA formalmente en el sistema.');
    }

    public function render()
    {
        return view('livewire.digitacion-acta')->layout('layouts.app');
    }

    public function updatedBusquedaMesa($value)
    {
        if (strlen($value) < 2) {
            $this->sugerencias_mesas = [];
            return;
        }
        // Buscador Inteligente: Trae coincidencias por número de mesa o nombre de local/distrito
        $this->sugerencias_mesas = DB::table('mesas_sufragio')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
            ->select(
                'mesas_sufragio.numero_mesa',
                'mesas_sufragio.estado_acta',
                'centros_votacion.nombre as local_nombre',
                'ubigeos.nombre as distrito_nombre'
            )
            ->where('mesas_sufragio.numero_mesa', 'like', $value . '%')
            ->orWhere('centros_votacion.nombre', 'like', '%' . $value . '%')
            ->orderBy('mesas_sufragio.numero_mesa')
            ->take(5) // Limitamos a 5 sugerencias para cuidar el rendimiento de la UI
            ->get()
            ->toArray();
    }

    // Método intermedio para cargar la mesa desde el panel de sugerencias inteligentes
    public function seleccionarMesaSugerida($numero)
    {
        $this->numero_mesa = $numero;
        $this->busqueda_mesa = $numero;
        $this->sugerencias_mesas = [];
        $this->buscarMesa(); // Dispara tu método nativo de carga general
    }

}
