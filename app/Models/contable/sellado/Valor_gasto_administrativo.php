<?php

namespace App\Models\Contable\Sellado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valor_gasto_administrativo extends Model
{
    protected $connection = 'mysql3';

    protected $table = 'valor_gasto_administrativo';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_valor_gasto_administrativo';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
}
