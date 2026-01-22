<?php

namespace App\Models\usuarios_y_permisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\Nav;
use App\Models\usuarios_y_permisos\Botones;


class Vista extends Model
{
    use HasFactory;
    protected $connection = 'mysql4';
    protected $table = 'vistas';

    protected $fillable = [
        'vista_nombre',
        'menu_id',
    ];

    public function menu()
    {
        return $this->belongsTo(Nav::class, 'menu_id');
    }

    public function botones()
    {
        return $this->hasMany(Botones::class, 'vista_id');
    }

     public function permisos()
    {
        return $this->hasMany(Permiso::class, 'vista_id');
    }
    
    
}