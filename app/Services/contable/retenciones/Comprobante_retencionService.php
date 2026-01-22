<?php

namespace App\Services\contable\retenciones;

use App\Models\Contable\retenciones\Comprobante_retencion;


class Comprobante_retencionService
{
    /* protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    } */



    public function devolverComprobanteRetencion()
    {
        $comprobante= Comprobante_retencion::all();
        return response()->json($comprobante);
    }

    public function comprobantesPorId($id)
    {
        $comprobante = Comprobante_retencion::find($id);
        return response()->json($comprobante);
    }

}