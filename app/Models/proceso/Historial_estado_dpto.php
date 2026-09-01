<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_dpto;
use App\Models\usuarios_y_permisos\Usuario;

class Historial_estado_dpto extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_estado_dpto';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'id_estado',
        'observaciones',
        'fecha_inventario',
        'fecha_carga',
        'quien_cargo',
        'id_proceso_propiedad',
        'verificado_por'
    ];

    public function estado()
    {
        return $this->belongsTo(Estado_dpto::class, 'id_estado', 'id');
    }

    public function verificadoPor()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por', 'id');
    }
}
