<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Usuario;

class Foto_inventario extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'fotos_inventario';

    protected $fillable = [
        'id_proceso_propiedad',
        'propiedad_id',
        'url',
        'notes',
        'quien_cargo',
    ];

    public function quien_cargo()
    {
        return $this->belongsTo(Usuario::class, 'quien_cargo', 'id');
    }
}
