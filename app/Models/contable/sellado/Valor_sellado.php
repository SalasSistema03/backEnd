<?php

namespace App\Models\contable\sellado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valor_sellado extends Model
{
    protected $connection = 'mysql3';

    protected $table = 'valor_sellado';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_valor_sellado';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
}
