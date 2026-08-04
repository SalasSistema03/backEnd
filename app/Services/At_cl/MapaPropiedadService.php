<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Propiedad;
use Illuminate\Support\Collection;

class MapaPropiedadService
{
    /**
     * Obtiene las propiedades que tienen coordenadas, aplicando los filtros del usuario.
     */
    public function obtenerPropiedadesParaMapa(array $filtros): Collection
    {
        // 1. Iniciamos la consulta asegurando que solo trae propiedades geolocalizadas.
        $query = Propiedad::whereNotNull('latitud')
                          ->whereNotNull('longitud');

        // 2. Reutilizamos tu scope existente para aplicar todos los filtros dinámicos[cite: 1].
        // Esto cubre: inmuebles, cochera, habitaciones, mascotas, zonas y precios[cite: 1].
        $query->filtrar($filtros);

        // 3. Cargamos las relaciones necesarias para el Popup del mapa (Eager Loading)[cite: 1].
        // Usamos 'calle', 'precioActual' y 'tipoInmueble' que ya están definidas en tu modelo[cite: 1].
        $query->with([
            'calle:id,name', 
            'precioActual', 
            'tipoInmueble:id,inmueble' // Asumiendo que tu tabla tipo_inmueble tiene un campo 'name' o similar
        ]);

        // 4. Seleccionamos SOLO los campos necesarios de la tabla 'propiedades' para no saturar la red[cite: 1].
        // Incluimos las llaves foráneas 'id_calle' e 'id_inmueble' para que las relaciones funcionen[cite: 1].
        return $query->get([
            'id',
            'id_calle',
            'numero_calle',
            'id_inmueble',
            'cantidad_dormitorios',
            'cochera',
            'latitud',
            'longitud',
            'cod_venta',
            'cod_alquiler'
        ]);
    }
}