<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exp_unidades_sys extends Model
{
    use HasFactory;

    // conexión a MySQL 9
    protected $connection = 'mysql9';

    // tabla
    protected $table = 'exp_unidades_sys';

    // clave primaria (bigint unsigned auto_increment)
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    // no tiene created_at/updated_at en la estructura que pasaste
    public $timestamps = false;

    // asignación masiva
    protected $fillable = [
        'folio',
        'casa',
        'comienza',
        'vencimiento',
        'ubicacion',
        'comision',
        'administra',
        'id_empresa'

    ];

 
    
}
