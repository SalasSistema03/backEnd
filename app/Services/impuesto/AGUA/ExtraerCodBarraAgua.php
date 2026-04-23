<?php

namespace App\Services\impuesto\AGUA;

use Carbon\Carbon;

class ExtraerCodBarraAgua
{
    public function separarCodigoBarras($codigoBarras)
    {
        $padron = $this->obtenerPadron($codigoBarras);

        $folio = $padron->folio;
        $partida = $padron->partida;
        $adm = $padron->administra;

        // Extracción correcta según posiciones fijas
        $fechaVencimiento_1 = $this->separarFechaVencimiento($codigoBarras, 8);
        $importe_1 = $this->separarImporte($codigoBarras, 14);

        $fechaVencimiento_2 = $this->separarFechaVencimiento($codigoBarras, 24);
        $importe_2 = $this->separarImporte($codigoBarras, 30);



        // Retornar los valores separados en un array asociativo
        return [
            'codigo_barra' => $codigoBarras,
            'folio' => $folio,
            'partida' => $partida,
            'adm' => $adm,
            'importe_1' => $importe_1,
            'fecha_vencimiento_1' => $fechaVencimiento_1,
            'importe_2' => $importe_2,
            'fecha_vencimiento_2' => $fechaVencimiento_2
        ];
    }

    public function obtenerPadron($codigoBarras)
    {
        //Obtenemos los primeros 8 digitos
        $clave = substr($codigoBarras, 0, 8);

        //Limpiamos la clave
        $clave = ltrim($clave, "0");

        //Obtenemos el padron existente
        $padronExistente = (new PadronAguaService())->obtenerPadronExistente();

        $registro = $padronExistente->first(function ($item) use ($clave) {
            return $item->clave == $clave;
        });
        return $registro;
    }

    public function separarImporte($codigoBarras, $inicio)
    {
        $importe = substr($codigoBarras, $inicio, 10);

        // Convertir a formato decimal (ej: 0002540556 → 25405.56)
        $importe = ltrim($importe, "0");
        $importe = str_pad($importe, 3, '0', STR_PAD_LEFT);

        return number_format(((int)$importe) / 100, 2, '.', '');
    }

    public function separarFechaVencimiento($codigoBarras, $inicio)
    {
        $fecha = substr($codigoBarras, $inicio, 6); // DDMMYY

        return Carbon::createFromFormat('dmy', $fecha)->format('Y-m-d');
    }
}
