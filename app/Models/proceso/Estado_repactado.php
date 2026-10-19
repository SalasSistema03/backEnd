<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Historial_estado_reserva;

/**
 * Catálogo de estados de una repactación de alquiler.
 *
 * El modelo `Historial_estado_repactado` se relaciona con este catálogo
 * mediante su columna `estado`, que corresponde a `estado_repactado.id`.
 *
 * Utiliza la conexión `mysql10`, correspondiente a la base de datos del
 * proceso inmobiliario.
 */
class Estado_repactado extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'estado_repactado';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    /**
     * Nombre del estado, único campo habilitado para asignación masiva.
     */
    protected $fillable = [
        'estado',
    ];


    /**
     * Obtiene los historiales de repactación cuyo campo `estado` coincide con
     * el `id` de este estado.
     *
     * La clave foránea se encuentra en `historial_estado_repactado.estado`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historiales()
    {
        return $this->hasMany(Historial_estado_repactado::class, 'estado', 'id');
    }
}
