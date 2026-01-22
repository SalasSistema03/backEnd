<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    
    use HasFactory;
    
    //Asociacion con la tabla barrio de la BD
    protected $table = 'barrio';
    
    // Campos asignables masivamente
    protected $fillable = [
        'name',
    ];

    /**
     * Relación con el modelo `Propiedad` (relación inversa).
     */
     public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_barrio');
    } 
}
