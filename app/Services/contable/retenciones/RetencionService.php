<?php

namespace App\Services\contable\retenciones;

use App\Models\Contable\retenciones\Padron_retenciones;


class RetencionService
{
    /* protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    } */

    public function getPadronRetencion()
    {
        return Padron_retenciones::all();
    }

}