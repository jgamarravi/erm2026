<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ControlActasObservadas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $filtro_error = '';

    public function liberarMesaParaRedigitacion($mesa_id)
    {
        // Resetea la mesa a su estado original para que el digitador pueda volver a rellenarla corregida
        DB::transaction(function () use ($mesa_id) {
            DB::table('mesas_sufragio')->where('id', $mesa_id)->update([
                'total_votaron' => null,
                'estado_acta' => 'SIN_DIGITAR',
                'tipo_observacion' => null
            ]);
            // Limpiamos los votos corruptos anteriores asociados
            DB::table('votos_mesas')->where('mesa_sufragio_id', $mesa_id)->delete();
        });

        session()->flash('message', 'Mesa liberada con éxito. Se encuentra disponible para re-digitación en el módulo de escrutinio.');
    }

    public function render()
    {
        $actas_observadas = DB::table('mesas_sufragio')
            ->join('centros_votacion', 'mesas_sufragio.centro_votacion_id', '=', 'centros_votacion.id')
            ->join('ubigeos', 'centros_votacion.ubigeo_id', '=', 'ubigeos.id')
            ->select(
                'mesas_sufragio.id',
                'mesas_sufragio.numero as mesa_numero',
                'mesas_sufragio.electores_habiles',
                'mesas_sufragio.tipo_observacion',
                'ubigeos.nombre as distrito_nombre',
                'ubigeos.region_electoral',
                'centros_votacion.nombre as centro_nombre'
            )
            ->where('mesas_sufragio.estado_acta', 'OBSERVADA')
            ->when($this->filtro_error, function($query) {
                $query->where('mesas_sufragio.tipo_observacion', $this->filtro_error);
            })
            ->paginate(10);

        return view('livewire.control-actas-observadas', compact('actas_observadas'))->layout('layouts.app');
    }
}
