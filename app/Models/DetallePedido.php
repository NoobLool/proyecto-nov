<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    /** Tabla a utilizar */
    protected $table = 'detalle_pedido';

    /** Campos a llenar */
    protected $fillable = [
        'id_pedido',
        'producto',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    public $timestamps = false;

    /**
     * Get the user that owns the DetallePedido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    use HasFactory;
}
