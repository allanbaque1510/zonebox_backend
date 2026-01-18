<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'tipo_usuario';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
