<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Precio;
use Illuminate\Http\Request;

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
     * @param Request $request
     * @param int $propiedadId
     * @return Precio|null
     */
    public function crearDesdeRequest(Request $request, int $propiedadId): ?Precio
    {
        $precioData = $this->prepararDatosDesdeRequest($request, $propiedadId);

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
    private function prepararDatosDesdeRequest(Request $request, int $propiedadId): array
    {
        $precioData = ['propiedad_id' => $propiedadId];

        // Procesar datos de venta
        if ($request->filled('moneda_venta') && $request->filled('monto_venta')) {
            $precioData = array_merge($precioData, $this->procesarDatosVenta($request));
        }

        // Procesar datos de alquiler
        if ($request->filled('moneda_alquiler') && $request->filled('monto_alquiler')) {
            $precioData = array_merge($precioData, $this->procesarDatosAlquiler($request));
        }

        // Agregar fechas de alta si corresponde
        if ($request->filled('cod_venta')) {
            $precioData['venta_fecha_alta'] = now();
        }

        if ($request->filled('cod_alquiler')) {
            $precioData['alquiler_fecha_alta'] = now();
        }

        return $precioData;
    }

    /**
     * Procesar datos de venta según la moneda seleccionada
     *
     * @param Request $request
     * @return array
     */
    private function procesarDatosVenta(Request $request): array
    {
        // Moneda 1 = Pesos ($), otra = Dólares
        $esPesos = $request->moneda_venta == '1';

        return [
            'moneda_venta_pesos' => $esPesos ? $request->monto_venta : null,
            'moneda_venta_dolar' => !$esPesos ? $request->monto_venta : null,
            'moneda' => $esPesos ? '0' : '1'
        ];
    }

    /**
     * Procesar datos de alquiler según la moneda seleccionada
     *
     * @param Request $request
     * @return array
     */
    private function procesarDatosAlquiler(Request $request): array
    {
        // Moneda 1 = Pesos ($), otra = Dólares
        $esPesos = $request->moneda_alquiler == '1';

        return [
            'moneda_alquiler_pesos' => $esPesos ? $request->monto_alquiler : null,
            'moneda_alquiler_dolar' => !$esPesos ? $request->monto_alquiler : null,
            'moneda' => $esPesos ? '0' : '1'
        ];
    }

    /**
     * Actualizar precio existente
     *
     * @param int $propiedadId
     * @param Request $request
     * @return bool
     */
    public function actualizarPorPropiedad(int $propiedadId, Request $request): bool
    {
        $precio = Precio::where('propiedad_id', $propiedadId)->first();

        if (!$precio) {
            return false;
        }

        $precioData = $this->prepararDatosDesdeRequest($request, $propiedadId);
        
        // Remover propiedad_id ya que no debe actualizarse
        unset($precioData['propiedad_id']);

        return $precio->update($precioData);
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
