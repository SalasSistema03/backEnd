<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Observaciones_propiedades;

class ObservacionesPropiedadesService
{
      /**
     * Obtiene las observaciones de tipo *VENTA* para una propiedad,
     * ordenadas de más recientes a más antiguas.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerObservacionesVenta($propiedadId)
    {
        return Observaciones_propiedades::where('propiedad_id', $propiedadId)
            ->where('tipo_ofera', 'V')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Obtiene las observaciones de tipo *ALQUILER* para una propiedad.
     *
     * Nota: A diferencia de las de venta, aquí no se aplica orden explícito.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerObservacionesAlquiler($propiedadId)
    {
        return Observaciones_propiedades::where('propiedad_id', $propiedadId)
            ->where('tipo_ofera', 'A')
            ->get();
    }

    public function guardarObservaciones($novedades, $propiedadId, $userId){
        foreach($novedades as $novedad){
            Observaciones_propiedades::create([
                'propiedad_id' => $propiedadId,
                'tipo_ofera' => $novedad['tipo_ofera'],
                'notes' => $novedad['notes'],
                'last_modified_by' => $userId,
                'created_at' => now(),
            ]);
        }
    }
}
