<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Padron;

class PadronService
{
    /**
     * Buscar personas en el padrón según apellido o DNI.
     *
     * @param string|null $apellido  Apellido a buscar.
     * @param string|null $dni       DNI a buscar.
     * @return \Illuminate\Support\Collection
     */
    public function BuscarPadron($apellido, $dni)
    {
        // Si no se proporciona ni apellido ni DNI, devolver una colección vacía
        if (!$apellido && !$dni) {
            $personas = collect(); // Devuelve una colección vacía
        } else {
            // Crear consulta sobre el modelo Padron, cargando la relación 'telefonos'
            $personas = Padron::with('telefonos') // Cargar la relación del teléfono
                ->where(function ($query) use ($apellido, $dni) {
                    // Si se proporcionó un apellido, agregar condición LIKE
                    if ($apellido) {
                        $query->where('apellido', 'like', '%' . $apellido . '%');
                    }
                    // Si se proporcionó un DNI, agregar condición OR LIKE
                    if ($dni) {
                        $query->orWhere('documento', 'like', '%' . $dni . '%');
                    }
                })->get();
        }
        // Retornar la colección de personas encontradas (o vacía si no había filtros)
        return $personas;
    }
}
