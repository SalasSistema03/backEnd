<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class Agua_carga extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'agua_carga';

    // Si el campo 'id' no es 'id', entonces debes configurarlo así:
    protected $primaryKey = 'id';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Si no usas timestamps (created_at, updated_at), desactívalos:
    public $timestamps = false;


    protected $fillable = [
        'codigo_barra',
        'compartidos',
        'importe',
        'fecha_vencimiento',
        'periodo_anio',
        'periodo_mes',
        'num_broche',
        'comienza',
        'rescicion',
        'bajado',
        'id_aguaPadron',
        'armado',
        'controlado'
    ];


    //Este metodo vincula id_aguaPadron con id de Agua_padron
    public function padron()
    {
        return $this->belongsTo(Agua_padron::class, 'id_aguaPadron', 'id');
    }
}
