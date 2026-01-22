<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;


class HistorialCodMuestra extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_cod_muestra';

    public $timestamps = false; 
    protected $fillable = [
        'codigo_muestra',
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
