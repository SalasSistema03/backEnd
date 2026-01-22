<?php

namespace App\Models\cliente;
use Illuminate\Database\Eloquent\Model;

class tipo_inmueble extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'tipo_inmuebles';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_tipo_inmueble';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'inmueble',
    ];
}
