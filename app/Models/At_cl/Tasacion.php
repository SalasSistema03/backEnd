<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasacion extends Model
{
    use HasFactory;
    protected $table = 'tasacion';

    protected $fillable = [
        'moneda',
        'tasacion_pesos_venta',
        'tasacion_dolar_venta',
        /* 'tasacion_dolar_alquiler',
        'tasacion_pesos_alquiler', */
        'fecha_tasacion',
        'propiedad_id',
        'comentario_tasacion'
    ];
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }
    
}
