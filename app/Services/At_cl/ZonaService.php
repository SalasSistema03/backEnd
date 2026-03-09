<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Zona;


class ZonaService
{
  public function getZonas()
  {
    return Zona::select('id', 'name')->get();
  }
}
