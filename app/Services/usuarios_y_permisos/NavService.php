<?php

namespace App\Services\usuarios_y_permisos;

use App\Models\usuarios_y_permisos\Nav;

class NavService
{


    public function getNavPermitidos(array $navsPermitidos)
    {
        return Nav::whereIn('id', $navsPermitidos)->get();
    }
}
