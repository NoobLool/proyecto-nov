<?php

namespace App\Models;

use App\Models\AdminModels\Repartidor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class VentaRepartidor extends Model
{
    /** Tabla a usar */
    protected $table = 'ventas_repartidor';

    /** Campos a llenar */
    protected $fillable = [
        'id_repartidor',
        'id_sucursal',
        'total'
    ];

    /** Descativar updated_at */
    public $timestamps = false;

    /**
     * Get all of the comments for the VentaRepartidor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVentaRepartidor::class, 'id_venta_repartidor');
    }

    /**
     * Get the user that owns the VentaRepartidor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor')->select([
            'id','nombre', 
        ]);
    }

    /** Elimina datos relacionados con el registro */
    public static function boot(){

        parent::boot();

        static::creating(function ($venta){
            $venta->created_at = $venta->freshTimestamp();
        });

        static::deleting(function ($ventaRepartidor){
            $ventaRepartidor->detalles()->delete();
        });
    }

    const CREATED_AT = 'created_at'; 

    use HasFactory;
}
