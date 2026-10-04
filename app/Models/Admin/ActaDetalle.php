<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ActaDetalle extends Model
{
    protected $table = 'acta_detalles';

    protected $fillable = [
        'acta_id',
        'partido_politico_id',
        'votos_gobernador',
        'votos_consejero',
        'votos_provincial',
        'votos_distrital'
    ];

    // REQUERIMIENTO: Declarar la relación exacta que el controlador busca para el nombre/logo
    public function partidoPolitico()
    {
        return $this->belongsTo(PartidoPolitico::class, 'partido_politico_id', 'id');
    }

    public function acta()
    {
        return $this->belongsTo(Acta::class, 'acta_id', 'id');
    }
}
