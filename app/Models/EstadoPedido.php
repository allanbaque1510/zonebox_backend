<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    protected $table = 'estados';

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'color',
        'orden',
    ];

}
