<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class boton_actualizado extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'boton_actualizado';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'ultima_fecha_agua',
        'ultima_fecha_api',
        'ultima_fecha_tgi',
        'ultima_fecha_gas'
    ];
}
