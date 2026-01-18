<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HistorialEstados extends Model
{
    protected $table = 'historial_estados';

    protected $fillable = [
        'id',
        'id_pedido',
        'id_estado',
        'fecha',
        'completado',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'completado' => 'boolean',
    ];
    protected $appends = ['estado_pedido', 'estado_descripcion'];
    
  

    protected function estadoPedido(): Attribute {
        return Attribute::make(
            get: fn () => $this->estado->nombre
        );
    }

    protected function estadoDescripcion(): Attribute {
        return Attribute::make(
            get: fn () => $this->estado->descripcion
        );
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado');
    }
}
