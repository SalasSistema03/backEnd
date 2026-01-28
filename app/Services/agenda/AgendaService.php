<?php

namespace App\Services\agenda;

use App\Models\agenda\Agenda;
use App\Models\cliente\Usuario_sector;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\agenda\Notas;
use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf;

class AgendaService
{
    public function sincronizarSectores(array $sectores, int $usuario_id): void
    {
        $sectoresActuales = $this->obtenerSectoresActuales($usuario_id);

        $this->eliminarSectoresObsoletos($sectoresActuales, $sectores, $usuario_id);
        $this->agregarSectoresNuevos($sectoresActuales, $sectores, $usuario_id);
        $this->actualizarTimestampsSectores($sectores, $usuario_id);
    }

    private function obtenerSectoresActuales(int $usuario_id): array
    {
        return Agenda::where('usuario_id', $usuario_id)
            ->pluck('sector_id')
            ->toArray();
    }

    private function eliminarSectoresObsoletos(
        array $sectoresActuales, 
        array $sectores, 
        int $usuario_id
    ): void {
        $sectoresAEliminar = array_diff($sectoresActuales, $sectores);
        
        if (!empty($sectoresAEliminar)) {
            Agenda::where('usuario_id', $usuario_id)
                ->whereIn('sector_id', $sectoresAEliminar)
                ->delete();
        }
    }

    private function agregarSectoresNuevos(
        array $sectoresActuales, 
        array $sectores, 
        int $usuario_id
    ): void {
        $sectoresAAgregar = array_diff($sectores, $sectoresActuales);
        
        if (empty($sectoresAAgregar)) {
            return;
        }

        $datosInsertar = [];
        foreach ($sectoresAAgregar as $sector) {
            $datosInsertar[] = [
                'usuario_id' => $usuario_id,
                'sector_id' => $sector,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Agenda::insert($datosInsertar);
    }

    private function actualizarTimestampsSectores(array $sectores, int $usuario_id): void
    {
        if (!empty($sectores)) {
            Agenda::where('usuario_id', $usuario_id)
                ->whereIn('sector_id', $sectores)
                ->update(['updated_at' => now()]);
        }
    }
}