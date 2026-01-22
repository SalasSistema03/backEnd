<?php

namespace App\Models\cliente;

use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use Illuminate\Database\Eloquent\Model;

class ConsultaPropVenta extends Model
{
    // Nombre de la tabla
    protected $table = 'consulta_prop_venta';

    // Conexión personalizada
    protected $connection = 'mysql5';

    // Clave primaria
    protected $primaryKey = 'id_con_prop_venta';

    // No usa timestamps (created_at, updated_at)
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = [
        'tipo_consulta',
        'observ_con_venta',
        'fecha_consulta_propiedad',
        'estado_consulta_venta',
        'id_cliente',
        'id_propiedad',
        'usuario_id',
        'id_criterio_venta'
    ];

    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }


    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad', 'id');
    }

    public function criterio_venta()
    {
        return $this->belongsTo(CriterioBusquedaVenta::class, 'id_criterio_venta', 'id_criterio_venta');
    }
}
