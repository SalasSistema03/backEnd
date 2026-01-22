<?php

namespace App\Http\Controllers\clientes;

use App\Services\clientes\CategoriasService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    protected $categoriasService;

    public function __construct(CategoriasService $categoriass)
    {
        $this->categoriasService = $categoriass;
    }

    public function getCategorias()
    {
        $categorias = $this->categoriasService->getAllCategorias();
        return response()->json($categorias);
    }

}
