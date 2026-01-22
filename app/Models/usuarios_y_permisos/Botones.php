<?php

namespace App\Models\usuarios_y_permisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\Vista;

class Botones extends Model
{
    use HasFactory;
    protected $connection = 'mysql4';
    protected $table = 'botones';

    protected $fillable = [
        'btn_nombre',
        'vista_id',
    ];

    public function vista()
    {
        return $this->belongsTo(Vista::class, 'vista_id');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'boton_id');
    }
}