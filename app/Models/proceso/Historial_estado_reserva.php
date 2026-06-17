<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_reserva;

class Historial_estado_reserva extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_estado_reserva';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'id_estado',
        'observaciones',
        'fecha_carga',
        'fecha_firma',
    ];

    public function estado()
    {
        return $this->belongsTo(Estado_reserva::class, 'id_estado', 'id');
    }
    
}
