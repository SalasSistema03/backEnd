<?php

namespace App\Models\fideicomiso;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidades extends Model
{
    protected $connection = 'mysql11';
    
    protected $table = 'unidades';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;

    protected $fillable = [
        'propietario',
        'piso',
        'unidad',
        'porcentual'
    ];
}
