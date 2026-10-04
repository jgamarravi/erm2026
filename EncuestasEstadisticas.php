<?php

namespace App\Livewire\Admin\Electoral;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\Noticia;
use App\Models\Admin\Encuesta;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class EncuestasEstadisticas extends Component
{
    use WithFileUploads;

    public $noticiaId;
    public $titular = '';
    public $contenido = '';
    public $locutora_firma = ''; // NUEVA PROPIEDAD
    public $encuesta_id = '';
    public $foto_destacada;
    public $imagenExistente;

    public $modoEdicion = false;

    protected $rules = [
        'titular' => 'required|string|min:10',
        'contenido' => 'required|string|min:30',
        'locutora_firma' => 'required|string|min:3',
        'encuesta_id' => 'nullable|integer',
        'foto_destacada' => 'nullable|image|max:2048'
    ];

    public function guardarNoticia()
    {
        $this->validate();

        $rutaImagen = $this->imagenExistente;
        if ($this->foto_destacada) {
            if ($rutaImagen) { Storage::disk('public')->delete($rutaImagen); }
            $rutaImagen = $this->foto_destacada->store('articulos', 'public');
        }

        Noticia::updateOrCreate(
            ['id' => $this->noticiaId],
            [
                'titular' => $this->titular,
                'contenido' => $this->contenido,
                'locutora_firma' => $this->locutora_firma, // SE GUARDA LA FIRMA
                'imagen_path' => $rutaImagen,
                'encuesta_id' => $this->encuesta_id ?: null
            ]
        );

        $this->limpiarFormulario();
        $this->dispatch('notificar-estadistica', mensaje: 'Crónica procesada en el historial.');
    }

    public function editarNoticia($id)
    {
        $this->modoEdicion = true;
        $noticia = Noticia::findOrFail($id);
        $this->noticiaId = $noticia->id;
        $this->titular = $noticia->titular;
        $this->contenido = $noticia->contenido;
        $this->locutora_firma = $noticia->locutora_firma ?? '';
        $this->encuesta_id = $noticia->encuesta_id ?? '';
        $this->imagenExistente = $noticia->imagen_path;
    }

    // NUEVO MÉTODO: Escucha la orden de eliminación de SweetAlert2
    #[On('ejecutar-borrado-cronica')]
    public function eliminarNoticia($id)
    {
        $noticia = Noticia::findOrFail($id);
        if ($noticia->imagen_path) {
            Storage::disk('public')->delete($noticia->imagen_path);
        }
        $noticia->delete();
        $this->dispatch('notificar-estadistica', mensaje: 'Crónica eliminada de forma permanente.');
    }

    public function limpiarFormulario() {
        $this->reset(['noticiaId', 'titular', 'contenido', 'locutora_firma', 'encuesta_id', 'foto_destacada', 'imagenExistente', 'modoEdicion']);
    }

    public function render()
    {
        return view('livewire.admin.electoral.encuestas-estadisticas', [
            'noticiasHistoricas' => Noticia::with('encuesta')->orderBy('id', 'desc')->get(),
            'encuestasDisponibles' => Encuesta::orderBy('id', 'desc')->get()
        ])->layout('layouts.app');
    }
}
