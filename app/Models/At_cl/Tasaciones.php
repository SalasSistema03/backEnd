<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\usuarios_y_permisos\Usuario;
use App\Models\cliente\Usuario_sector;

class Tasaciones extends Model
{
    use HasFactory;
    protected $table = 'tasaciones';
    public $timestamps = false;

    protected $fillable = [
        'tipo_inmueble',
        'calle',
        'numero_calle',
        'propietario',
        'fecha',
        'tasacion_venta',
        'tipo_tasacion',
        'tasacion_alquiler',
        'origen',
        'tasador',
        'costo_tasacion',
        'observaciones',
        'folio',
        'tasacion_informada_prop',
        'piso',
        'departamento',
        'id_localidad',
        'asignada',
        'id_propiedad'
    ];

    protected $connection = 'mysql';

    public function tipoInmueble()
    {
        return $this->belongsTo(Tipo_inmueble::class, 'tipo_inmueble');
    }

    public function calleRelacion()
    {
        return $this->belongsTo(Calle::class, 'calle');
    }

    public function propietarioRelacion()
    {
        return $this->belongsTo(Padron::class, 'propietario');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'id_localidad');
    }

    /* public function tasadorUsuario()
    {
        return $this->belongsTo(Usuario::class, 'tasador');
    } */

    public function tasadorSector()
    {
        return $this->belongsTo(Usuario_sector::class, 'tasador', 'id_usuario')
            ->where('tasador', 'S');
    }
}
