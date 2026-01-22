<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Precio extends Model
{
    use HasFactory;
    //Asociacion con la tabla localidad de la BD
    protected $table = 'precio';

    // Atributos que se pueden asignar en masa
    protected $fillable = [
        'moneda',
        'moneda_alquiler_dolar',
        'moneda_alquiler_pesos',
        'alquiler_fecha_alta',
        'alquiler_fecha_baja',
        'moneda_venta_pesos',
        'moneda_venta_dolar',
        'venta_fecha_alta',
        'venta_fecha_baja',
        'propiedad_id'
    ];
     public function precio()
    {
        return $this->hasOne(Precio::class, 'propiedad_id');
    } 

    

}
