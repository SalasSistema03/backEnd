<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;

class HistorialCodConsulta extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_cod_consulta';

    protected $fillable = [
        'id_criterio_venta',
        'mensaje',
        'fecha_hora',
        'last_modified_by',
        'codigo_consulta',
        'devolucion',
        'fecha_devolucion',
        'direccion'
    ];

    public function criterioVenta()
    {
        return $this->belongsTo(CriterioBusquedaVenta::class, 'id_criterio_venta', 'id_criterio_venta');
    }
}