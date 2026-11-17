<?php

namespace App\Models\Dpto;

use Illuminate\Database\Eloquent\Model;

class InventarioVideo extends Model
{
    protected $connection = 'mysql10';
    protected $table = 'videos_inventario';
    protected $fillable = ['id_proceso_propiedad', 'url', 'notes'];
}
