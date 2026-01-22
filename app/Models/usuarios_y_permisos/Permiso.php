<?php

namespace App\Models\usuarios_y_permisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Nav;
use App\Models\usuarios_y_permisos\Botones;
use App\Models\usuarios_y_permisos\Vista;

class Permiso extends Model
{
    use HasFactory;

    // Especifica la conexión de la base de datos
    protected $connection = 'mysql4';

    // Especifica la tabla asociada al modelo
    protected $table = 'permisos';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'vista_id',
        'boton_id',
        'usuario_id',
        'nav_id',
    ];

    /**
     * Relación con el modelo Vista.
     */
    public function vista()
    {
        return $this->belongsTo(Vista::class, 'vista_id');
    }

    /**
     * Relación con el modelo Boton.
     */
    public function boton()
    {
        return $this->belongsTo(Botones::class, 'boton_id');
    }

    /**
     * Relación con el modelo Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    public function nav()
    {
        return $this->belongsTo(Nav::class, 'nav_id');
    }
}
