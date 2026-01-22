<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;

class categorias extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'categorias';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_categoria';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'categoria',
    ];
}
