<?php

namespace App\Services\turno;


use App\Models\turnos\Sector;

class SectoresService
{
    public function getSectoresOrdenados()
    {
        
        $sectores = Sector::orderBy('nombre', 'asc')->get();
       
        return $sectores;
    }
}
