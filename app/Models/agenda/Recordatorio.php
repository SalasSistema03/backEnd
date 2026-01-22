<?php

namespace App\Models\agenda;

use Illuminate\Database\Eloquent\Model;
use App\Models\agenda\Sectores;
use App\Models\At_cl\Usuario;

class Recordatorio extends Model
{
    protected $connection = 'mysql6';
    protected $table = 'recordatorio';

    protected $fillable = [
        'descripcion',
        'agenda_id',
        'fecha_inicio',
        'intervalo',
        'fecha_actualizacion',
        'fecha_fin',
        'usuario_carga',
        'usuario_finaliza',
        'activo',
        'hora',
        'cantidad',
        'repetir',
        'finalizado',
        'es_asesor',
        'es_asesor_activo',
    ];

    public function sector()
    {
        return $this->belongsTo(Sectores::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function creado_por()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function agenda()
    {
        return $this->belongsTo(\App\Models\agenda\Agenda::class);
    }
}
