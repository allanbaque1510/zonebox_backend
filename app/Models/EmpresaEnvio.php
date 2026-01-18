<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaEnvio extends Model
{
    protected $table = 'empresas_envio';

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'activo',
    ];
    public $timestamps = false;
}