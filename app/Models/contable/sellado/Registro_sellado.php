<?php

namespace App\Models\Contable\Sellado;

use App\Models\proceso\Proceso_propiedad;
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
        'empresa',
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
        'mostrar',
        'finalizado',
        'confirmar'
    ];

    //Esta funcion trae los datos del usuario
    public function usuario()
    {
        return $this->belongsTo('App\Models\usuarios_y_permisos\Usuario', 'usuario_id');
    }

    // 1. Relacionamos el Registro_sellado con Empresa_propiedad a través del folio
    public function empresaPropiedad()
    {
        return $this->hasOne(\App\Models\At_cl\Empresas_propiedades::class, 'folio', 'folio');
    }

    // 2. Relacionamos directamente a la Propiedad a través de Empresa_propiedad
    public function propiedad()
    {
        return $this->hasOneThrough(
            \App\Models\At_cl\Propiedad::class,
            \App\Models\At_cl\Empresas_propiedades::class,
            'folio', // Clave foránea en tabla intermedia (empresa_propiedad)
            'id', // Clave en la tabla objetivo (propiedades)
            'folio', // Clave local en tabla origen (registro_sellado)
            'propiedad_id' // Clave foránea en la tabla intermedia que apunta al objetivo
        );
    }

    public function proceso()
    {
        return $this->hasOne(Proceso_propiedad::class, 'id_registro_sellado', 'id_registro_sellado');
    }
}
