<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;


class HistorialCodOfrecimiento extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_cod_ofrecimiento';

    public $timestamps = false; 
    protected $fillable = [
        'codigo_ofrecimiento',
        'mensaje',
        'fecha_hora',
        'last_modified_by',
        'id_criterio_venta',
        'devolucion',
        'fecha_devolucion',
        'direccion'
    ];

    public function criterioVenta()
    {
        return $this->belongsTo(CriterioBusquedaVenta::class, 'id_criterio_venta', 'id_criterio_venta');
    }
}
