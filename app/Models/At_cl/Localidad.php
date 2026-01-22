<?php
namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    use HasFactory;
    //Asociacion con la tabla localidad de la BD
    protected $table = 'localidad';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_localidad');
    }
}
