<?php

namespace App\Livewire\Admin\Electoral;

use App\Models\Admin\Noticia;
use Livewire\Component;
use Livewire\WithPagination;

class NoticiasPublicas extends Component
{
    use WithPagination;

    public function render()
    {
        // Consultamos la tabla independiente de noticias directamente
        $articulosNoticias = Noticia::orderBy('id', 'desc')->paginate(3);

        return view('livewire.admin.electoral.noticias-publicas', [
            'articulosNoticias' => $articulosNoticias
        ])->layout('layouts.app');
    }
}
