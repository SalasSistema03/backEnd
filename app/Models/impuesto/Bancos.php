<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bancos extends Model
{
    use HasFactory;

    // Conexión a MySQL 9
    protected $connection = 'mysql9';

    // Nombre de la tabla
    protected $table = 'bancos';

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos asignables
    protected $fillable = [
        'cbu',
        'nombre'
    ];

    // Si la tabla no tiene created_at / updated_at
    public $timestamps = false;
}
