<?php

namespace App\Models\cliente;

use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\cliente\clientes;
use Illuminate\Database\Eloquent\Model;

class CriterioBusquedaVenta extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'criterio_busqueda_venta';

    protected $primaryKey = 'id_criterio_venta';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_tipo_inmueble',
        'id_categoria',
        'id_zona',
        'cant_dormitorios',
        'cochera',
        'observaciones_criterio_venta',
        'estado_criterio_venta',
        'precio_hasta',
        'motivo_finalizado',
        'fecha_criterio_venta',
        'usuario_id',
    ];


    public function tipoInmueble()
    {
        return $this->belongsTo(Tipo_inmueble::class, 'id_tipo_inmueble', 'id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }
}
