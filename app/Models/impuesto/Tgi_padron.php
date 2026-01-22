<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class Tgi_padron extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'tgi_padron';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'folio',
        'calle',
        'partida',
        'clave',
        'abona',
        'administra',
        'empresa',
        'estado',
        'comienza',
        'rescicion'
    ];
}
