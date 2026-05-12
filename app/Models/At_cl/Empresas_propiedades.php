<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresas_propiedades extends Model
{
    use HasFactory;
    //Asociacion con la tabla calle de la BD
    protected $table = 'empresa_propiedad';

    public $timestamps = false;
    protected $fillable = [
        'propiedad_id',
        'empresa_id',
        'folio',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }
}
