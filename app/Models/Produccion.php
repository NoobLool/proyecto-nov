<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminModels\Maquina;

class Produccion extends Model
{
    /** Tabla que se usa en la base de datos */
    protected $table = 'produccion';

    /** Campos a llenar en la base de datos  */
    protected $fillable = [
        'id_sucursal',
        'id_maquina'
    ];

    /** Descativar updated_at */
    public $timestamps = false;

    /**
     * Get all of the detalles for the Produccion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleProduccion::class, 'id_produccion');
    }

    /**
     * Get thn maquinas that owns the Produccion
     *
     * @return \Illuminate\DatabMaquinaEloquent\Relations\BelongsTo
     */
    public function maquinas(): BelongsTo
    {
        return $this->belongsTo(Maquina::class, 'id_maquina');
    }

    /** Función para eliminar datos relacionados con el registro */
    protected static function boot(){

        parent::boot();

        /** Solo usar created_at */
        static::creating(function ($produccion){
            $produccion->created_at = $produccion->freshTimestamp();
        });

        static::deleting(function ($produccion){
            $produccion->detalles()->delete();
        });
    }

    const CREATED_AT = 'created_at';

    use HasFactory;
}
