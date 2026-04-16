<?php
namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Usuario;

class Documentacion extends Model
{
    use HasFactory;
    //Asociacion con la tabla localidad de la BD
    protected $table = 'documentacion';
    protected $fillable = ['propiedad_id', 'url', 'notes'];

     /**
     * Relación con el modelo Propiedad.
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }
}
