<?php

namespace App\Models\Contable\Sellado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valor_hoja extends Model
{
    protected $connection = 'mysql3';

    protected $table = 'valor_hoja';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_valor_hoja';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;

}
