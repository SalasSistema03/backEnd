<?php

namespace App\Models\proceso;

use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Database\Eloquent\Model;

/**
 * Vincula la empresa asociada a una propiedad con el historial de su repactado.
 *
 * Utiliza la conexión `mysql10`, correspondiente a la base de datos sistema_proceso.
 */
class Historial_tasaciones_repactado extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_tasaciones_repactado';

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'id_empresa_propiedad',
        'id_historial_estado_repactado',
    ];

    /**
     * Empresa y propiedad asociadas a esta tasación de repactado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresaPropiedad()
    {
        return $this->belongsTo(Empresas_propiedades::class, 'id_empresa_propiedad', 'id');
    }

    /**
     * Historial de estado del repactado asociado a esta tasación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historialEstadoRepactado()
    {
        return $this->belongsTo(Historial_estado_repactado::class, 'id_historial_estado_repactado', 'id');
    }
}
