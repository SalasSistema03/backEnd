<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observaciones_propiedades extends Model
{
    use HasFactory;
    //Asociacion con la tabla localidad de la BD
    protected $table = 'observaciones_propiedades';

    public function propiedad()
    {

        return $this->belongsTo(Propiedad::class);
    }
    public function lastModifiedBy()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by'); // Asegúrate que 'last_modified_by' sea el nombre correcto de la columna
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }
}
