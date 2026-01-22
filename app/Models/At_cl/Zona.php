<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    use HasFactory;
    //Asociacion con la tabla zona de la BD
    protected $connection = 'mysql';
    protected $table = 'zona';
    protected $primaryKey = 'id';

    public function propiedades()
    {
        return $this->belongsTo(Propiedad::class, 'id_zona');
    }
}
