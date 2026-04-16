<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadosVenta extends Model
{
    use HasFactory;
    //Asociacion con la tabla padron de la BD
    protected $table = 'historial_estados_venta';

    // Especifica los campos fillable si es necesario
    protected $fillable = [
        'id_propiedad',
        'id_estado_venta',
        'fecha',
        'comentario',
        'reactiva_fecha',
        'id_usuario',
        'updated_at',
        'created_at'
    ];

    /**
     * Relación con el modelo Propiedad.
     * Un historial de venta pertenece a una propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad');
    }

}
