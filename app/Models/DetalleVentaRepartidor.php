<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetalleVentaRepartidor extends Model
{
    /** Tabla a usar */
    protected $table = 'detalle_venta_repartidor';

    /** Campos a llenar */
    protected $fillable = [
        'id_venta_repartidor',
        'producto',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    /** Se desactiva created_at y updated_at */
    public $timestamps = false;

    /**
     * Get the user that owns the DetalleVentaRepartidor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ventaRepartidor(): BelongsTo
    {
        return $this->belongsTo(VentaRepartidor::class, 'id_venta_repartidor');
    }

    use HasFactory;
}
