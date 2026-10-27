<?php

namespace App\Models\proceso;

use App\Models\At_cl\Propiedad;
use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_reserva;

/**
 * Historial de estados de una repactación de alquiler.
 *
 * Cada fila conserva el estado registrado junto con los valores económicos,
 * las condiciones y los comentarios asociados a ese momento del proceso.
 *
 * Utiliza la conexión `mysql10`, correspondiente a la base de datos del
 * proceso inmobiliario.
 */
class Historial_estado_repactado extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_estado_repactado';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    /**
     * Campos habilitados para asignación masiva.
     *
     * La lista restringe las columnas que pueden escribirse mediante
     * `create()` o `update()` con un arreglo.
     */
    protected $fillable = [
        'estado',
        'cuenta_corriente',
        'plazo_y_actualizacion_contrato',
        'nuevo_valor_alquiler',
        'fecha_cierre',
        'gestor',
        'observaciones_estado',
        'condicion',
        'situacion',
        'total',
        'comentario_inq',
        'comentario_prop',
        'quien_guardo',
        'propiedad_id',
        'periodo_cargado',
        'monto_alquiler_viejo',
        'monto_documento',
        'reconocimiento',
        'inicio_contrato',
        'vencimiento_contrato'

    ];

    /**
     * Obtiene el estado de repactación asociado al historial.
     *
     * La consulta compara el campo `estado` de este historial con el `id`
     * de `estado_repactado`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estado()
    {
        return $this->belongsTo(Estado_repactado::class, 'estado', 'id');
    }

    /**
     * Obtiene el proceso de propiedad cuyo `id_historial_estado_repactado`
     * coincide con el `id` de este historial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proceso_propiedad()
    {
        return $this->belongsTo(Proceso_propiedad::class, 'id', 'id_historial_estado_repactado');
    }



    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id', 'id');
        // O simplemente: return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }
}
