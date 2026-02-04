<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Tipo_inmueble;


class InmuebleService
{
  public function getInmuebles()
  {
    return Tipo_inmueble::select('id', 'inmueble')->get();
  }
}
