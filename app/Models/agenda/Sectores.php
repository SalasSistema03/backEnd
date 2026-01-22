<?php

namespace App\Models\agenda;

use Illuminate\Database\Eloquent\Model;

class Sectores extends Model
{
    protected $connection = 'mysql6';
    protected $table = 'sectores';

    protected $fillable = [
        'nombre',
    ];
}
