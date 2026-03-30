<?php

namespace App\Services\clientes;

use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Botones;
use App\Models\usuarios_y_permisos\Permiso;
use Illuminate\Support\Facades\Log;

class Permisos
{
    //Este servicio se encarga de verificar los permisos de acceso a botones o elementos en la interfaz
    //El nombre del elemento es el nombre del registro en la tabla  "permisos"
    public static function verificarAccesoBotones_Elementos($nombreElemento)
    {
        /* $usuario = Usuario::find(session('usuario_id')); */
        $id_usuario = auth('api')->id();
        $usuario = Usuario::find($id_usuario);

        if (!$usuario) {
            return response()->json(false);
        }

        // Busca un permiso para el usuario donde el botón asociado tenga el nombre especificado
        $tienePermiso = $usuario->permisos()
            ->whereHas('boton', function ($query) use ($nombreElemento) {
                $query->where('btn_nombre', $nombreElemento);
            })
            ->exists(); // Usamos exists() para mayor eficiencia

        if (!$tienePermiso) {
            return false;
        }

        return response()->json($tienePermiso);
    }
}
