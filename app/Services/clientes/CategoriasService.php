<?php

namespace App\Services\clientes;

use App\Models\cliente\categorias;

class CategoriasService
{
    public function getAllCategorias()
    {
        $categorias = categorias::all();
        return $categorias;
    }
}
