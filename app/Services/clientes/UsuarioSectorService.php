<?php

namespace App\Services\clientes;

use App\Models\cliente\Usuario_sector;

class UsuarioSectorService
{
    public function getAllUsuarioSector()
    {
        $sectores = Usuario_sector::all();
        return $sectores->map(function ($sector) {
            $sector->usuario_nombre = $sector->usuario->nombre ?? null;   // o 'name' si tu campo es así
            $sector->usuario_username = $sector->usuario->username ?? null;
            return $sector;
        });
    }
    public function getAllUsuarioTasacion()
    {
        $sectores = Usuario_sector::where('tasador', 'S')->get();

        return $sectores->map(function ($sector) {
            $sector->usuario_nombre = $sector->usuario->name ?? null;
            $sector->usuario_username = $sector->usuario->username ?? null;

            return $sector;
        });
    }
}
