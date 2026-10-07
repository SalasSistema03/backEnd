<?php

namespace App\Models\liquidacion;
    
use Illuminate\Database\Eloquent\Model;

class Liquidaciones extends Model
{
    protected $connection = 'mysql12';

    protected $table = 'liquidaciones';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;
 
    protected $fillable = [
        'propiedad_id',
        'periodo',
        'monto'
    ];

}
