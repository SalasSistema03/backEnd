<?php

namespace App\Services\impuesto\API;
use Illuminate\Support\Facades\Log;


class ExtraerCodBarra
{

    public function __construct() {}



    //35 es la cantidad de caracteres del codigo de barras
    public function separarCodigoBarras($codigoBarras)
    {
        $partida = $this->separarPartida($codigoBarras);
        $fechaVencimiento = $this->separarFechaVencimiento($codigoBarras);
        
        $importe = $this->separarImporte($codigoBarras);
    
       
        // Retornar los valores separados en un array asociativo
        return [
            'codigo_barra' => $codigoBarras,
            'partida' => $partida,
            'importe' => $importe,
            'fecha_vencimiento' => $fechaVencimiento,
        ];
    }


   

    private function separarPartida($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $partida = substr($codigoBarras, 8, 17);
        return $partida;
    }

    private function separarImporte($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código
        $importe = substr($codigoBarras, 39, 10);
        
        $importe_limpio = ltrim($importe, "0") / 100; // Esto elimina todos los ceros del inicio del string.
        return $importe_limpio;
    }

    private function separarFechaVencimiento($codigoBarras)
    {
        // Ejemplo: extraer los últimos 10 dígitos antes de la letra final
        // Ajustá esto según cómo esté estructurado tu código .34
        $fechaVencimiento = substr($codigoBarras, 33, 6);

        $añoVencimiento = substr($fechaVencimiento, 0, 2);
        $diaVencimiento = substr($fechaVencimiento, 4, 2);
        $mesVencimiento = substr($fechaVencimiento, 2, 2);
        $fechaVencimiento = $añoVencimiento . '-' . $mesVencimiento . '-' . $diaVencimiento;
        //Log::info($fechaVencimiento);
        
        return $fechaVencimiento;
    }
}
