<?php

namespace App\Models\At_cl;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Padron_telefonos extends Model
{
    use HasFactory;
    //Asociacion con la tabla padron_telefonos de la BD
    protected $table = 'padron_telefonos';
    protected $fillable = [
        'phone_number',
        'notes',
        'padron_id',
        'last_modified_by',
    ];

    // Relación inversa con Padron
    public function padron()
    {
        return $this->belongsTo(Padron::class, 'padron_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }
}
