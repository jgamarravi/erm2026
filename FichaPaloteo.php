<?php

// app/Livewire/FichaPaloteo.php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FichaPaloteo extends Component
{
    // Selectores en cascada
    public $region_electoral_sel = 'LIMA PROVINCIAS'; 
    public $provincia_id = '';
    public $distrito_id = '';

    public $frecuencia = 0;

    // Colecciones para renderizar selectores
    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];

    // Banderas estructurales de la cédula
    public $col_g_activa = false;
    public $col_c_activa = false;
    public $col_p_activa = false;
    public $col_d_activa = false;
    public $partidos_maestros = [];
    public $ubigeo_seleccionado = null;

    public function mount()
    {
        $this->departamentos = DB::table('ubigeos')
            ->whereNotNull('region_electoral')
            ->distinct()
            ->pluck('region_electoral')
            ->toArray();
            
        $this->cargarProvincias();
    }

    public function updatedRegionElectoralSel($value)
    {
        $this->reset(['provincia_id', 'distrito_id', 'provincias', 'distritos', 'ubigeo_seleccionado', 'partidos_maestros','frecuencia']);
        if (!empty($value)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $value)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->get();
        }
    }

    public function updatedProvinciaId($value)
    {
        $this->reset(['distrito_id', 'distritos', 'ubigeo_seleccionado', 'partidos_maestros']);
        if (!empty($value)) {
            $prov_code = substr($value, 0, 4);
            $this->distritos = DB::table('ubigeos')
                ->where('id', 'like', $prov_code . '%')
                ->where('id', 'not like', '%00')
                ->get();
        }
        $this->procesarEstructuraFicha();
    }

    public function updatedDistritoId()
    {
        $this->procesarEstructuraFicha();
    }

    public function procesarEstructuraFicha()
    {
        // Determinamos el Ubigeo definitivo de la ficha a emitir
        $this->ubigeo_seleccionado = $this->distrito_id ?: ($this->provincia_id ?: null);
        $this->frecuencia = random_int(4,13);
        
        if (!$this->ubigeo_seleccionado) {
            $this->partidos_maestros = [];
            return;
        }

        $ubigeo_dist = $this->ubigeo_seleccionado;
        $ubigeo_prov = substr($ubigeo_dist, 0, 4) . '00';

        // 1. Consultar inscripciones reales para evaluar si la columna está viva en este territorio
        $inscritos_g = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'GOBERNADOR')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();
        $inscritos_c = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'CONSEJERO')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();
        $inscritos_p = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'PROVINCIAL')->where('ubigeo_id', $ubigeo_prov)->pluck('partido_politico_id')->toArray();

        // 2. CANDADO JNE CAPITAL: Evaluar si el ubigeo activo de la encuesta es capital de provincia
        $ubigeo_info = DB::table('ubigeos')->where('id', $ubigeo_dist)->first();
        
        if ($ubigeo_info && (int)$ubigeo_info->es_capital === 1) {
            $inscritos_d = []; // Se extingue por ley
        } else {
            $inscritos_d = DB::table('inscripciones_partidos')->where('tipo_eleccion', 'DISTRITAL')->where('ubigeo_id', $ubigeo_dist)->pluck('partido_politico_id')->toArray();
        }

        // Banderas lógicas para pintar sombras en el HTML
        $this->col_g_activa = count($inscritos_g) > 0;
        $this->col_c_activa = count($inscritos_c) > 0;
        $this->col_p_activa = count($inscritos_p) > 0;
        $this->col_d_activa = count($inscritos_d) > 0;

        $partidos_ids = array_unique(array_merge($inscritos_g, $inscritos_c, $inscritos_p, $inscritos_d));
        
        $this->partidos_maestros = DB::table('partidos_politicos')
            ->whereIn('id', $partidos_ids)
            ->select('id', 'nombre')
            ->orderBy('orden_cedula')
            ->get()->toArray();
    }

    private function cargarProvincias()
    {
        if (!empty($this->region_electoral_sel)) {
            $this->provincias = DB::table('ubigeos')
                ->where('region_electoral', $this->region_electoral_sel)
                ->where('id', 'like', '%00')
                ->where('id', 'not like', '%0000')
                ->get();
        }
    }

    public function render()
    {
        
        $info_geografica = '';
        if ($this->ubigeo_seleccionado) {
            $u_data = DB::table('ubigeos')->where('id', $this->ubigeo_seleccionado)->first();
            $prov_name = DB::table('ubigeos')->where('id', substr($this->ubigeo_seleccionado, 0, 4) . '00')->value('nombre') ?? '';
            $info_geografica = "{$u_data->region_electoral} / {$prov_name} / {$u_data->nombre}";
        }
        
        

        return view('livewire.ficha-paloteo', [
            'nombre_completo_ubigeo' => $info_geografica])->layout('layouts.app');
    }
}
