<?php

namespace App\Http\Controllers\At_cl;

use Illuminate\Http\Request;
use App\Models\At_cl\Tipo_inmueble;

class Tipo_inmuebleController 
{
    
    public function index()
    {
        $tipo_inmueble = Tipo_inmueble::all();
        return response()->json($tipo_inmueble);
    }

    
    public function create(){}

    
    public function store(Request $request){}


    public function show(string $id){}

    
    public function edit(string $id){}

    
    public function update(Request $request, string $id){}

    
    public function destroy(string $id){}
    
}
