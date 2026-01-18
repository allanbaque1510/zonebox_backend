<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';

    protected $fillable = [
        'codigo',
        'numero_tracking',
        'descripcion',
        'precio',
        'id_empresa_envio',
        'id_tienda',
        'otra_tienda',
        'id_ciudad_destino',
        'fecha_entrega_estimada',
        'fecha_entrega',
        'instruccion',
        'id_cliente',
        'id_usurio_gestion',
        'id_estado',
        'anio',
        'activo',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_entrega' => 'datetime:Y-m-d H:i:s',
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function empresaEnvio()
    {
        return $this->belongsTo(EmpresaEnvio::class, 'id_empresa_envio');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstados::class, 'id_pedido'); 
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function ciudadDestino()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad_destino');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente');
    }

    public function usuarioGestion()
    {
        return $this->belongsTo(User::class, 'id_usurio_gestion');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado');
    }
}
