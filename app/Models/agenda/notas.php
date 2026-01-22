<?php

namespace App\Models\agenda;

use Illuminate\Database\Eloquent\Model;
use App\Models\agenda\Agenda;
use App\Models\At_cl\Usuario;
use App\Models\cliente\clientes;
use App\Models\At_cl\Propiedad;

class Notas extends Model
{
    protected $connection = 'mysql6';
    protected $table = 'notas';
    protected $fillable = [
        'agenda_id',
        'descripcion',
        'usuario_id',
        'hora_inicio',
        'hora_fin',
        'activo',
        'creado_por',
        'cliente_id',
        'propiedad_id',
        'fecha',
        'realizado',
        'devoluciones'
    ];
    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    public function cliente()
    {
        return $this->belongsTo(clientes::class, 'cliente_id');
    }
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }
    public function creado_por()
    {
        return $this->belongsTo(Usuario::class);
    }
}
