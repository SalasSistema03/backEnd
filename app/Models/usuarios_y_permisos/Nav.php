<?php

namespace App\Models\usuarios_y_permisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Vista;
use App\Models\usuarios_y_permisos\Permiso;


class Nav extends Model
{
    use HasFactory;
    protected $connection = 'mysql4';
    protected $table = 'nav';

    protected $fillable = [
        'menu',
    ];

    public $timestamps = true;

    public function vistas()
    {
        return $this->hasMany(Vista::class, 'menu_id');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'nav_id');
    }
}
