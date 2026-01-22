<?php

namespace App\Models\agenda;

use Illuminate\Database\Eloquent\Model;
use App\Models\agenda\Sectores;
use App\Models\At_cl\Usuario;

class Agenda extends Model
{
    protected $connection = 'mysql6';
    protected $table = 'agenda';

    protected $fillable = [
        'sector_id',
        'usuario_id',
    ];

    public function sector()
    {
        return $this->belongsTo(Sectores::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    
}
