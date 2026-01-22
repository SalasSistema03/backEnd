<?php

namespace App\Models\Contable\retenciones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Padron_retenciones extends Model
{
    protected $connection = 'mysql8';
    protected $table = 'padron_retencion';
    protected $fillable = [
        'cuit_retencion',
        'razon_social_retencion',
        'domicilio_retencion',
        'localidad_retencion',
        'id_provincia_retencion',
        'codigo_postal_retencion',
        'last_modified_by',
    ];
    public $timestamps = false;
}
