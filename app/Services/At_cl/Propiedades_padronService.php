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

    public function vincularActualizacion($propiedad_id, $propietarios_nuevos)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            $vinculados = 0;

            foreach ($propietarios_nuevos as $propietario) {
                // Crear la relación en la tabla intermedia
                Propiedades_padron::create([
                    'propiedad_id' => $propiedad_id,
                    'padron_id' => $propietario['id'],
                    'observaciones' => $propietario['pivot']['observaciones'] ?? '',
                    'baja' => $propietario['pivot']['baja'] ?? 'no',
                ]);
                $vinculados++;
            }

            DB::commit(); // Confirmar la operación

            $message = $vinculados > 1
                ? "Propietarios vinculados correctamente a la propiedad."
                : "Propietario vinculado correctamente a la propiedad.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'vinculados' => $vinculados
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios si algo falla
            Log::error("Error al vincular propietarios: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al vincular los propietarios.'
            ], 500);
        }
    }

    public function eliminarPropietario($propiedad_id, $padron_ids)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Convertir a array si es un solo valor
            if (!is_array($padron_ids)) {
                $padron_ids = [$padron_ids];
            }

            // Eliminar las relaciones en la tabla intermedia
            $eliminados = Propiedades_padron::where('propiedad_id', $propiedad_id)
                ->whereIn('padron_id', $padron_ids)
                ->delete();

            DB::commit(); // Confirmar la operación


            return response()->json([
                'success' => true,
                'message' => 'Persona Eliminado correctamente',
                'eliminados' => $eliminados
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios si algo falla
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al desvincular.'
            ], 500);
        }
    }


    public function modificarPropietario($propiedad_id, $propietarios_modificados){
        foreach($propietarios_modificados as $propietario){
            Propiedades_padron::where('propiedad_id', $propiedad_id)
                ->where('padron_id', $propietario['id'])
                ->update([
                    'observaciones_baja' => $propietario['observaciones'] ?? '',
                    'baja' => $propietario['baja'] ?? 'no',
                    'fecha_baja' => now(),
                ]);
        }
    }
}
