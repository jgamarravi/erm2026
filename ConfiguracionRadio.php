<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\Configuracion;
use Illuminate\Support\Facades\Storage;
class ConfiguracionRadio extends Component
{
    use WithFileUploads;

    public $mensaje_bienvenida = '';
    public $foto_portada;
    public $imagenExistente;

    // NUEVAS PROPIEDADES PARA WHATSAPP
    public $whatsapp_numero = '';
    public $whatsapp_mensaje_base = '';

    protected $rules = [
        'mensaje_bienvenida' => 'required|string|min:10',
        'foto_portada' => 'nullable|image|max:2048',
        'whatsapp_numero' => 'required|numeric|digits_between:8,15',
        'whatsapp_mensaje_base' => 'required|string|max:255'
    ];

    public function mount()
    {
        $configMensaje = Configuracion::where('clave', 'mensaje_bienvenida')->first();
        $this->mensaje_bienvenida = $configMensaje ? $configMensaje->valor : '';

        $configImagen = Configuracion::where('clave', 'imagen_portada')->first();
        $this->imagenExistente = $configImagen ? $configImagen->valor : null;

        // Cargar configuraciones de WhatsApp de la BD
        $this->whatsapp_numero = Configuracion::where('clave', 'whatsapp_numero')->first()?->valor ?? '51926634659';
        $this->whatsapp_mensaje_base = Configuracion::where('clave', 'whatsapp_mensaje_base')->first()?->valor ?? 'Hola Stereo 92...';
    }

    public function guardarConfiguracion()
    {
        $this->validate();

        // 1. Guardar mensaje e imagen de portada
        Configuracion::updateOrCreate(['clave' => 'mensaje_bienvenida'], ['valor' => $this->mensaje_bienvenida]);

        if ($this->foto_portada) {
            if ($this->imagenExistente) { Storage::disk('public')->delete($this->imagenExistente); }
            $rutaNueva = $this->foto_portada->store('portada', 'public');
            Configuracion::updateOrCreate(['clave' => 'imagen_portada'], ['valor' => $rutaNueva]);
            $this->imagenExistente = $rutaNueva;
        }

        // 2. NUEVO: Guardar ajustes de WhatsApp de la cabecera
        Configuracion::updateOrCreate(['clave' => 'whatsapp_numero'], ['valor' => trim($this->whatsapp_numero)]);
        Configuracion::updateOrCreate(['clave' => 'whatsapp_mensaje_base'], ['valor' => $this->whatsapp_mensaje_base]);

        $this->reset('foto_portada');
        $this->dispatch('notificar-config-radio', mensaje: '¡Ajustes de portada y WhatsApp guardados con éxito!');
    }

    public function render()
    {
        return view('livewire.admin.electoral.configuracion-radio')
        ->layout('layouts.app');
    }
}
