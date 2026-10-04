<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    protected $table = 'encuestas';

    protected $fillable = [
        'pregunta',
        'activa',
        'nota_prensa',
        'imagen_path',
        'mensaje_bienvenida',
    ];

    public function opciones()
    {
        return $this->hasMany(Opcion::class, 'encuesta_id');
    }
}
