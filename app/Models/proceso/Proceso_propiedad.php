<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_reserva;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Historial_estado_dpto;
use App\Models\cliente\clientes;
use App\Models\At_cl\Propiedad;
use App\Models\usuarios_y_permisos\Usuario;

/**
 * Proceso de reserva y alquiler de una propiedad.
 *
 * Vincula al cliente, la propiedad y el asesor responsable, y conserva las
 * condiciones de la reserva y del alquiler. Los campos `id_historial_*`
 * referencian el estado registrado en cada sector del proceso: reserva,
 * contrato, departamento técnico y repactación.
 *
 * Utiliza la conexión `mysql10`, correspondiente a la base de datos del
 * proceso inmobiliario.
 */
class Proceso_propiedad extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'proceso_propiedad';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    /**
     * Campos habilitados para asignación masiva.
     *
     * Agrupa los datos de la reserva (fechas, montos, moneda y tipo), las
     * claves hacia cliente y propiedad, los historiales de estado de cada
     * sector y las condiciones económicas del alquiler.
     */
    protected $fillable = [
        'asesor',
        'fecha_reserva',
        'fecha_fin_reserva',
        'id_cliente', //relacion con el modelo cliente
        'reservante',
        'id_propiedad', //relacion con el modelo propiedad
        'tipo_reserva',
        'moneda',
        'monto_reserva',
        'monto_aceptado',
        'documentacion',
        'id_historial_estado_reserva',
        'id_historial_estado_contrato',
        'id_historial_estado_dpto',
        'id_historial_estado_repactado',
        'quien_cargo',
        'quien_modifico',
        'estado_alquiler_inicial',
        'precio_alquiler',
        'meses_contrato',
        'id_registro_sellado'
    ];

    /**
     * Cliente que realizó la reserva.
     *
     * La relación cruza a la conexión `mysql5` y usa `id_cliente` como clave
     * primaria del modelo `clientes`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Propiedad reservada.
     *
     * La relación cruza a la conexión `mysql` y usa el campo `id` de la tabla
     * `propiedades`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad', 'id');
    }

    /**
     * Estado de reserva vigente del proceso.
     *
     * `id_historial_estado_reserva` apunta al registro más reciente creado en
     * `historial_estado_reserva`. El historial conserva además la referencia
     * inversa en su columna `id_proceso_propiedad`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historialEstadoReserva()
    {
        return $this->belongsTo(Historial_estado_reserva::class, 'id_historial_estado_reserva', 'id');
    }

    /**
     * Estado de contrato vigente del proceso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historialEstadoContrato()
    {
        return $this->belongsTo(Historial_estado_contrato::class, 'id_historial_estado_contrato', 'id');
    }

    /**
     * Estado del departamento técnico vigente del proceso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historialEstadoDpto()
    {
        return $this->belongsTo(Historial_estado_dpto::class, 'id_historial_estado_dpto', 'id');
    }

    /**
     * Estado de repactación asociado al proceso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historialEstadoRepactado()
    {
        return $this->belongsTo(Historial_estado_repactado::class, 'id_historial_estado_repactado', 'id');
    }

    /**
     * Asesor responsable del proceso.
     *
     * El campo `asesor` almacena el identificador de la tabla `usuarios` de la
     * conexión `mysql4`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asesorUsuario()
    {
        return $this->belongsTo(Usuario::class, 'asesor');
    }

    /**
     * Registro de sellado asociado al proceso.
     *
     * La relación usa `id_registro_sellado` como clave en ambas tablas y
     * consulta la conexión `mysql3`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function registroSellado()
    {
        // Como Registro_sellado está en otra base de datos (mysql3), Eloquent manejará la consulta.
        // Si el registro se borra, esta relación devolverá null automáticamente sin romper la base de datos.
        return $this->belongsTo(\App\Models\Contable\Sellado\Registro_sellado::class, 'id_registro_sellado', 'id_registro_sellado');
    }
}
