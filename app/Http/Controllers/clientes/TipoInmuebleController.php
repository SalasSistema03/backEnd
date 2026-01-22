<?php

namespace App\Http\Controllers\clientes;

use App\Http\Controllers\Controller;
use App\Services\clientes\TipoInmuebleService;
use Illuminate\Http\Request;

class TipoInmuebleController extends Controller
{
    protected $tipoInmuebleService;

    public function __construct(TipoInmuebleService $inmuebles)
    {
        $this->tipoInmuebleService = $inmuebles;
    }

    public function getCategorias()
    {
        $categorias = $this->tipoInmuebleService->getTipoInmueble();
        return response()->json($categorias);
    }
}
