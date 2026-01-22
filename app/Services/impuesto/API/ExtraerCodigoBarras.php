<?php

namespace App\Services\impuesto\API;


class ExtraerCodigoBarras
{

    public function __construct() {}



    //35 es la cantidad de caracteres del codigo de barras
    public function separarCodigoBarras($codigoBarras)
    {
       
        $partida = $this->separarPartida($codigoBarras);
        $importe = $this->separarImporte($codigoBarras);
        $fechaVencimiento = $this->separarFechaVencimiento($codigoBarras);
        

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
        // Toma los últimos 11 dígitos del código de barras
        $importe = substr($codigoBarras, -11,10);
        
        // Elimina ceros a la izquierda y divide por 100 para obtener el valor real
        $importe_limpio = ltrim($importe, "0") / 100;

        return $importe_limpio;
    }

    private function separarFechaVencimiento($codigoBarras)
    {
        // Tomamos los 6 dígitos que están justo antes del importe
        $fechaVencimiento = substr($codigoBarras, -17, 6); // posición relativa al final

        // Separar los componentes
        $anio = '20' . substr($fechaVencimiento, 0, 2); // primeros 2 -> año
        $mes  = substr($fechaVencimiento, 2, 2);        // siguientes 2 -> mes
        $dia  = substr($fechaVencimiento, 4, 2);        // últimos 2 -> día

        // Formato YYYY-MM-DD
        return "$anio-$mes-$dia";
    }

   
}
