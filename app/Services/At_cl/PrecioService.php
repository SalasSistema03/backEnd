<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Precio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrecioService
{
    /**
     * Obtiene el último precio registrado para una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerUltimoPrecio($propiedadId)
    {
        return Precio::where('propiedad_id', $propiedadId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Crea un nuevo precio para una propiedad.
     *
     * @param array $precioData  Datos validados del precio.
     * @return \App\Models\At_cl\Precio
     */
    public function crearPrecio(array $precioData)
    {
        return Precio::create($precioData);
    }



    /**
     * Crear un registro de precio desde los datos del request
     *
     * @param Array $venta
     * @param Array $alquiler
     * @param int $propiedadId
     * @return Precio|null
     */
    public function crearDesdeRequest(array $venta, array $alquiler, int $propiedadId): ?Precio
    {
        //Log::info($venta, $alquiler, $propiedadId);
        $precioData = $this->prepararDatosDesdeRequest($venta, $alquiler, $propiedadId);

        if (empty($precioData)) {
            return null;
        }

        return Precio::create($precioData);
    }

    /**
     * Preparar array de datos para crear/actualizar precio
     *
     * @param Request $request
     * @param int $propiedadId
     * @return array
     */
    private function prepararDatosDesdeRequest(array $venta, array $alquiler, int $propiedadId): array
    {
         
        $precioData = ['propiedad_id' => $propiedadId];
        if ($venta !== null && !empty($venta)) {
            // Procesar datos de venta
            if (isset($venta['moneda_venta']) && isset($venta['monto_venta']) && 
                $venta['moneda_venta'] !== null && $venta['monto_venta'] !== null) {
                $precioData = array_merge($precioData, $this->procesarDatosVenta($venta));
            }
            // Agregar fechas de alta si corresponde
            if (isset($venta['cod_venta'])) {
                $precioData['venta_fecha_alta'] = now();
            }
        }

        log::info('estos son los datos de venta', $precioData);
        if ($alquiler !== null && !empty($alquiler)) {
            // Procesar datos de alquiler
            if (isset($alquiler['moneda_alquiler']) && isset($alquiler['monto_alquiler']) && 
                $alquiler['moneda_alquiler'] !== null && $alquiler['monto_alquiler'] !== null) {
                $precioData = array_merge($precioData, $this->procesarDatosAlquiler($alquiler));
            }

            if (isset($alquiler['cod_alquiler'])) {
                $precioData['alquiler_fecha_alta'] = now();
            }
        }
        log::info('estos son los datos de alquiler', $precioData);



        return $precioData;
    }

    /**
     * Procesar datos de venta según la moneda seleccionada
     *
     * @param Request $request
     * @return array
     */
    private function procesarDatosVenta(array $venta): array
    {
        // Moneda 1 = Pesos ($), otra = Dólares
        $esPesos = $venta['moneda_venta'] == '1';

        return [
            'moneda_venta_pesos' => $esPesos ? $venta['monto_venta'] : null,
            'moneda_venta_dolar' => !$esPesos ? $venta['monto_venta'] : null,
            'moneda' => $esPesos ? '0' : '1'
        ];
    }

    /**
     * Procesar datos de alquiler según la moneda seleccionada
     *
     * @param Request $request
     * @return array
     */
    private function procesarDatosAlquiler(array $alquiler): array
    {
        // Moneda 1 = Pesos ($), otra = Dólares
        $esPesos = $alquiler['moneda_alquiler'] == '1';

        return [
            'moneda_alquiler_pesos' => $esPesos ? $alquiler['monto_alquiler'] : null,
            'moneda_alquiler_dolar' => !$esPesos ? $alquiler['monto_alquiler'] : null,
            'moneda' => $esPesos ? '0' : '1'
        ];
    }



    /**
     * Obtener precio de una propiedad
     *
     * @param int $propiedadId
     * @return Precio|null
     */
    public function obtenerPorPropiedad(int $propiedadId): ?Precio
    {
        return Precio::where('propiedad_id', $propiedadId)->first();
    }

    /**
     * Eliminar precio de una propiedad
     *
     * @param int $propiedadId
     * @return bool
     */
    public function eliminarPorPropiedad(int $propiedadId): bool
    {
        return Precio::where('propiedad_id', $propiedadId)->delete();
    }

    /**
     * Verificar si una propiedad tiene precio registrado
     *
     * @param int $propiedadId
     * @return bool
     */
    public function tienePrecio(int $propiedadId): bool
    {
        return Precio::where('propiedad_id', $propiedadId)->exists();
    }
}
