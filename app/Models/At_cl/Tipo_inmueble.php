<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_inmueble extends Model
{
    use HasFactory;
    //Asociacion con la tabla localidad de la BD
    protected $connection = 'mysql'; // 👈 ¡Esto es lo más importante!

    protected $table = 'tipo_inmueble';
    protected $primaryKey = 'id'; // si la columna se llama "id", explícitalo


    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_inmueble');
    }

    public function tasaciones()
    {
        return $this->hasMany(Tasaciones::class, 'tipo_inmueble');
    }
}
