<?php

namespace App\Models\Admin;

use App\Models\VotoMesa;
use Illuminate\Database\Eloquent\Model;

class MesaSufragio extends Model
{
    protected $table = 'mesas_sufragio';
    protected $fillable = ['centro_votacion_id', 'numero_mesa', 'electores_habiles','total_votaron','estado_acta','tipo_observacion'];

    public function centroVotacion()
    {
        return $this->belongsTo(CentroVotacion::class, 'centro_votacion_id');
    }

    public function mesasSufragio(){
        return $this->hasMany(VotoMesa::class,'mesa_sufragio_id');
    }

}
