<?php

namespace App\Services\contable\retenciones;

use App\Models\Contable\retenciones\Padron_retenciones;


class Padron_retencionesService
{
    /* protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    } */



    public function devolverPersonasRetenciones()
    {
        $personas = Padron_retenciones::all();
        return response()->json($personas);
    }

}