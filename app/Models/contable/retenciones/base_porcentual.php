<?php

namespace App\Models\Contable\retenciones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Base_porcentual extends Model
{
    protected $connection = 'mysql8';
    protected $table = 'base_porcentual';
    protected $primaryKey = 'id_base_porcentual';
    public $incrementing = false;
    protected $fillable = [
        'nombre',
        'dato',
        'last_modified_by',
    ];
    public $timestamps = false;
}
