<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class DetalleProduccion extends Model
{
    /** Tabla a usar de la base de datos */
    protected $table = 'detalle_produccion';

    protected $fillable = [
        'id_produccion',
        'insumo',
        'cantidad_insumo',
        'producto',
        'cantidad_producto'
    ];

    /**
     * Get the user that owns the DetalleProduccion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function produccion(): BelongsTo
    {
        return $this->belongsTo(Produccion::class);
    }

    public $timestamps = false;

    use HasFactory;
}
