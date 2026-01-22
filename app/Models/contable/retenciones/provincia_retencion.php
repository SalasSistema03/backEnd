<?php

namespace App\Models\Contable\retenciones;

use Illuminate\Database\Eloquent\Model;

class Provincia_retencion extends Model
{
    protected $connection = 'mysql8';

    protected $table = 'provincia_retencion';

    protected $fillable = [
       'nombre_provincia_retencion',
       'numero_provincia_retencion'
    ];
}
