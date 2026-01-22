<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Padron extends Model
{
    use HasFactory;
    //Asociacion con la tabla padron de la BD
    protected $table = 'padron';

    // Especifica los campos fillable si es necesario
    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'fecha_nacimiento',
        'calle',
        'numero_calle',
        'piso_departamento',
        'ciudad',
        'provincia',
        'notes',
        'last_modified_by'
    ];



    public function telefonos()
    {
        return $this->hasMany(Padron_telefonos::class, 'padron_id');
    }
    public function propiedad()
    {
        return $this->belongsToMany(Propiedad::class, 'propiedades_padron', 'padron_id', 'propiedad_id')
            ->withPivot('observaciones', 'baja', 'fecha_baja', 'observaciones_baja', 'last_modified_by')
            ->withTimestamps();
    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }

    // En el modelo Propiedades_padron
public function padron()
{
    return $this->belongsTo(Padron::class, 'padron_id');
}
}
