<?php
namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class estado_general extends Model
{
    use HasFactory;
    //Asociacion con la tabla estado_general de la BD
    protected $table = 'estado_general';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_estado_general');
    }
}
