<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\impuesto\Exp_Unidades;

class Exp_broche extends Model
{
    use HasFactory;

    // conexión
    protected $connection = 'mysql9';

    // nombre de la tabla
    protected $table = 'exp_broche';

    // clave primaria
    protected $primaryKey = 'id';

    // la tabla no tiene timestamps
    public $timestamps = false;

    // campos asignables
    protected $fillable = [
        'vencimiento',
        'extraordinaria',
        'ordinaria',
        'total',
        'periodo',
        'anio',
        'unidad',
        'administra',
    ];

   // un exp_broche púede tener un solo exp_unidad utilizando id_unidad
    public function exp_unidad()
    {
        return $this->hasOne(Exp_Unidades::class, 'id', 'id_unidad');
    }

    // un exp_broche puede tenerr un solo exp_administrador_conmsorcio a traves de admnistra
    public function exp_administrador_consorcio()
    {
        return $this->hasOne(Exp_administrador_consorcio::class, 'id', 'administra');
    }
}
