<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Calle;


class CalleService
{
  public function getCalles()
  {
    return Calle::select('id', 'name')->get();
  }
}
