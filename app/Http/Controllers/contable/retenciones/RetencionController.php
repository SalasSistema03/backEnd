<?php

namespace App\Http\Controllers\contable\retenciones;


use App\Http\Controllers\Controller;
use App\Services\contable\retenciones\RetencionService;

class RetencionController extends Controller
{
 
    public function __construct(
        protected RetencionService $retencionService
    ){}

    public function getPadronRetencionController()
    {
        try {
            $padronRetenciones = $this->retencionService->getPadronRetencion();
            return response()->json([
                'status' => 'success',
                'data' => $padronRetenciones
            ], 200);
        } catch (\Exception $e) {
            // Si algo falla (conexión, base de datos, etc.), esto te dirá QUÉ es en Postman
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el padrón de retenciones',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

}