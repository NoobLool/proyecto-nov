<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    /** Tabla que se usa en la base de datos */
    protected $table = 'insumos';

    public $timestamps = false;

    /** Campos a llenar en la tabla */
    protected $fillable = [
        'id_sucursal',
        'nombre',
        'cantidad',
        'unidad_medicion',
        'precio_unitario',
        'total'
    ];

    /**
     * Get the user that owns the Insumo
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_sucursal');
    }

    use HasFactory;
}
