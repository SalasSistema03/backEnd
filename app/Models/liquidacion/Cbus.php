<?php

namespace App\Models\liquidacion;

use Illuminate\Database\Eloquent\Model;

class Cbus extends Model
{
    protected $connection = 'mysql12';

    protected $table = 'cbus';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
 
    protected $fillable = [
        'propietario_id',
        'propiedad_id',
        'titular_cuenta',
        'dni',
        'cuit',
        'cbu',
        'porcentaje_distribucion'
    ];

}
