<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AuditoriaInscripciones extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Filtros de Auditoría Inversa
    public $partido_id_sel = ''; // <-- NUEVO SELECT DE PARTIDOS
    public $filtro_tipo = 'GOBERNADOR'; // Gobernador por defecto para auditar coberturas masivas
    public $search_ubigeo = '';

    // Colección para el combo
    public $partidos_maestros = [];

    public function mount()
    {
        // Cargamos todos los partidos políticos de la BD para el nuevo selector
        $this->partidos_maestros = DB::table('partidos_politicos')
            ->select('id', 'nombre', 'logo_url')
            ->orderBy('nombre')
            ->get()
            ->toArray();

        // Inicializamos con el primer partido si existe para no mostrar la grilla vacía
        if (count($this->partidos_maestros) > 0) {
            $this->partido_id_sel = $this->partidos_maestros[0]->id;
        }
    }

    public function updatingPartidoIdSel()
    {
        $this->resetPage();
    }
    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }
    public function updatingSearchUbigeo()
    {
        $this->resetPage();
    }

    public function eliminarAsignacion($ubigeo_id, $partido_id)
    {
        // Purgamos la habilitación específica según el nivel seleccionado
        DB::table('inscripciones_partidos')
            ->where('ubigeo_id', $ubigeo_id)
            ->where('partido_politico_id', $partido_id)
            ->where('tipo_eleccion', $this->filtro_tipo)
            ->delete();

        session()->flash('message_delete', 'La cédula seleccionada ha sido revocada de este Ubigeo.');
    }

    public function render()
{
    $partido_activo = $this->partido_id_sel;
    $tipo_busqueda = $this->filtro_tipo;

    // CONSULTA CONTROLADA CON REGLAS DE HERENCIA DIRECTA JNE
    $query = DB::table('ubigeos')
        ->select(
            'ubigeos.id as ubigeo_codigo',
            'ubigeos.nombre as ubigeo_nombre',
            'ubigeos.region_electoral',
            DB::raw("'$partido_activo' as partido_id"),
            
            // Flags dinámicos para pintar los badges de la vista Blade de forma limpia
            DB::raw("(SELECT COUNT(*) FROM inscripciones_partidos WHERE partido_politico_id = '$partido_activo' AND tipo_eleccion = 'GOBERNADOR' AND (ubigeo_id = ubigeos.id OR ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00') OR ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 2), '0000'))) as tiene_gobernador"),
            DB::raw("(SELECT COUNT(*) FROM inscripciones_partidos WHERE partido_politico_id = '$partido_activo' AND tipo_eleccion = 'CONSEJERO' AND (ubigeo_id = ubigeos.id OR ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00'))) as tiene_consejero"),
            DB::raw("(SELECT COUNT(*) FROM inscripciones_partidos WHERE partido_politico_id = '$partido_activo' AND tipo_eleccion = 'PROVINCIAL' AND (ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00') OR ubigeo_id = (SELECT id FROM ubigeos WHERE id LIKE CONCAT(SUBSTRING(ubigeos.id, 1, 4), '%') AND es_capital = 1 LIMIT 1))) as tiene_provincial"),
            DB::raw("(SELECT COUNT(*) FROM inscripciones_partidos WHERE partido_politico_id = '$partido_activo' AND tipo_eleccion = 'DISTRITAL' AND ubigeo_id = ubigeos.id) as tiene_distrital")
        )
        // Filtro estricto en el WHERE para asegurar la herencia jurídica por tipo de elección
        ->where(function($q) use ($partido_activo, $tipo_busqueda) {
            if ($tipo_busqueda === 'GOBERNADOR') {
                $q->whereExists(function($sub) use ($partido_activo) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones_partidos')
                        ->where('inscripciones_partidos.partido_politico_id', $partido_activo)
                        ->where('inscripciones_partidos.tipo_eleccion', 'GOBERNADOR')
                        ->whereRaw("(inscripciones_partidos.ubigeo_id = ubigeos.id OR inscripciones_partidos.ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00') OR inscripciones_partidos.ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 2), '0000'))");
                });
            } elseif ($tipo_busqueda === 'CONSEJERO') {
                $q->whereExists(function($sub) use ($partido_activo) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones_partidos')
                        ->where('inscripciones_partidos.partido_politico_id', $partido_activo)
                        ->where('inscripciones_partidos.tipo_eleccion', 'CONSEJERO')
                        ->whereRaw("(inscripciones_partidos.ubigeo_id = ubigeos.id OR inscripciones_partidos.ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00'))");
                });
            } elseif ($tipo_busqueda === 'PROVINCIAL') {
                $q->whereExists(function($sub) use ($partido_activo) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones_partidos')
                        ->where('inscripciones_partidos.partido_politico_id', $partido_activo)
                        ->where('inscripciones_partidos.tipo_eleccion', 'PROVINCIAL')
                        ->whereRaw("(inscripciones_partidos.ubigeo_id = CONCAT(SUBSTRING(ubigeos.id, 1, 4), '00') OR inscripciones_partidos.ubigeo_id = (SELECT id FROM ubigeos WHERE id LIKE CONCAT(SUBSTRING(ubigeos.id, 1, 4), '%') AND es_capital = 1 LIMIT 1))");
                });
            } elseif ($tipo_busqueda === 'DISTRITAL') {
                $q->whereExists(function($sub) use ($partido_activo) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones_partidos')
                        ->where('inscripciones_partidos.partido_politico_id', $partido_activo)
                        ->where('inscripciones_partidos.tipo_eleccion', 'DISTRITAL')
                        ->whereRaw("inscripciones_partidos.ubigeo_id = ubigeos.id");
                });
            }
        })
        // Filtro dinámico por el texto ingresado en el buscador (Ej: "PARAMONGA")
        ->when($this->search_ubigeo, function($q) {
            $q->where(function($sub) {
                $sub->where('ubigeos.nombre', 'like', '%' . $this->search_ubigeo . '%')
                    ->orWhere('ubigeos.id', 'like', $this->search_ubigeo . '%');
            });
        })
        ->orderBy('ubigeos.id')
        ->paginate(15);

    $partido_info = DB::table('partidos_politicos')->where('id', $partido_activo)->first();

    return view('livewire.auditoria-inscripciones', [
        'inscripciones' => $query,
        'partido_nombre' => $partido_info->nombre ?? 'SELECCIONADO',
        'partido_logo' => $partido_info->logo_url ?? null
    ])->layout('layouts.app');
}

}






