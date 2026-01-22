<?php

namespace App\Models\cliente;

use App\Models\At_cl\Usuario;
use Illuminate\Database\Eloquent\Model;

class Usuario_sector extends Model
{
    protected $connection = 'mysql5';

    protected $table = 'usuario_sector';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id_usuario_sector';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'id_usuario',
        'venta',
        'alquiler',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
