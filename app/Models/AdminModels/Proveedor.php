<?php

namespace App\Models\AdminModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{   
    /** Tabla a usar */
    protected $table = 'proveedores';

    /** Campos a llenar */
    protected $fillable = [
        'nombre',
        'telefono'
    ];

    use HasFactory;
}
