<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'paises';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
        'flag',
    ];

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'id_pais');
    }
}
