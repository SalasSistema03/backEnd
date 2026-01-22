<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class padron2 extends Model
{
    // Indicar la conexión de la segunda base de datos
    protected $connection = 'mysql2';

    // Indicar el nombre de la tabla si no es el plural del modelo
    protected $table = 'padron';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_padron';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;

    
}
