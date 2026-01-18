<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Casillero extends Model
{
    protected $table = 'casilleros';

    protected $fillable = [
        'id_ciudad',
        'id_usuario',
        'codigo',
        'direccion',
        'estado',
        'zip',
        'telefono',
        'activo',
    ];

    protected $appends = ['nombre_ciudad', 'nombre_pais', 'direccion_completa'];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    
    protected function nombreCiudad(): Attribute {
        return Attribute::make(
            get: fn () => ucwords(strtolower($this->ciudad->nombre))
        );
    }
    
    protected function direccionCompleta(): Attribute {
        return Attribute::make(
            get: fn () => $this->direccion . ', ' . $this->estado . ' ' . $this->zip . ', ' . $this->ciudad->pais->nombre
        );
    }


    protected function nombrePais(): Attribute {
        return Attribute::make(
            get: fn () => ucwords(strtolower($this->ciudad->pais->nombre))
        );
    }


    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
