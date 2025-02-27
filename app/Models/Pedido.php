<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    /** Tabla a utilizar */
    protected $table = 'pedidos';

    /** Campos a llenar */
    protected $fillable = [
        'id_sucursal',
        'id_cliente',
        'tipo_pago',
        'total'
    ];

    public $timestamps = false;

    /**
     * Get the user associated with the Pedido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido')->select([
            'id', 'id_pedido', 'producto', 'cantidad', 'precio_unitario', 'subtotal'
        ]);
    }

    /**
     * Get the user associated with the Pedido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estado(): HasOne
    {
        return $this->hasOne(EstadoPedido::class, 'id_pedido')->select([
            'id', 'id_pedido', 'estado', 'saldo_pendiente', 'saldo_abonado', 'updated_at'
        ]);
    }

    /**
     * Get the user that owns the Pedido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public static function boot(){

        parent::boot();

        /** Solo usar created_at */
        static::creating(function ($pedido){
            $pedido->created_at = $pedido->freshTimestamp();
        });

        /** Eliminar datos registrados como tambien los datos asociados de otros modelos */
        static::deleting(function ($pedido){

            $pedido->detalles()->each(function($detalle){
                $detalle->delete();
            });

            $pedido->estado()->delete();

        });

    }

    const CREATED_AT = 'created_at';

    use HasFactory;
}
