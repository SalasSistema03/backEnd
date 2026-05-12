<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    use HasFactory;
    //Asociacion con la tabla calle de la BD
    protected $table = 'empresas';

       protected $fillable = [
        'nombre',
    ];

    public function propiedades()
    {
        return $this->belongsToMany(Propiedad::class, 'empresa_propiedad', 'empresa_id', 'propiedad_id')
            ->withPivot('folio');
    }
}
