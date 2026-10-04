<?php

namespace App\Models;

use App\Models\Admin\MesaSufragio;
use Illuminate\Database\Eloquent\Model;

class VotoMesa extends Model
{
    protected $table = 'votos_mesas';

    protected $fillable = [
        'mesa_sufragio_id',
        'partido_politico_id',
        'tipo_eleccion',
        'voto_especial',
        'cantidad_votos',
    ];
 
    public function mesaSufragio()
    {
        return $this->belongsTo(MesaSufragio::class, 'mesa_sufragio_id');
    }
}
