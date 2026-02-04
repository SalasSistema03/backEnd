<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Estado_alquiler;


class EstadoAlquilerService
{
  public function getEstadoAlquiler()
  {
    return Estado_alquiler::select('id', 'name')->get();
  }
}
