<?php

namespace App\Services\contable\sellado;

use Illuminate\Support\Facades\DB;
use App\Models\usuarios_y_permisos\Permiso; // Ensure this is the correct namespace for the Permiso class

class PermitirAccesoSelladoService
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }



    public function tieneAcceso($btnNombre)
    {
        $permiso = Permiso::where('usuario_id', $this->userId)
        ->whereHas('boton', function ($query) use ($btnNombre) {
            $query->where('btn_nombre', $btnNombre);
        })
        ->first();

    return $permiso !== null;
    }

    public function tieneAccesoUsuario($usuarioId, $botonNombre){
        $permiso = Permiso::where('usuario_id', $usuarioId)
        ->whereHas('boton', function ($query) use ($botonNombre) {
            $query->where('btn_nombre', $botonNombre);
        })
        ->first();

    return $permiso !== null;
    }


}
