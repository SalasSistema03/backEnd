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
        return $this->belongsTo(ZonaModel::class,'id_zona', 'id');
    }

}
