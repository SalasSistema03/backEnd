<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\proceso\Estado_contrato;
use App\Models\usuarios_y_permisos\Usuario;

class Historial_estado_contrato extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'historial_estado_contrato';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'id_estado',
        'fecha_inventario',
        'fecha_comercial_presenta_carpeta',
        'fecha_preaprobada',
        'fecha_reserva',
        'gastos_administrativos',
        'tirilla_entregada_a',
        'fecha_tirilla_entregada',
        'tirilla_controlada_por',
        'fecha_tirilla_controlada',
        'fecha_contrato',
        'fecha_autorizacion',
        'fecha_finalizacion_firma_cobro',
        'observaciones'
    ];

    public function estado()
    {
        return $this->belongsTo(Estado_contrato::class, 'id_estado', 'id');
    }

    public function tirillaEntregadaPor()
    {
        return $this->belongsTo(Usuario::class, 'tirilla_entregada_a');
    }

    public function tirillaControladaPor()
    {
        return $this->belongsTo(Usuario::class, 'tirilla_controlada_por');
    }
}
