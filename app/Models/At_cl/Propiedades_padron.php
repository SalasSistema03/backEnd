<?php


namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\At_cl\Propiedad;
use App\Models\usuarios_y_permisos\Usuario;

class Propiedades_padron extends Model
{
    use HasFactory;
    protected $table = 'propiedades_padron';
    protected $fillable = ['padron_id', 'propiedad_id', 'last_modified_by','observaciones', 'baja', 'fecha_baja', 'observaciones_baja'];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }
}
