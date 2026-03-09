<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Calle;
use Illuminate\Support\Facades\DB;
use App\Services\At_cl\CalleService;



class CalleController
{
    public function getCalles()
    {
        $calles = (new CalleService())->getCalles();
        return response()->json($calles);
    }
}
