<?php

namespace App\Services\proceso;

use App\Models\At_cl\Propiedad;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Proceso_propiedad;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\cliente\Usuario_sector;
use App\Notifications\RecordatorioNotificacion;

class ProcesoService
{
    /**
     * Subir una nueva reserva
     */
    public function subirReserva(array $data, $usuarioId)
    {
        //Log::info('Datos recibidos: ' . json_encode($data));
        //dd($data);
        try {
            $comprobantePath = null;

            // Manejar comprobante si existe
            if (isset($data['comprobante'])) {
                $comprobantePath = $this->guardarComprobante(
                    $data['comprobante'],
                    $data['fechaReserva'] ?? null,
                    $data['idPropiedad'] ?? '0'
                );
            }

            // Crear historial inicial
            $historial = Historial_estado_reserva::create([
                'id_estado' => 1,
                'observaciones' => 'Reserva creada',
                'quien_cargo' => $data['asesor'] ?? null,
                'fecha_carga' => now(),
            ]);

            // Crear proceso de propiedad
            $proceso = Proceso_propiedad::create([
                'asesor' => $data['asesor'] ?? null,
                'fecha_reserva' => $data['fechaReserva'] ?? null,
                'fecha_fin_reserva' => $data['fechaFinReserva'] ?? null,
                'id_cliente' => $data['idCliente'] ?? null,
                'reservante' => $data['nombreReservante'] ?? null,
                'id_propiedad' => $data['idPropiedad'] ?? null,
                'tipo_reserva' => $data['tipo'] ?? null,
                'moneda' => $data['moneda'] ?? null,
                'monto_reserva' => $data['montoReserva'] ?? null,
                'monto_aceptado' => 0,
                'documentacion' => $comprobantePath ?? $data['documentacion'] ?? null,
                'id_historial_estado_reserva' => $historial->id,
                'quien_cargo' => $usuarioId,
                'precio_alquiler' => $data['precioFolio'] ?? null,
                'meses_contrato' => $data['mesesContrato'] ?? null,
            ]);

            // Actualizar historial con el ID del proceso
            $historial->update(['id_proceso_propiedad' => $proceso->id]);

            // Actualizar estado de la propiedad
            $estadoInicial = $this->actualizarEstadoPropiedad($data['idPropiedad'] ?? null, $usuarioId);

            // Guardar estado inicial en el proceso
            $proceso->update(['estado_alquiler_inicial' => $estadoInicial]);

            return [
                'proceso' => $proceso,
                'comprobantePath' => $comprobantePath
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Guardar comprobante en carpeta compartida
     */
    private function guardarComprobante($file, $fechaReserva, $idPropiedad)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['pdf', 'jpg', 'jpeg'])) {
            throw new \InvalidArgumentException('Formato de comprobante no válido.');
        }

        $year = date('Y', strtotime($fechaReserva ?? now()));
        $sharedFolder = '\\\\10.10.10.152\\Compartida\\RESERVAS\\' . $year;

        if (!File::exists($sharedFolder)) {
            File::makeDirectory($sharedFolder, 0777, true);
        }

        $fileName = 'reserva_' . ($idPropiedad ?? '0') . '_' . time() . '.' . $extension;
        $destinationPath = $sharedFolder . '\\' . $fileName;

        if (!copy($file->getPathname(), $destinationPath)) {
            throw new \RuntimeException('No se pudo copiar el comprobante a la carpeta compartida.');
        }

        @unlink($file->getPathname());

        return $destinationPath;
    }

    /**
     * Actualizar estado de la propiedad
     */
    private function actualizarEstadoPropiedad($idPropiedad, $usuarioId)
    {
        $propiedad = Propiedad::find($idPropiedad);
        $estadoInicial = $propiedad ? $propiedad->id_estado_alquiler : null;

        if ($propiedad) {
            $propiedad->update([
                'id_estado_alquiler' => 5,
                'updated_at' => now(),
                'last_modified_by' => $usuarioId,
            ]);
        }

        return $estadoInicial;
    }

    /**
     * Obtener reservas según permisos
     */
    public function obtenerReservas($usuarioId, $estado = null, $mes = null)
    {
        $accessService = new PermitirAccesoSelladoService($usuarioId);
        $tieneAccesoGlobal = $accessService->tieneAcceso('listarAsesoresReserva');

        // Obtener IDs de historiales activos
        $idsHistorialesActivos = Proceso_propiedad::whereNotNull('id_historial_estado_reserva')
            ->pluck('id_historial_estado_reserva');

        // Construir query base
        $query = Historial_estado_reserva::with([
            'estado',
            'proceso_propiedad.propiedad.folios',
            'proceso_propiedad.propiedad.calle',
            'proceso_propiedad.cliente',
            'proceso_propiedad.asesorUsuario',
            'proceso_propiedad.propiedad.precioActual'
        ])->whereIn('id', $idsHistorialesActivos);

        // Filtrar por estado si se proporciona
        if ($estado !== null) {
            $query->where('id_estado', $estado);
        }

        // ✅ Filtrar por mes actual (usando whereHas)
        if ($mes != null && $mes !== "Todos") {
            $query->whereHas('proceso_propiedad', function ($q) {
                $q->whereMonth('fecha_reserva', now()->month)
                    ->orWhereMonth('fecha_fin_reserva', now()->month);
            });
        }

        // Filtrar por asesor si no tiene acceso global
        if (!$tieneAccesoGlobal) {
            $query->whereHas('proceso_propiedad', function ($query) use ($usuarioId) {
                $query->where('asesor', $usuarioId);
            });
        }



        return $query->orderBy('id_estado', 'asc')->get();
    }

    /**
     * Guardar nuevo estado de reserva
     */
    public function guardarEstado(array $data, $usuarioId)
    {
        Log::info($data);
        // Crear nuevo historial
        $historial = Historial_estado_reserva::create([
            'id_estado' => $data['estado'],
            'observaciones' => $data['detalle'] ?? null,
            'fecha_carga' => now(),
            'quien_cargo' => $usuarioId,
            'id_proceso_propiedad' => $data['idProcesoPropiedad']
        ]);
        $proceso = Proceso_propiedad::find($data['idProcesoPropiedad']);
        $folio = Empresas_propiedades::where('propiedad_id', $proceso->id_propiedad)->first();
        $notificar = Usuario_sector::where('contrato_nuevo', 'S')->get();


        //Armamos la parte de notificacion si el estado pasa reserva finalizada
        $mensajeBase = [
            'descripcion'       => "Nuevo ingreso, Folio: " . $folio->folio ?? "-",
            'fecha'             => now()->isoFormat('DD/MM/YYYY'),
            'hora'              => now()->isoFormat('HH:mm'),
            'activo'            => 1,
            'cliente_id'        => null,
            'id_criterio_venta' => null,
            'pertenece'         => "contratoNuevo",
            'folio'             => $folio->folio ?? "-"
        ];

        // Si es estado 3, actualizar fecha de firma
        if ($data['estado'] == 3) {
            $historial_estado_contrato = Historial_estado_contrato::create([
                'id_estado' => 7,
            ]);
            $historial->update(['fecha_firma' => now()]);

            foreach ($notificar as $us) {
                $usuario = Usuario::find($us->id_usuario); // ajustá el nombre del campo FK real
                if ($usuario) {
                    $mensaje = array_merge($mensajeBase, ['usuarioNotificar' => $usuario->id]);
                    $usuario->notify(new RecordatorioNotificacion($mensaje));
                }
            }
        }

        // Si es estado 4 (finalizado), restaurar estado de propiedad
        if ($data['estado'] == 4) {
            $this->restaurarEstadoPropiedad($data['idProcesoPropiedad'], $usuarioId);
        }

        // Actualizar proceso con nuevo historial
        Proceso_propiedad::where('id', $data['idProcesoPropiedad'])
            ->update([
                'id_historial_estado_reserva' => $historial->id,
                'id_historial_estado_contrato' => $historial_estado_contrato->id ?? null
            ]);

        return $historial;
    }

    /**
     * Restaurar estado inicial de la propiedad
     */
    private function restaurarEstadoPropiedad($idProcesoPropiedad, $usuarioId)
    {
        $proceso = Proceso_propiedad::find($idProcesoPropiedad);
        if ($proceso) {
            $propiedad = Propiedad::find($proceso->id_propiedad);
            if ($propiedad) {
                $propiedad->update([
                    'id_estado_alquiler' => $proceso->estado_alquiler_inicial,
                    'updated_at' => now(),
                    'last_modified_by' => $usuarioId,
                ]);
            }
        }
    }

    /**
     * Obtener historial de una reserva
     */
    public function getHistorial($idProcesoPropiedad)
    {
        $historial = Historial_estado_reserva::where('id_proceso_propiedad', $idProcesoPropiedad)
            ->with(['estado'])
            ->orderBy('id', 'desc')
            ->get();

        // Agregar nombres de usuarios
        foreach ($historial as $item) {
            $usuario = Usuario::find($item->quien_cargo);
            $item->quien_cargo = $usuario ? $usuario->username : null;
        }

        return $historial;
    }

    /**
     * Obtener reservas identificadas
     */
    public function getReservasIdentificadas($montoAceptado = null)
    {
        $query = Proceso_propiedad::whereNotNull('id_historial_estado_reserva')
            ->where('tipo_reserva', 'TRANSFERENCIA');

        if ($montoAceptado !== null) {
            $query->where('monto_aceptado', $montoAceptado);
        }

        $idsHistorialesActivos = $query->pluck('id_historial_estado_reserva');

        return Historial_estado_reserva::with([
            'estado',
            'proceso_propiedad.propiedad.folios',
            'proceso_propiedad.propiedad.calle',
            'proceso_propiedad.cliente',
            'proceso_propiedad.propiedad.precioActual'
        ])
            ->whereIn('id', $idsHistorialesActivos)
            ->orderBy('id_estado', 'asc')
            ->get();
    }

    /**
     * Guardar reserva identificada
     */
    public function guardarReservaIdentificada($idProcesoPropiedad, $usuarioId)
    {
        $proceso = Proceso_propiedad::find($idProcesoPropiedad);
        if ($proceso) {
            $proceso->update([
                'monto_aceptado' => 1,
                'quien_modifico' => $usuarioId,
            ]);
            return true;
        }
        return false;
    }

    /**
     * Obtener contenido del comprobante
     */
    public function obtenerComprobante($rutaCompleta)
    {
        if (!file_exists($rutaCompleta)) {
            throw new \RuntimeException('Archivo no encontrado');
        }

        $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
        $nombreArchivo = basename($rutaCompleta);

        $contentType = match ($extension) {
            'pdf'        => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'        => 'image/png',
            default      => 'application/octet-stream',
        };

        return [
            'content' => file_get_contents($rutaCompleta),
            'contentType' => $contentType,
            'filename' => $nombreArchivo
        ];
    }
}
