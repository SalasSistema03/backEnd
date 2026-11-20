<?php

namespace App\Models\At_cl;

use App\Models\usuarios_y_permisos\Usuario;
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
        /* 'tasacion_dolar_alquiler',*/
        'tasacion_pesos_alquiler',
        'fecha_tasacion',
        'propiedad_id',
        'id_usuario',
        'comentario_tasacion'
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
