<?php

namespace App\Models\Contable\retenciones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante_retencion extends Model
{
    protected $connection = 'mysql8';
    protected $table = 'comprobante_retencion';
    protected $primaryKey = 'id_comprobante';
    protected $fillable = [
        'fecha_comprobante',
        'numero_comprobante',
        'suma_comprobante',
        'importe_comprobante',
        'cuit_retencion',
        'importe_retencion',
        'calcula_base',
        'fecha_retencion',
        'last_modified_by',
    ];
    public $timestamps = false;
}
