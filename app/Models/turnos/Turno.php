<?php

namespace App\Models\turnos;

use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Usuario;

class Turno extends Model
{
    protected $connection = 'mysql7';
    protected $fillable = [
        'numero_identificador',
        'tipo_identificador',
        'sector',
        'usuario_id',
        'fecha_carga',
        'fecha_llamado',
        'activo'
    ];

    protected $casts = [
        'fecha_carga' => 'datetime',
        'fecha_llamado' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector', 'id');
    }

    // Accesor para obtener el nombre del sector directamente
    public function getSectorNombreAttribute()
    {
        return $this->sector ? $this->sector->nombre : null;
    }

    
}
