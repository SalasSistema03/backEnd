<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Model;

class View_propiedades_sys extends Model
{
    // Conexión a la base de datos
    protected $connection = 'mysql2';

    // Nombre de la vista
    protected $table = 'view_propiedades';

    // Clave primaria de la vista
    protected $primaryKey = 'id_casa';

    // La vista no administra fechas de creación ni modificación
    public $timestamps = false;

    // La vista se utiliza únicamente para consultas
    protected $guarded = [];
}
