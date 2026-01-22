<?php

namespace App\Models\turnos;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $connection = 'mysql7';
    protected $table = 'sectores';
    protected $fillable = [
        'nombre',
        'activo'
    ];
    
}

