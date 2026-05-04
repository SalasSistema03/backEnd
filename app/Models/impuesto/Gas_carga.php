<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class Gas_carga extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'gas_carga';

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
        'id_gasPadron',
        'inicio_liquidacion',
        'fin_liquidacion',
        'liquidacion',
        'armado',
        'controlado'
    ];


    //Este metodo vincula id_gasPadron con id de Gas_padron
    public function padron()
    {
        return $this->belongsTo(Gas_padron::class, 'id_gasPadron', 'id');
    }
}
