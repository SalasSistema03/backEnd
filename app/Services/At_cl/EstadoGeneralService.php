<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Estado_general;


class EstadoGeneralService
{
  public function getEstadoGeneral()
  {
    return Estado_general::select('id', 'estado_general')->get();
  }
}
