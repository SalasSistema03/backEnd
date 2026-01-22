<?php

namespace App\Services\clientes;

use App\Models\cliente\Usuario_sector;
use App\Models\At_cl\Usuario;


class UsuarioSectorService
{
    public function getAllUsuarioSector()
    {
        $sectores = Usuario_sector::all();
        return $sectores->map(function($sector) {
        $sector->usuario_nombre = $sector->usuario->nombre ?? null;   // o 'name' si tu campo es así
        $sector->usuario_username = $sector->usuario->username ?? null;
        return $sector;
    });
    }
}
