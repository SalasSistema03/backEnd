<?php

namespace App\Models\sys\impuesto
;

use Illuminate\Database\Eloquent\Model;

class padronTGI_sys extends Model
{
    // Indicar la conexión de la segunda base de datos
    protected $connection = 'mysql2';

    // Indicar el nombre de la tabla si no es el plural del modelo
    protected $table = 'propiedades_impuestos';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_propiedad_impuesto';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
}

