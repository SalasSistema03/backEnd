<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Exp_administrador_consorcio extends Model
{
    use HasFactory;

    // Conexión a MySQL 9 (definida en config/database.php)
    protected $connection = 'mysql9';

    // Nombre de la tabla
    protected $table = 'exp_administrador_consorcio';

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos que se pueden asignar en masa
    protected $fillable = [
        'nombre',
        'cuit',
        'rubro',
        'contacto',
        'pagina_web',
        'direccion',
        'altura'
    ];
    

    // Opcional: si no usás timestamps (created_at, updated_at)
    public $timestamps = false;

    // Un administrador de consorcio puede tener varios exp_edificios
    public function exp_edificios()
    {
        return $this->hasMany(Exp_edificio::class);
    }

    // un exp_administrador_consorcio puede tener varios exp_broches
    public function exp_broches()
    {
        return $this->hasMany(Exp_broche::class, 'administra');
    }
}
