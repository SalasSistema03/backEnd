<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;

class HistorialCriterioConversacionAlq extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'historial_criterio_conversacion_alq';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
    'id_criterio_alquiler',
    'mensaje',
    'last_modified_by',
    'created_at',
    'updated_at'
    ];

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    public function criterioAlquiler(){
        return $this->belongsTo(CriterioBusquedaAlquiler::class, 'id_criterio_alquiler', 'id_criterio');
        
    }
}
