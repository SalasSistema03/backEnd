<?php

namespace App\Models\impuesto;

use App\Models\At_cl\Propiedad;
use Illuminate\Database\Eloquent\Model;

class Epe extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'epe';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'cliente',
        'plan',
        'ruta',
        'folio',
        'ds',
        'propiedad_id'
    ];

    public function propiedad()
    {
        return $this->hasOne(Propiedad::class, 'id', 'propiedad_id');
    }
}
