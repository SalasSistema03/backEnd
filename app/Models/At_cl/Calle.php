<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calle extends Model
{
    use HasFactory;
    //Asociacion con la tabla calle de la BD
    protected $table = 'calle';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_calle');
    }
}
