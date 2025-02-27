<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /** Tabla a usar en la base de datos */
    protected $table = 'productos';

    public $timestamps = false;

    /** Campos a llenar en la tabla*/
    protected $fillable = [
        'id_sucursal',
        'nombre',
        'cantidad',
        'unidad_medicion'
    ];

    /**
     * Get the user that owns the Producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_sucursal');
    }

    use HasFactory;
}
