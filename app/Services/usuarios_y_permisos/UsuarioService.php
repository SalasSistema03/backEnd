<?php

namespace App\Services\usuarios_y_permisos;

use Tymon\JWTAuth\Facades\JWTAuth;
class UsuarioService
{

    public function devolverUsuario()
    {
        // Obtener el usuario autenticado desde el token JWT
        $user = JWTAuth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        return $user;
    }

    /* public function esAdmin(){
        $user = auth('api')->user();
        if($user->admin == 1){
            return response()->json(['message' => 'Usuario es admin'],200);
        }
        return response()->json(['message' => 'Usuario no es admin'],401);
    } */
}