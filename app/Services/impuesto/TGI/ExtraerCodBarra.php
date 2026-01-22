<?php

namespace App\Services\impuesto\TGI;


class ExtraerCodBarra
{

    public function __construct() {}



    //35 es la cantidad de caracteres del codigo de barras
    public function separarCodigoBarras($codigoBarras)
    {
        $folio = $this->separarFolio($codigoBarras);
        $partida = $this->separarPartida($codigoBarras);
        $importe = $this->separarImporte($codigoBarras);
        $fechaVencimiento = $this->separarFechaVencimiento($codigoBarras);
        $adm = $this->separarAdm($codigoBarras);
       
        // Retornar los valores separados en un array asociativo
        return [
            'codigo_barra' => $codigoBarras,
            'folio' => $folio,
            'partida' => $partida,
            'importe' => $importe,
            'fecha_vencimiento' => $fechaVencimiento,
            'adm' => $adm
        ];
    }


    private function separarFolio($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $folio = substr($codigoBarras, -35, 5);
        $folio_limpio = ltrim($folio, "0"); // Esto elimina todos los ceros del inicio del string.
        return $folio_limpio;
    }

    private function separarPartida($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $partida = substr($codigoBarras, -30, 8);
        return $partida;
    }

    private function separarImporte($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $importe = substr($codigoBarras, -22, 13);
        $importe_limpio = ltrim($importe, "0") / 100; // Esto elimina todos los ceros del inicio del string.
        return $importe_limpio;
    }

    private function separarFechaVencimiento($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $fechaVencimiento = substr($codigoBarras, -9, 8);

        $añoVencimiento = substr($fechaVencimiento, 4, 4);
        $diaVencimiento = substr($fechaVencimiento, 0, 2);
        $mesVencimiento = substr($fechaVencimiento, 2, 2);
        $fechaVencimiento = $añoVencimiento . '-' . $mesVencimiento . '-' . $diaVencimiento;

        return $fechaVencimiento;
    }

    private function separarAdm($codigoBarras)
    {
        $adm = substr($codigoBarras, -1, 1);
        return $adm;
    }
}
