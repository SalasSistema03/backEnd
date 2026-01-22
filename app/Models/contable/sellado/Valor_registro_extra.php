<?php

namespace App\Models\contable\sellado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valor_registro_extra extends Model
{
    protected $connection = 'mysql3';

    protected $table = 'valor_registro_extra';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_registro_extra';

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;

    public function getValorRegistroExtra(){
        return valor_registro_extra::all();
    }
    
}
