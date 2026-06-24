<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Historial_estado_contrato;

class Estado_contrato extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'estado_contrato';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'estado',
    ];

    public function historiales()
    {
        return $this->hasMany(Historial_estado_contrato::class, 'id_estado', 'id');
    }
    
}
