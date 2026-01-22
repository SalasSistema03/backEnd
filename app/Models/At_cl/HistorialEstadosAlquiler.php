<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadosAlquiler extends Model
{
    use HasFactory;
    //Asociacion con la tabla padron de la BD
    protected $table = 'historial_estados_alquiler';
    // Especifica los campos fillable si es necesario
    protected $fillable = [
        'id_propiedad',
        'id_estado_alquiler',
        'fecha_alquiler',
        'comentario_alquiler',
        'reactiva_fecha_alquiler',
        'id_usuario',
        'updated_at',
        'created_at'
    ];

}
