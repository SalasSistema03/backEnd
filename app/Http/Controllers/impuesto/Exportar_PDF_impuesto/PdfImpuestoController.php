<?php

namespace App\Http\Controllers\impuesto\Exportar_PDF_impuesto;

use App\Services\impuesto\IMPUESTO\PDF_IMPUESTO\PdfImpuesto;
use App\Services\impuesto\TGI\CargaTgiService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PdfImpuestoController
{
    protected $cargarTgiService;
    protected $usuario, $id_usuario;

    public function __construct(CargaTgiService $cargarTgiService)
    {
        $this->cargarTgiService = $cargarTgiService;
    }

    public function PDF_broche(Request $request)
    {
        if($request->impuesto === 'tgi' || $request->impuesto === 'agua'){
        $data = (new PdfImpuesto)->obtenerRegistrosPorBroche($request->anio, $request->mes, $request->impuesto);
        return response()->json($data);

        }
    }


    // Este metodo genera el pdf del borche de SALAS consumiendo el servicio obtenerRegistrosDesdeFolio50000
    public function PDF_BorcheSalas(Request $request)
    {
        $data = (new PdfImpuesto)->obtenerRegistrosDesdeFolio50000($request->anio, $request->mes, $request->impuesto);

        Log::info('data', $data);
        return response()->json($data);
    }
}
