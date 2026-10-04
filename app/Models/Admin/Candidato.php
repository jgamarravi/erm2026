<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    protected $table = 'candidatos';
    protected $fillable = ['partido_politico_id', 'dni', 'nombres', 'apellidos', 'cargo', 'ubigeo_id'];

    public function partido()
    {
        return $this->belongsTo(PartidoPolitico::class, 'partido_politico_id');
    }

}
