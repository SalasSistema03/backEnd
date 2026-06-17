<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_contrato;

class Historial_estado_contrato extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_estado_contrato';

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
        return $this->belongsTo(Estado_contrato::class, 'id_estado', 'id');
    }
    
}
