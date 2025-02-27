<?php

namespace App\Models\AdminModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{   
    /** Tabla a usar */
    protected $table = 'sucursales';

    /** Campos a llenar */
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono'
    ];

    use HasFactory;
}
