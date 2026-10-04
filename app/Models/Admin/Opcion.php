<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Opcion extends Model
{
    protected $table = 'encuesta_opciones';

    protected $fillable = [
        'encuesta_id',
        'opcion',
        'votos',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }
}
