<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Propiedades_padron;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Propiedades_padronService
{
    /**
     * Obtiene todos los propietarios asociados a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerPropietarios($propiedadId)
    {
        return DB::table('propiedades_padron')
            ->join('padron', 'propiedades_padron.padron_id', '=', 'padron.id')
            ->where('propiedades_padron.propiedad_id', $propiedadId)
            ->select(
                'padron.nombre',
                'padron.apellido',
                'propiedades_padron.baja',
                'propiedades_padron.fecha_baja',
                'propiedades_padron.observaciones as notes',
                'propiedades_padron.padron_id',
                'propiedades_padron.observaciones_baja'
            )
            ->get();
    }


    public function vincular($propiedad_id, $padron_id)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Crear la relación en la tabla intermedia
            Propiedades_padron::create([
                'propiedad_id' => $propiedad_id,
                'padron_id' => $padron_id,
                // Aquí podrías agregar last_modified_by si tenés autenticación
            ]);

            DB::commit(); // Confirmar la operación
            return response()->json([
                'success' => true,
                'message' => 'Persona vinculada correctamente a la propiedad.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios si algo falla
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al vincular.'
            ], 500);
        }
    }
}
