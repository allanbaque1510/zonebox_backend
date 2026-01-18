<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'activo',
    ];

}
