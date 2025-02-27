<?php

namespace App\Models\AdminModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produccion;

class Maquina extends Model
{
    /** Tabla a usar en la base de datos */
    protected $table = 'maquinas';

    /** Campos a llenar */
    protected $fillable = [
        'nombre',
        'materia_prima',
        'ubicacion'
    ];

    /**
     * Get the user associated with the Maquina
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function produccion(): HasOne
    {
        return $this->hasOne(Produccion::class);
    }

    use HasFactory;
}

