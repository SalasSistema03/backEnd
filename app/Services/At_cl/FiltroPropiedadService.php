<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Propiedad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio encargado de aplicar filtros y ordenamientos avanzados
 * sobre las propiedades disponibles en el sistema.
 *
 * Centraliza toda la lógica de búsqueda, ordenamiento dinámico,
 * validación de precios y combinación de criterios.
 */
class FiltroPropiedadService
{
    /**
     * Procesa y filtra propiedades según los valores recibidos.
     *
     * - Si no hay filtros → devuelve colección vacía.
     * - Si hay "orden" → deriva a los métodos de ordenamiento.
     * - Sino → aplica el filtro base (scope) y devuelve resultados por fecha desc.
     *
     * @param array $filtros    Filtros procesados desde el Request.
     * @return Collection
     */
    public function filtrarPropiedades(array $filtros): Collection
    {
        // Si no se recibió ningún filtro significativo, no tiene sentido consultar la BD
        if (empty(array_filter($filtros))) {
            return collect();
        }

        // Si el usuario solicitó un ordenamiento específico,
        // delegamos la responsabilidad al método especializado
        if (!empty($filtros['orden'])) {
            return $this->aplicarOrden($filtros);
        }

        // Orden por defecto: fecha de creación descendente
        return Propiedad::filtrar($filtros)
            ->orderBy('propiedades.created_at', 'desc')
            ->get();
    }

    /**
     * Determina qué tipo de orden aplicar según el parámetro recibido.
     *
     * @param array $filtros
     * @return Collection
     */
    private function aplicarOrden(array $filtros): Collection
    {
        return match ($filtros['orden']) {
            'calle' => $this->ordenarPorCalle($filtros),
            'zona' => $this->ordenarPorZona($filtros),
            'tipo' => $this->ordenarPorTipo($filtros),
            'habitaciones' => $this->ordenarPorHabitaciones($filtros),
            'cochera' => $this->ordenarPorCochera($filtros),
            'precio_asc' => $this->ordenarPorPrecio($filtros, 'asc'),
            'precio_desc' => $this->ordenarPorPrecio($filtros, 'desc'),
            'banio' => $this->ordenarPorBanio($filtros),
            default => Propiedad::filtrar($filtros)->orderBy('propiedades.created_at', 'desc')->get(),
        };
    }
    
    /**
     * Ordena las propiedades por cantidad de baños.
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorBanio(array $filtros): Collection
    {
        return Propiedad::filtrar($filtros)
            ->orderBy('banios', 'desc')
            ->get();
    }

    /**
     * Ordena las propiedades por nombre de calle.
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorCalle(array $filtros): Collection
    {
        return Propiedad::filtrar($filtros)
            ->join('calle', 'calle.id', '=', 'propiedades.id_calle')
            ->orderBy('calle.name')
            ->select('propiedades.*')
            ->with('calle')
            ->get();
    }

    /**
     * Ordena propiedades por nombre de zona.
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorZona(array $filtros): Collection
    {
        return Propiedad::filtrar($filtros)
            ->join('zona', 'zona.id', '=', 'propiedades.id_zona')
            ->orderBy('zona.name')
            ->select('propiedades.*')
            ->with('zona')
            ->get();
    }

    /**
     * Ordena propiedades por tipo de inmueble (casa, dpto, ph, etc).
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorTipo(array $filtros): Collection
    {
        // Extraer solo tipos validos
        $tipos = array_filter((array) ($filtros['tipo_inmueble'] ?? []));

        $query = Propiedad::filtrar($filtros)
            ->leftJoin('tipo_inmueble', 'tipo_inmueble.id', '=', 'propiedades.id_inmueble')
            ->select('propiedades.*')
            ->with('tipoInmueble');

        // Si se especificaron tipo, filtrar por ellos
        if (!empty($tipos)) {
            $query->whereIn('propiedades.id_inmueble', $tipos);
        }

        return $query->orderBy('tipo_inmueble.inmueble')->get();
    }

    /**
     * Ordena por cantidad de dormitorios de manera ascendente.
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorHabitaciones(array $filtros): Collection
    {
        return Propiedad::filtrar($filtros)
            ->orderBy('cantidad_dormitorios', 'asc')
            ->get();
    }

    /**
     * Ordena por disponibilidad de cochera (0 / 1).
     *
     * @param array $filtros
     * @return Collection
     */
    private function ordenarPorCochera(array $filtros): Collection
    {
        return Propiedad::filtrar($filtros)
            ->orderBy('cochera', 'asc')
            ->get();
    }

    /**
     * Ordena por precio de venta o alquiler según el tipo de oferta.
     *
     * - Obtiene precios desde la relación `ultimoPrecio`
     * - Filtra propiedades con valores válidos
     * - Ordena según asc / desc
     *
     * @param array $filtros
     * @param string $direccion   "asc" o "desc"
     * @return Collection
     */
    private function ordenarPorPrecio(array $filtros, string $direccion): Collection
    {
        /* Log::info($direccion); */
        // Primero traemos propiedades con filtro + relación de precio
        $propiedades = Propiedad::filtrar($filtros)
            ->with('ultimoPrecio')
            ->get()
            // Eliminamos las que no tienen precio válido
            ->filter(fn($propiedad) => $this->tienePrecioValido($propiedad, $filtros));

        // Si no se especificó tipo de oferta (1 venta / 2 alquiler)
        if (empty($filtros['busqueda'])) {
            return $propiedades;
        }

        // Determinar método dinámico de ordenamiento
        $sortMethod = $direccion === 'asc' ? 'sortBy' : 'sortByDesc';
/* Log::info($filtros['busqueda']); */
        // Oferta 1 = venta
        if ($filtros['busqueda'] == 1) {
            return $propiedades->$sortMethod(fn($p) => $this->obtenerPrecioVenta($p, $direccion))->values();
        }
        

        // Oferta 2 = alquiler
        if ($filtros['busqueda'] == 2) {
            return $propiedades->$sortMethod(fn($p) => $this->obtenerPrecioAlquiler($p, $direccion))->values();
        }

        return $propiedades;
    }

    /**
     * Verifica si la propiedad tiene un precio válido para el tipo de oferta seleccionado.
     *
     * @param mixed $propiedad
     * @param array $filtros
     * @return bool
     */
    private function tienePrecioValido($propiedad, array $filtros): bool
    {
        $precio = $propiedad->ultimoPrecio;

        if (!$precio) {
            return false;
        }

        // Validación para venta
        if (!empty($filtros['oferta']) && $filtros['oferta'] == 1) {
            return $precio->moneda_venta_dolar > 0 || $precio->moneda_venta_pesos > 0;
        }

        // Validación para alquiler
        if (!empty($filtros['oferta']) && $filtros['oferta'] == 2) {
            return $precio->moneda_alquiler_pesos > 0 || $precio->moneda_alquiler_dolar > 0;
        }

        return false;
    }

    /**
     * Obtiene el precio a usar para ordenar ventas.
     *
     * Reglas:
     * - Prioridad dólar → pesos
     * - Para orden ascendente, los valores en pesos se "inflan" para no mezclar escalas
     *
     * @param mixed   $propiedad
     * @param string  $direccion
     * @return float|int
     */
    private function obtenerPrecioVenta($propiedad, string $direccion): float|int
    {
        
        $precio = $propiedad->ultimoPrecio;

        // ASC y pesos → moverlos al final mediante un valor muy alto
        if ($direccion === 'asc') {
            return $precio->moneda_venta_dolar > 0
                ? $precio->moneda_venta_dolar
                : ($precio->moneda_venta_pesos > 0 ? $precio->moneda_venta_pesos + 1_000_000_000 : PHP_INT_MAX);
        }

        // DESC → menor castigo para pesos
        return $precio->moneda_venta_dolar > 0
            ? $precio->moneda_venta_dolar
            : ($precio->moneda_venta_pesos > 0 ? $precio->moneda_venta_pesos : 0);
    }

    /**
     * Obtiene el precio a usar para ordenar alquileres.
     *
     * Similar a obtenerPrecioVenta, pero sólo para precios de alquiler.
     *
     * @param mixed   $propiedad
     * @param string  $direccion
     * @return float|int
     */
    private function obtenerPrecioAlquiler($propiedad, string $direccion): float|int
    {
        $precio = $propiedad->ultimoPrecio;

        if ($direccion === 'asc') {
            return $precio->moneda_alquiler_pesos > 0
                ? $precio->moneda_alquiler_pesos
                : ($precio->moneda_alquiler_dolar > 0 ? $precio->moneda_alquiler_dolar + 1_000_000_000 : PHP_INT_MAX);
        }

        return $precio->moneda_alquiler_pesos > 0
            ? $precio->moneda_alquiler_pesos
            : ($precio->moneda_alquiler_dolar > 0 ? $precio->moneda_alquiler_dolar : 0);
    }
}
