<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /** Tabla a utilizar */
    protected $table = 'clientes';

    /** Campos a llenar */
    protected $fillable = [
        'id_sucursal',
        'nombre',
        'direccion',
        'telefono'
    ];

    /**
     * Get all of the comments for the Cliente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pedidos(): HasMany
    {
       return $this->hasMany(Pedido::class, 'id_cliente')->select([
           'id', 'nombre'
        ]);
    }
    
    use HasFactory;
}
