<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Provincia;

class ProvinciaService
{
    public function getProvincias(){
        return Provincia::select('id', 'name')->get();
    }
}
