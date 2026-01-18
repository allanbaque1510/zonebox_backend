<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades';

    protected $fillable = [
        'id_pais',
        'codigo',
        'nombre',
        'activo',
    ];

    protected $appends = ['nombre_ciudad'];
    
    protected function nombreCiudad(): Attribute {
        return Attribute::make(
            get: fn () => ucwords(strtolower($this->nombre))
        );
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'id_pais');
    }
}
