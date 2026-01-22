<?php

namespace App\Models\Contable\Sellado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro_sellado extends Model
{
    protected $connection = 'mysql3';

    protected $table = 'registro_sellado';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_registro_sellado';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'cantidad_informes',
        'cantidad_meses',
        'fecha_inicio',
        'folio',
        'gasto_administrativo',
        'hojas',
        'informe',
        'inq_prop',
        'iva_gasto_adm',
        'monto_alquiler_comercial',
        'monto_alquiler_vivienda',
        'monto_contrato',
        'monto_documento',
        'nombre',
        'prop_alquiler',
        'prop_doc',
        'sellado',
        'tipo_contrato',
        'total_contrato',
        'valor_informe',
        'fecha_carga',
        'usuario_id',
    ];

}
