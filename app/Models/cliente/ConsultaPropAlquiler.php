<?php

namespace App\Models\cliente;

use App\Models\At_cl\Propiedad;
use Illuminate\Database\Eloquent\Model;

class ConsultaPropAlquiler extends Model
{
    // Nombre de la tabla
    protected $table = 'consulta_prop_alquiler';

    // Conexión personalizada
    protected $connection = 'mysql5';

    // Clave primaria
    protected $primaryKey = 'id_con_prop_alquiler';

    // No usar timestamps automáticos (created_at / updated_at)
    public $timestamps = false;

    // Campos que pueden asignarse masivamente
    protected $fillable = [
        'tipo_consulta',
        'observ_con_alquiler',
        'fecha_consulta_propiedad',
        'estado_consulta_alquiler',
        'id_cliente',
        'id_propiedad',
        'usuario_id',
    ];

    /**
     * Relación con el modelo Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Relación con el modelo Propiedad
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad', 'id');
    }
}
