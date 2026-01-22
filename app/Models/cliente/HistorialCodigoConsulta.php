<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;

class HistorialCodigoConsulta extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_cod_consulta';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
    'codigo_consulta',
    'mensaje',
    'fecha_hora',
    'last_modified_by',
    'id_criterio_venta',
    'devolucion',
    'fecha_devolucion',
    'direccion'
    ];

}
