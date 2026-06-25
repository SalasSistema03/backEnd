<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exp_edificio extends Model
{
    use HasFactory;

    // 👉 Conexión a MySQL 9
    protected $connection = 'mysql9';

    // 👉 Nombre de la tabla
    protected $table = 'exp_edificios';

    // 👉 Clave primaria
    protected $primaryKey = 'id';

    // 👉 Campos asignables
    protected $fillable = [
        'direccion',
        'altura',
        'nombre_consorcio',
        'id_administrador_consorcio',
    ];

    // 👉 Si la tabla no tiene created_at / updated_at
    public $timestamps = false;



    // Un edificio puede tener un solo exp_administrador_consorcio
    public function exp_administrador_consorcio()
    {
        return $this->hasOne(Exp_administrador_consorcio::class);
    }

    // Un edificio puede tener muchas exp_unidades
    /* public function exp_unidades()
    {
        return $this->hasMany(ExpUnidad::class);
    } */

        // Definimos la relación: "Un edificio pertenece a un administrador"
    public function administrador()
    {
        return $this->belongsTo(Exp_administrador_consorcio::class, 'id_administrador_consorcio', 'id');
    }
}
