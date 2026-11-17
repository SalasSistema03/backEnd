<?php

namespace App\Models\proceso;

use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Usuario;

class Documento_inventario extends Model
{
    protected $connection = 'mysql10';

    protected $table = 'documentos_inventario';

    protected $fillable = [
        'id_proceso_propiedad',
        'propiedad_id',
        'url',
        'quien_cargo',
    ];

    // El nombre original no se guarda: sale de la URL (el archivo en disco tiene ese mismo nombre).
    protected $appends = ['nombre_original'];

    public function getNombreOriginalAttribute(): string
    {
        return basename(rawurldecode((string) $this->url));
    }

    public function quien_cargo()
    {
        return $this->belongsTo(Usuario::class, 'quien_cargo', 'id');
    }
}
