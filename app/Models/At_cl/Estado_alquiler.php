<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Model;

class Estado_alquiler extends Model
{
    protected $table = 'estado_alquileres';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_estado_alquiler');
    }
}
