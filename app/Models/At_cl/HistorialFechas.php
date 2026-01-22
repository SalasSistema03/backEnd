<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialFechas extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo (opcional si sigue la convención de nombres)
    protected $table = 'historial_fechas';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'propiedad_id',
        'fecha_de_alta',
        'fecha_de_baja',
    ];

    /**
     * Relación con el modelo Propiedad.
     * Un historial de fechas pertenece a una propiedad.
     *
     * @return BelongsTo
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }
}
