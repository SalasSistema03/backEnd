<?php

namespace App\Services\impuesto\GAS;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExtraerCodBarraGas
{
    public function separarCodigoBarras($codigoBarras)
    {
        $padron = $this->obtenerPadron($codigoBarras);
        $fechaVencimiento =  $this->separarFechaVencimiento($codigoBarras, 27);
        $importe = $this->separarImporte($codigoBarras, 17);
        $inicioLiq = $this->separarFechaVencimiento($codigoBarras, 35);
        $finLiq  = $this->separarFechaVencimiento($codigoBarras, 43);

        // Retornar los valores separados en un array asociativo
        return [
            'codigo_barra' => $codigoBarras,
            'importe' => $importe,
            'fecha_vencimiento' => $fechaVencimiento,
            'inicio_liquidacion' => $inicioLiq,
            'fin_liquidacion' => $finLiq,
            'partida' => $padron ? $padron->partida : null,
        ];
    }

     public function separarImporte($codigoBarras, $inicio)
    {
        $importe = substr($codigoBarras, $inicio, 10);

        // Convertir a formato decimal (ej: 0000079670 → 796.70)
        $importe = ltrim($importe, "0");
        $importe = str_pad($importe, 3, '0', STR_PAD_LEFT);

        return number_format(((int)$importe) / 100, 2, '.', '');
    }

    public function separarFechaVencimiento($codigoBarras, $inicio)
    {
        $fecha = substr($codigoBarras, $inicio, 8); // DDMMYYYY

        return Carbon::createFromFormat('dmY', $fecha)->format('Y-m-d');
    }

    public function obtenerPadron($codigoBarras)
    {
        //Obtenemos los primeros 6 digitos
        $clave = substr($codigoBarras, 6, 11);

        //Limpiamos la clave
        $clave = ltrim($clave, "0");


        //Obtenemos el padron existente
        $padronExistente = (new PadronGasService())->obtenerPadronExistente();

        $registro = $padronExistente->first(function ($item) use ($clave) {
            return $item->partida == $clave;
        });

        return $registro;
    }


}
