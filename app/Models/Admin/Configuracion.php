<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;


class Configuracion extends Model
{
    protected $table = 'configuraciones';
    protected $fillable = ['clave','valor'];


}
