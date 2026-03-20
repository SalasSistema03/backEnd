<?php

namespace App\Services\clientes;

use App\Models\cliente\CriterioBusquedaVenta;
use Illuminate\Support\Facades\Log;

class CriterioBusquedaVentaService
{
    public function guardarcriterioBusquedaVenta(array $data)
    {
        try {
            return CriterioBusquedaVenta::create($data); // Guarda los datos en la base de datos
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e);
            return null;
        }
    }

    public static function getCriteriosExistentesPorIDCliente($idCliente){
        return CriterioBusquedaVenta::where('id_cliente', $idCliente)
            ->where('estado_criterio_venta', 'Activo')
            ->get();
    }

    /**
     * Sincroniza criterios de venta de un cliente
     * Elimina los criterios que no están en la lista nueva y agrega los nuevos
     */
    public function sincronizarCriteriosVenta($idCliente, $criteriosNuevos)
    {
        // Obtener criterios existentes del cliente
        $criteriosExistentes = self::getCriteriosExistentesPorIDCliente($idCliente);
        $idsExistentes = $criteriosExistentes->pluck('id_criterio_venta')->toArray();

        Log::info('Criterios existentes', ['ids' => $idsExistentes]);
        Log::info('Criterios nuevos recibidos', ['criterios' => $criteriosNuevos]);

        // Separar criterios nuevos (sin id) y existentes (con id)
        $criteriosAAgregar = [];
        $idsRecibidos = [];

        foreach ($criteriosNuevos as $criterio) {
            if (isset($criterio['id_criterio_venta'])) {
                $idsRecibidos[] = $criterio['id_criterio_venta'];
            } else {
                // Es un criterio nuevo, prepararlo para agregar
                $criterio['id_cliente'] = $idCliente;
                $criteriosAAgregar[] = $criterio;
            }
        }

        // Eliminar criterios que ya no están en la lista nueva
        $idsAEliminar = array_diff($idsExistentes, $idsRecibidos);
        if (!empty($idsAEliminar)) {
            Log::info('Eliminando criterios', ['ids' => $idsAEliminar]);
            CriterioBusquedaVenta::whereIn('id_criterio_venta', $idsAEliminar)
                ->update(['estado_criterio_venta' => 'Eliminado']);
        }

        // Agregar nuevos criterios
        $criteriosCreados = [];
        foreach ($criteriosAAgregar as $criterio) {
            $nuevoCriterio = $this->guardarcriterioBusquedaVenta($criterio);
            if ($nuevoCriterio) {
                $criteriosCreados[] = $nuevoCriterio;
            }
        }

        Log::info('Criterios agregados', ['cantidad' => count($criteriosCreados)]);

        return [
            'eliminados' => count($idsAEliminar),
            'agregados' => count($criteriosCreados),
            'criterios_creados' => $criteriosCreados
        ];
    }
}
