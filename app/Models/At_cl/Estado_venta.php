<?php
namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Model;

class Estado_venta extends Model
{
    protected $table = 'estado_ventas';

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_estado_venta');
    }
}
