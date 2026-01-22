<?php

namespace App\Models\cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class clientes extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'clientes';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_cliente';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'nombre',
        'telefono',
        'observaciones',
        'ingreso',
        'pertenece_a_inmobiliaria',
        'nombre_de_inmobiliaria',
        'id_asesor_venta',
        'id_asesor_alquiler',
        'usuario_id',
    ];


    public function consulta_prop_venta()
    {
        return $this->hasMany(ConsultaPropVenta::class, 'id_cliente')
                        ->orderByDesc('fecha_consulta_propiedad') // primero la fecha más reciente
                ->orderBy('estado_consulta_venta', 'desc'); // después "Activo" > "Inactivo;
    }

    public function consulta_prop_alquiler()
    {
        return $this->hasMany(ConsultaPropAlquiler::class, 'id_cliente');
    }

    public function criterio_busqueda_alquiler()
    {
        return $this->hasMany(CriterioBusquedaAlquiler::class, 'id_cliente');
    }

    public function  criterio_busqueda_venta()
    {
        return $this->hasMany(CriterioBusquedaVenta::class, 'id_cliente');
    }

    public function asesor()
    {
        return $this->belongsTo(Usuario_sector::class, 'id_asesor_venta', 'id_usuario');
    }

    public function asesor_alquiler()
    {
        return $this->belongsTo(Usuario_sector::class, 'id_asesor_alquiler', 'id_usuario');
    }

    
}
