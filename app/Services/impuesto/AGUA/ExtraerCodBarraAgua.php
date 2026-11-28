<?php

namespace App\Services\impuesto\AGUA;

use App\Models\impuesto\boton_actualizado;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class ExtraerCodBarraAgua
{
    public function separarCodigoBarras($codigoBarras)
    {
        $padron = $this->obtenerPadron($codigoBarras);

        if (!$padron) {
            $clave = ltrim(substr($codigoBarras, 0, 10), '0');
            throw new \Exception("No se encontró un padrón de agua para la clave {$clave} del código de barras.");
        }

        $folio = $padron->folio;
        $partida = $padron->partida;
        $adm = $padron->administra;

        $fechaVencimiento_1 = $this->separarFechaVencimiento($codigoBarras, 10);
        $importe_1 = $this->separarImporte($codigoBarras, 18);

        $fechatgi = boton_actualizado::query()->firstOrFail()->ultima_fecha_agua;
        //Log::info('fechaagua', [$fechatgi]);
        //Log::info('fecha_vencimiento_1', [$fechaVencimiento_1]);
        //dd('hola');

        //if (Carbon::parse($fechaVencimiento_1)->month > Carbon::parse($fechatgi)->month) {
          //  throw new \Exception('El Padron se encuentra desactualizado');
        //}
          if ($this->fechaVencimientoSuperaPeriodoAgua($fechaVencimiento_1, $fechatgi)) {
            throw new \Exception('El Padron se encuentra desactualizado');
        }



        $fechaVencimiento_2 = $this->separarFechaVencimiento($codigoBarras, 26);
        $importe_2 = $this->separarImporte($codigoBarras, 38);



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
        // Los primeros 10 dígitos identifican la clave; la fecha comienza en la posición 10.
        $clave = substr($codigoBarras, 0, 10);

        $clave = ltrim($clave, '0');

        $padronExistente = (new PadronAguaService())->obtenerPadronExistente();

        $registro = $padronExistente->first(function ($item) use ($clave) {
            return ltrim((string) $item->clave, '0') === $clave;
        });

        return $registro;
    }

    /*  public function separarImporte($codigoBarras, $inicio)
    {
        $importe = substr($codigoBarras, $inicio, 10);

        // Convertir a formato decimal (ej: 0002540556 → 25405.56)
        $importe = ltrim($importe, "0");
        $importe = str_pad($importe, 3, '0', STR_PAD_LEFT);

        return number_format(((int)$importe) / 100, 2, '.', '');
    }  */


    public function separarImporte($codigoBarras, $inicio)
    {
        // Extrae los 8 dígitos del importe (ej: "02045128")
        $subcadena = substr($codigoBarras, $inicio, 8);

        // Convierte directamente a float dividiendo entre 100
        $importeFloat = (float)$subcadena / 100;

        // Retorna el formato decimal con 2 decimales (ej: "30164.51")
        return number_format($importeFloat, 2, '.', '');
    }

    public function separarFechaVencimiento($codigoBarras, $inicio)
    {
        $fecha = substr($codigoBarras, $inicio, 6); // DDMMYY

        return Carbon::createFromFormat('dmy', $fecha)->format('Y-m-d');
    }

    private function fechaVencimientoSuperaPeriodoAgua($fechaVencimiento, $fechaTgi): bool
    {
        $vencimiento = Carbon::parse($fechaVencimiento);
        $periodoTgi = Carbon::parse($fechaTgi);
        $mesVencimiento = ($vencimiento->year * 12) + $vencimiento->month;
        $mesTgi = ($periodoTgi->year * 12) + $periodoTgi->month;

        return $mesVencimiento > $mesTgi;
    }
}
