<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Tasacion;
use App\Models\At_cl\Tasaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    public function crearDesdeRequest(Array $venta, int $propiedadId, Array $alquiler = []): ?Tasacion
    {
        if (!array_key_exists('tasacion_venta', $venta) && !array_key_exists('tasacion_alquiler', $alquiler)) {
            return null;
        }

        /* Datos base de la tasación */
        $data = [
            'fecha_tasacion' => $venta['fecha_tasacion_venta'] ?? null,
            'propiedad_id'   => $propiedadId,
        ];

        if (array_key_exists('tasacion_venta', $venta)) {
            /* Moneda: 1 = Pesos / otro valor = Dólar */
            if (($venta['moneda_venta'] ?? '1') == '1') {

                $data['tasacion_pesos_venta'] = $venta['tasacion_venta'];
                $data['tasacion_dolar_venta'] = null;
                $data['moneda'] = '0';
            } else {

                $data['tasacion_pesos_venta'] = null;
                $data['tasacion_dolar_venta'] = $venta['tasacion_venta'];
                $data['moneda'] = '1';
            }
        }
        if (!empty($alquiler['tasacion_alquiler'])) {
            $data['tasacion_pesos_alquiler'] = $alquiler['tasacion_alquiler'];
        }

        /* Crea y retorna el registro de tasación */
        return Tasacion::create($data);
    }

    public function precargaTasacion(Request $request): array
    {
         /* Log::info($request);
        dd('hola');  */
        DB::beginTransaction();

        try {
            $tasacion = Tasaciones::create([
                'tipo_inmueble' => $request->inmuebleId,
                'calle' => $request->id_calle,
                'numero_calle' => $request->numeroCalle,
                'propietario' => $request->propietarioId,
                'fecha' => $request->fecha,
                'tasacion_venta' => $request->tasacionVenta,
                'tipo_tasacion' => $request->tipoTasacion,
                'tasacion_alquiler' => $request->tasacionAlquiler,
                'origen' => $request->ingreso,
                'tasador' => $request->tasadorId,
                'costo_tasacion' => $request->costoTasacion,
                'observaciones' => $request->observaciones,
                'folio' => $request->folio,
                'tasacion_informada_prop' => $request->tasacionInformada,
                'piso' => $request->piso,
                'departamento' => $request->departamento,
                'id_localidad' => $request->localidadId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => $tasacion,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarTasacion(int $id, Request $request): array
    {
        DB::beginTransaction();

        try {
            $tasacion = Tasaciones::findOrFail($id);

            $tasacion->update([
                'tipo_inmueble' => $request->inmuebleId,
                'calle' => $request->id_calle,
                'numero_calle' => $request->numeroCalle,
                'propietario' => $request->propietarioId,
                'tasacion_venta' => $request->tasacionVenta,
                'tipo_tasacion' => $request->tipoTasacion,
                'tasacion_alquiler' => $request->tasacionAlquiler,
                'origen' => $request->ingreso,
                'tasador' => $request->tasadorId,
                'costo_tasacion' => $request->costoTasacion,
                'observaciones' => $request->observaciones,
                'folio' => $request->folio,
                'tasacion_informada_prop' => $request->tasacionInformada,
                'piso' => $request->piso,
                'departamento' => $request->departamento,
                'id_localidad' => $request->localidadId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => $tasacion,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminarTasacion(int $id): bool
    {
        $tasacion = Tasaciones::find($id);

        return $tasacion ? $tasacion->delete() : false;
    }
}
