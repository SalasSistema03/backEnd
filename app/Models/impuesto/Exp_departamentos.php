<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class Exp_departamentos extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'exp_departamentos';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'folio',
        'piso',
        'unidad',
        'administra',
        'propietario',
        'id_exp_edificios'
    ];


    public function edificios(){
        return $this->belongsto(Exp_edificios::class, 'id_exp_edificios', 'id');
    }

    public function broches(){
        return $this->hasMany(Exp_broche::class, 'id_exp_broches', 'id');
    }
    
}
