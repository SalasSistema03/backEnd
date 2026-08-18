<?php

namespace App\Models\cliente;

use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona as ZonaModel;
use Illuminate\Database\Eloquent\Model;

class CriterioBusquedaAlquiler extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'criterio_busqueda_alquiler';

    protected $primaryKey = 'id_criterio';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_tipo_inmueble',
        'id_categoria',
        'id_zona',
        'cant_dormitorios',
        'cochera',
        'observaciones_criterio_alquiler',
        'estado_criterio_alquiler',
        'situacion_criterio_alquiler',
        'fecha_criterio_alquiler',
        'usuario_id',
    ];


    public function tipoInmueble()
    {
        return $this->belongsTo(Tipo_inmueble::class, 'id_tipo_inmueble', 'id');
    }

    public function zona()
    {
        return $this->belongsTo(ZonaModel::class, 'id_zona', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }

    public function historialMuestras()
    {
        return $this->hasMany(HistorialCodMuestra::class, 'id_criterio_alquiler', 'id_criterio_alquiler')
            ->latest('fecha_hora');
    }

    public function historialOfrecimientos()
    {
        return $this->hasMany(HistorialCodOfrecimiento::class, 'id_criterio_alquiler', 'id_criterio_alquiler')
            ->latest('fecha_hora');
    }

    public function historialConsultas()
    {
        return $this->hasMany(HistorialCodigoConsulta::class, 'id_criterio_alquiler', 'id_criterio_alquiler')
            ->latest('fecha_hora');
    }

    public function historialConversaciones()
    {
        return $this->hasMany(HistorialCriteriosConversacion::class, 'id_criterio_alquiler', 'id_criterio_alquiler')
            ->latest('fecha_hora');
    }
}
