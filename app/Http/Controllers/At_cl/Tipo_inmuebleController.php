<?php

namespace App\Http\Controllers\At_cl;

use Illuminate\Http\Request;
use App\Models\At_cl\Tipo_inmueble;
use App\Services\At_cl\InmuebleService;

class Tipo_inmuebleController 
{
    
    public function getTiposInmueble()
    {
        $tipo_inmueble = (new InmuebleService())->getInmuebles();
        return response()->json($tipo_inmueble);
    }

  

    
    public function create(){}

    
    public function store(Request $request){}


    public function show(string $id){}

    
    public function edit(string $id){}

    
    public function update(Request $request, string $id){}

    
    public function destroy(string $id){}
    
}
