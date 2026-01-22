<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    use HasFactory;
    //Asociacion con la tabla calle de la BD
    protected $table = 'empresas';

       protected $fillable = [
        'nombre',
    ];
}
