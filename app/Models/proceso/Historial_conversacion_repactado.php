<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;

/**
 * Mensaje de la conversación registrada para un repactado.
 *
 * `id_historial_estado_repactado` apunta al registro de historial_estado_repactado
 * que se edita en el modal. `tipo_persona` indica quién lo dijo: 'propietario'
 * o 'inquilino'. `fecha_hora` es el momento en que se guardó el mensaje.
 *
 * Utiliza la conexión `mysql10`, correspondiente a la base sistema_proceso.
 */
class Historial_conversacion_repactado extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_conversacion_repactado';

    protected $fillable = [
        'id_historial_estado_repactado',
        'tipo_persona',
        'fecha_hora',
        'mensaje',
        'id_usuario',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];
}
