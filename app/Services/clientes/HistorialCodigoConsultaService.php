<?php

namespace App\Services\clientes;

use App\Models\At_cl\Propiedad;
use App\Models\cliente\HistorialCodigoConsulta;

use function Illuminate\Log\log;

class HistorialCodigoConsultaService
{
    public function guardarHistorialCodigoConsulta($id_propiedad, $id_criterio_venta)
    {
        // 1. Obtener el ID del usuario autenticado vía JWT/Token
        $id_usuario = auth('api')->id();
        $propiedadPorId = Propiedad::with('calle')->find($id_propiedad);
        if (!$propiedadPorId) {
            return null;
        }
        //log('Historial Codigo Consulta  venta: ' . $propiedadPorId);

        try {
            $dataToSave = [
                'codigo_consulta' => $propiedadPorId->cod_venta,
                'id_criterio_venta' => $id_criterio_venta,
                'mensaje' => 'Cliente consulto por codigo ' . $propiedadPorId->cod_venta . ' Direccion ' . $propiedadPorId->calle->name . '  ' . $propiedadPorId->numero_calle,
                'direccion' => $propiedadPorId->calle->name . '  ' . $propiedadPorId->numero_calle,
                'fecha_hora' => now(),
                'last_modified_by' => $id_usuario,
                'devolucion' => null,
                'fecha_devolucion' => null,
            ];

            // Muestra el array y detiene la ejecución
            // El código de abajo no se ejecutará
            return HistorialCodigoConsulta::create($dataToSave);

        } catch (\Exception $e) {
            log()->error($e);
            return null;
        }
    }
}
