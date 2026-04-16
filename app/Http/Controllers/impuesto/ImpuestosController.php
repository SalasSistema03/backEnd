<?php

namespace App\Http\Controllers\impuesto;


use App\Http\Controllers\Controller;
use App\Services\impuesto\TGI\CargaTgiService;
use App\Services\impuesto\TGI\PadronTgiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\impuesto\Tgi_padron;


class ImpuestosController extends Controller
{



    public function actualizarPadron($impuesto)
    {
        if ($impuesto === 'tgi') {
            return (new PadronTgiService())->actualizarPadronTGI();
        }
    }


    public function filtradoPadron(Request $request, $impuesto)
    {
        if ($impuesto === 'tgi') {
            return (new PadronTgiService())->obtenerPadronFiltradoTGI($request, $impuesto);
        }
    }


    public function actualizarImpuesto(Request $request)
    {
        if ($request->impuesto === 'tgi') {
            return (new PadronTgiService())->actualizarTGI($request);
        }
    }

    public function padronCarga(Request $request)
    {
        if ($request->impuesto === 'tgi') {
            return app(CargaTgiService::class)->padronCargaTGI($request);
        }
    }

    public function cargaManual(Request $request)
    {
        if ($request->impuesto === 'tgi') {
            try {
                return app(PadronTgiService::class)->obtenerRegistroPadronManual($request->folio, $request->empresa);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function cargaNuevoManual(Request $request)
    {
        if ($request->impuesto === 'tgi') {
            return app(CargaTgiService::class)->cargarNuevoTgiServiceManual($request);
        }
    }
}
