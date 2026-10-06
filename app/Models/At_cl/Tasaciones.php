<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasaciones extends Model
{
    use HasFactory;
    protected $table = 'tasaciones';

    protected $fillable = [
        'id_propiedad',
        'tipo_inmueble',
        'calle',
        'numero_calle',
        'propietario',
        'fecha',
        'tasacion_venta',
        'telefono',
        'tipo_tasacion',
        'tasacion_alquiler',
        'origen',
        'tasador',
        'costo_tasacion',
        'observaciones',
        'empresa',
        'folio',
        'tasacion_informada_prop',
        'anio_mes',
        'piso',
        'departamento'
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad');
    }
    
    
    
}
