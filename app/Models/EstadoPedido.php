<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{

    /** Tabla a utilizar */
    protected $table = 'estado_pedido';

    /** Campos a llenar */
    protected $fillable = [
        'id_pedido',
        'estado',
        'saldo_pendiente',
        'saldo_abonado'
    ];

    public $timestamps = false;

    /**
     * Get the user that owns the EstadoPedido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    protected static function boot(){

        parent::boot();

        /** Solo usar updated_at */
        static::creating(function($estadoPedido){
            $estadoPedido->updated_at = $estadoPedido->freshTimestamp();
        });

    }

    const UPDATED_AT = 'updated_at';

    use HasFactory;
}
