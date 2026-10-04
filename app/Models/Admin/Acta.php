<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Acta extends Model
{
    protected $table = 'actas';

    protected $fillable = [
        'mesa_sufragio_id',
        'estado',
        'region_electoral',
        'total_votantes_acta',
        'blancos_gobernador',
        'nulos_gobernador',
        'blancos_consejero',
        'nulos_consejero',
        'blancos_provincial',
        'nulos_provincial',
        'blancos_distrital',
        'nulos_distrital'
    ];

    // REQUERIMIENTO: Declarar la relación exacta que solicita el controlador
    public function mesaSufragio()
    {
        return $this->belongsTo(MesaSufragio::class, 'mesa_sufragio_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(ActaDetalle::class, 'acta_id', 'id');
    }
}
