<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use App\Models\Admin\Encuesta;
use App\Models\Admin\EncuestaOpcion;

class EncuestaPublica extends Component
{
    public $plataforma = 'escritorio';

    public $encuesta;
    public $opcionSeleccionada = '';
    public $yaVoto = false;

    public function mount()
    {
        $userAgent = request()->header('User-Agent');

        if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $this->plataforma = 'ios';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $this->plataforma = 'android';
        }
        // Obtener la encuesta activa del momento
        $this->encuesta = Encuesta::with('opciones')->where('activa', true)->latest()->first();
        
        // Verificar si este navegador ya votó en la sesión actual
        if (session()->has('encuesta_votada_' . ($this->encuesta->id ?? 0))) {
            $this->yaVoto = true;
        }
    }

    public function votar()
    {
        $this->validate([
            'opcionSeleccionada' => 'required'
        ], ['opcionSeleccionada.required' => 'Seleccione una opción antes de votar.']);

        // Incrementar el voto de manera atómica
        $opcion = EncuestaOpcion::findOrFail($this->opcionSeleccionada);
        $opcion->increment('votos');

        // Registrar bloqueo de duplicados en la sesión del usuario
        session()->put('encuesta_votada_' . $this->encuesta->id, true);
        $this->yaVoto = true;
        
        // Refrescar relaciones del modelo para recalcular gráficos
        $this->encuesta->refresh();
    }

    public function render()
    {
        $totalVotos = 0;
        if ($this->encuesta) {
            $totalVotos = $this->encuesta->opciones->sum('votos');
        }

        return view('livewire.admin.electoral.encuesta-publica', [
            'totalVotos' => $totalVotos
        ])->layout('layouts.app');
    }
}
