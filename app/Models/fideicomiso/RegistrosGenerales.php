<?php

namespace App\Models\fideicomiso;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrosGenerales extends Model
{
    use HasFactory;

    protected $connection = 'mysql11';

    protected $table = 'registros_generales';

    public $timestamps = false;

    protected $fillable = [
        'tgi',
        'agua',
        'api',
        'luz',
        'seguro',
        'limpieza',
        'ascensor',
        'honorario',
        'periodo',
        'vencimiento',
    ];
}