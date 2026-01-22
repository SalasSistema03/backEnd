<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exp_Unidades extends Model
{
    use HasFactory;

    // conexión a MySQL 9
    protected $connection = 'mysql9';

    // tabla
    protected $table = 'exp_unidades';

    // clave primaria (bigint unsigned auto_increment)
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    // no tiene created_at/updated_at en la estructura que pasaste
    public $timestamps = false;

    // asignación masiva
    protected $fillable = [
        'id_edificio',
        'tipo',
        'piso',
        'depto',
        'id_casa',
        'unidad',
        'observaciones',
        'estado'

    ];

    // relación opcional: unidad pertenece a un exp_edificio
    public function exp_edificio()
    {
        return $this->belongsTo(Exp_edificio::class, 'id_edificio');
    }

    // un exp_unidades puede tener varios exp'_broche
    public function exp_broches()
    {
        return $this->hasMany(Exp_broche::class, 'id_unidad');
    }
    
}
