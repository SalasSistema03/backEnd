<?php

namespace App\Models\impuesto;

use Illuminate\Database\Eloquent\Model;

class Api_carga extends Model
{
    protected $connection = 'mysql9';

    protected $table = 'api_carga';

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
        'origen_carga',
        'comienza',
        'rescicion',
        'id_apiPadron',
        'bajado',
        'armado',
        'controlado'
    ];


    //Este metodo vincula id_tgi_padron con id de Tgi_padron
    public function padron()
    {
        return $this->belongsTo(Api_padron::class, 'id_apiPadron', 'id');
    }
}
