<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;

class HistorialCriteriosConversacion extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_criterios_conversacion';
    public $timestamps = false; // Esto desactiva los timestamps

    protected $fillable = [
        'id_criterio_venta',
        'mensaje',
        'fecha_hora',
        'last_modified_by'
    ];

    public function criterioVenta()
    {
        return $this->belongsTo(CriterioBusquedaVenta::class, 'id_criterio_venta', 'id_criterio_venta');
    }
}
