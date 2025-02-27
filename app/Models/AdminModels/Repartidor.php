<?php

namespace App\Models\AdminModels;

use App\Models\VentaRepartidor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
    /** Tabla a usar */
    protected $table = 'repartidores';

    /** Campos a llenar */
    protected $fillable = [
        'id_sucursal',
        'nombre',
        'direccion',
        'telefono',
        'motocicleta_placas'
    ];

    /**
     * Get the user associated with the Repartidor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ventaRepartidor(): HasOne
    {
        return $this->hasOne(VentaRepartidor::class, 'id_repartidor');
    }

    use HasFactory;
}
