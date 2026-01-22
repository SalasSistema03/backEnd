<?php
namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;
    //Asociacion con la tabla provincia de la BD
    protected $table = 'provincia';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_provincia');
    }
}
