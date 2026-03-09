<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Estado_venta;


class EstadoVentaService
{
  public function getEstadoVenta()
  {
    return Estado_venta::select('id', 'name')->get();
  }
}
