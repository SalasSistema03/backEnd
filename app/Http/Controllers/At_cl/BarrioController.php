<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Barrio;

class BarrioController 
{
   public function barriosAll(){

       $barrios = Barrio::all();

       return $barrios;

    }
    
}
