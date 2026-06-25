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
        'quien_cargo',
        'quien_modifico',
        'estado_alquiler_inicial'
    ];

    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'id_cliente', 'id_cliente');
    }

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad', 'id');
    }

    public function historialEstadoReserva()
    {
        return $this->belongsTo(Historial_estado_reserva::class, 'id_historial_estado_reserva', 'id');
    }

    public function historialEstadoContrato()
    {
        return $this->belongsTo(Historial_estado_contrato::class, 'id_historial_estado_contrato', 'id');
    }

    public function historialEstadoDpto()
    {
        return $this->belongsTo(Historial_estado_dpto::class, 'id_historial_estado_dpto', 'id');
    }

    public function asesorUsuario()
    {
        return $this->belongsTo(Usuario::class, 'asesor');
    }
}
