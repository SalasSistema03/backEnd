<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Tasacion;
use Illuminate\Http\Request;

class TasacionService
{
    /**
     * Obtiene la última tasación asociada a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerUltimaTasacion($propiedadId)
    {
        return Tasacion::where('propiedad_id', $propiedadId)->latest('created_at')->first();
    }

    /**
     * Crea una nueva tasación asociada a una propiedad.
     *
     * @param array $tasacionData  Datos validados de la tasación.
     * @return \App\Models\At_cl\Tasacion
     */
    public function crearTasacion(array $tasacionData)
    {
        return Tasacion::create($tasacionData);
    }

    /**
     * Crea una tasación de venta a partir de los datos enviados en el request.
     *
     * Este método valida la existencia del valor de tasación, determina la moneda
     * seleccionada (pesos o dólares) y persiste el registro asociado a la propiedad.
     * Si no se envía información de tasación, no se genera ningún registro.
     *
     * @param  \Illuminate\Http\Request $request      request con los datos de la tasación
     * @param  int                      $propiedadId  ID de la propiedad asociada
     *
     * @return \App\Models\Tasacion|null instancia de la tasación creada o null si no aplica
     * @access public
     */
    public function crearDesdeRequest(Request $request, int $propiedadId): ?Tasacion
    {
        /* Verifica que el request contenga el valor de tasación de venta */
        if (!$request->filled('tasacion_venta')) {
            return null;
        }

        /* Datos base de la tasación */
        $data = [
            'fecha_tasacion' => $request->fecha_tasacion_venta,
            'propiedad_id'   => $propiedadId,
        ];

        /* Determina la moneda de la tasación
       Moneda: 1 = Pesos / otro valor = Dólar */
        if ($request->moneda_venta == '1') {

            /* Tasación expresada en pesos */
            $data['tasacion_pesos_venta'] = $request->tasacion_venta;
            $data['tasacion_dolar_venta'] = null;
            $data['moneda']               = '0';
        } else {

            /* Tasación expresada en dólares */
            $data['tasacion_pesos_venta'] = null;
            $data['tasacion_dolar_venta'] = $request->tasacion_venta;
            $data['moneda']               = '1';
        }

        /* Crea y retorna el registro de tasación */
        return Tasacion::create($data);
    }
}
