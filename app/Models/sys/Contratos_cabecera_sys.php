<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Model;

class Contratos_cabecera_sys extends Model
{
    // Indicar la conexión de la segunda base de datos
    protected $connection = 'mysql2';

    // Indicar el nombre de la tabla si no es el plural del modelo
    protected $table = 'contratos_cabecera';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_contrato_cabecera';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
}
