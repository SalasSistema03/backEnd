<?php

namespace App\Services;

use App\Models\Agenda\Recordatorio;
use App\Models\At_cl\Usuario;
use App\Notifications\RecordatorioNotificacion;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para la gestión integral de recordatorios del sistema.
 *
 * Este servicio abstrae la lógica relacionada con:
 *  - Creación de recordatorios automáticos para asesores
 *  - Finalización y reprogramación automática según intervalos
 *  - Actualización de recordatorios existentes
 *  - Sincronización de notificaciones pendientes en la base externa
 *
 * Centralizar esta lógica facilita el mantenimiento y reduce
 * la duplicación de código en controladores.
 *
 * @category   Services
 * @package    App\Services
 * @see        Recordatorio
 * @see        RecordatorioNotificacion
 */
class RecordatorioService
{
    /**
     * Crea un nuevo recordatorio asociado a la carga de un cliente.
     *
     * Este método se ejecuta normalmente desde un controlador en el momento en que
     * se registra un nuevo cliente para un asesor. El procedimiento incluye:
     *
     *  1. Crear el registro del recordatorio en la base de datos
     *  2. Construir el payload que será enviado en la notificación
     *  3. Enviar la notificación al asesor utilizando el canal configurado
     *  4. Registrar en el log la acción realizada
     *
     * @param  int  $asesorId  Identificador del asesor destinatario
     * @return Recordatorio     Instancia persistida del recordatorio creado
     *
     * @throws \Exception Si ocurre algún error inesperado durante la operación
     */
    public function crearDesdeCliente(int $asesorId, string $nombreCliente)
    {
        // Log::info('Logs provenientes de recordatorioService', $asesorId);
        // Crear el registro del recordatorio
        $recordatorio = Recordatorio::create([
            'descripcion'         => 'Nuevo Cliente ' . $nombreCliente,
            'fecha_inicio'        => now(),
            'agenda_id'           => null,
            'hora'                => now()->format('H:i'),
            'intervalo'           => 'Diario',
            'cantidad'            => 1,
            'usuario_carga'       => $asesorId,
            'usuario_finaliza'    => null,
            'activo'              => 0,
            'fecha_actualizacion' => now(),
            'fecha_fin'           => now(),
            'repetir'             => 1,
            'es_criterio'         => 1,
            'es_asesor_activo'    => 1,
        ]);

        //Log::info('esto es un recordatorio creado: ' . $recordatorio->id);
        // Construcción del mensaje que recibe la notificación
        $mensaje = [
            'pertenece'         => 'cliente',
            'id'                => $recordatorio->id,
            'descripcion'       => $recordatorio->descripcion,
            'fecha'             => $recordatorio->fecha_inicio->toDateString(),
            'hora'              => $recordatorio->hora,
            'activo'            => 0,
            'es_asesor_activo'  => 1,
            'es_criterio'       => 0,
        ];

        // Enviar notificación al asesor si existe
        if ($usuario = Usuario::find($asesorId)) {
            $usuario->notify(new RecordatorioNotificacion($mensaje));
        }

        //Log::info('Recordatorio creado desde servicio', $recordatorio->toArray());

        return $recordatorio;
    }

    /**
     * Crea un recordatorio asociado a un nuevo criterio cargado por un cliente
     * y envía una notificación al asesor correspondiente.
     *
     * Este método registra un recordatorio destinado al asesor indicado,
     * generando un mensaje de notificación con metadatos relevantes que permiten
     * su posterior seguimiento en la bandeja de recordatorios.
     *
     * @param  int    $asesorId       ID del asesor que recibirá el recordatorio
     * @param  string $nombreCliente  Nombre del cliente asociado al criterio
     *
     * @return \App\Models\Recordatorio instancia del recordatorio creado
     * @access public
     */
    public function crearDesdeCriterio(int $asesorId, string $nombreCliente)
    {
        /* Crea un registro de recordatorio con valores por defecto para eventos de criterios */
        $recordatorio = Recordatorio::create([
            'descripcion'         => 'Nuevo Criterio para el cliente ' . $nombreCliente,
            'fecha_inicio'        => now(),
            'agenda_id'           => null,
            'hora'                => now()->format('H:i'),
            'intervalo'           => 'Diario',
            'cantidad'            => 1,
            'usuario_carga'       => $asesorId,
            'usuario_finaliza'    => null,
            'activo'              => 0,
            'fecha_actualizacion' => now(),
            'fecha_fin'           => now(),
            'repetir'             => 1,
            'es_criterio'         => 1,
            'es_asesor_activo'    => 0,
        ]);

        /* Construcción del payload que se envía en la notificación */
        $mensaje = [
            'pertenece'         => 'criterio',
            'id'                => $recordatorio->id,
            'descripcion'       => $recordatorio->descripcion,
            'fecha'             => $recordatorio->fecha_inicio->toDateString(),
            'hora'              => $recordatorio->hora,
            'activo'            => 0,
            'es_asesor_activo'  => 0,
            'es_criterio'       => 1,
        ];

        /* Si el asesor existe, se emite la notificación asociada al recordatorio */
        if ($usuario = Usuario::find($asesorId)) {
            $usuario->notify(new RecordatorioNotificacion($mensaje));
        }

        /* Retorna el recordatorio recién creado para usos posteriores */
        return $recordatorio;
    }


    /**
     * Marca un recordatorio como finalizado y programa la próxima fecha según su intervalo.
     *
     * Este método se invoca cuando el asesor finaliza un recordatorio. El comportamiento es:
     *
     *  - Actualizar `fecha_inicio` con la última fecha registrada
     *  - Calcular la nueva fecha de actualización en base a:
     *      * intervalo = Diario o Mensual
     *      * cantidad  = cantidad de días/meses a sumar
     *  - Verificar si la nueva fecha supera `fecha_fin`
     *      * Si supera → el recordatorio se desactiva
     *      * Si no supera → se reprograma la próxima ejecución
     *
     * @param  Recordatorio $recordatorio Instancia que se desea finalizar
     * @return Recordatorio                Instancia actualizada del recordatorio
     */
    public function finalizarRecordatorio(Recordatorio $recordatorio): Recordatorio
    {
        // Actualizar la fecha de inicio con el último valor registrado
        $recordatorio->fecha_inicio = $recordatorio->fecha_actualizacion;
        $recordatorio->save();

        // Normalización de la cantidad
        $cantidad = (int) ($recordatorio->cantidad ?? 1);
        if ($cantidad <= 0) {
            $cantidad = 1;
        }

        $nueva = Carbon::parse($recordatorio->fecha_actualizacion);

        // Reprogramación según el intervalo configurado
        if ($recordatorio->intervalo === 'Diario') {
            $nueva->addDays($cantidad);
        } elseif ($recordatorio->intervalo === 'Mensual') {
            $nueva->addMonths($cantidad);
        }

        // Validar límites de fecha final
        if ($nueva->lte(Carbon::parse($recordatorio->fecha_fin))) {
            $recordatorio->fecha_actualizacion = $nueva;
        } else {
            $recordatorio->activo = false;
        }

        $recordatorio->save();

        return $recordatorio;
    }

    /**
     * Actualiza un recordatorio existente junto con sus cálculos de fechas.
     *
     * El proceso incluye:
     *
     *  - Recalcular fechas de actualización y fin según:
     *      * intervalo (Diario / Mensual)
     *      * cantidad
     *      * repetir (cantidad de ciclos)
     *  - Actualizar toda la información en el registro del recordatorio
     *
     * @param  Recordatorio $recordatorio  Instancia a modificar
     * @param  array        $data          Datos provenientes del formulario
     * @return Recordatorio                Recordatorio actualizado
     */
    public function actualizarRecordatorio(Recordatorio $recordatorio, array $data): Recordatorio
    {
      
        $fecha_inicio = Carbon::parse($data['fecha_inicio']);

        // Cálculo de fechas según repetición y tipo de intervalo
        if ($data['repetir'] == 1) {
            // Solo 1 ciclo
            if ($data['intervalo'] === 'Diario') {
                $fecha_actualizacion = $fecha_inicio->copy()->addDays((int) $data['cantidad']);
                $fecha_fin = $fecha_actualizacion;
            } else {
                $fecha_actualizacion = $fecha_inicio->copy()->addMonths((int) $data['cantidad']);
                $fecha_fin = $fecha_actualizacion;
            }
        } else {
            // Múltiples ciclos
            if ($data['intervalo'] === 'Diario') {
                $fecha_actualizacion = $fecha_inicio->copy()->addDays((int) $data['cantidad']);
                $fecha_fin = $fecha_inicio->copy()->addDays((int) $data['cantidad'] * (int) $data['repetir']);
            } else {
                $fecha_actualizacion = $fecha_inicio->copy()->addMonths((int) $data['cantidad']);
                $fecha_fin = $fecha_inicio->copy()->addMonths((int) $data['cantidad'] * (int) $data['repetir']);
            }
        }

        // Actualización completa del registro
        $recordatorio->update([
            'descripcion'        => $data['descripcion'],
            'fecha_inicio'       => $data['fecha_inicio'],
            'agenda_id'          => $data['agenda_id'],
            'hora'               => $data['hora'],
            'intervalo'          => $data['intervalo'],
            'cantidad'           => $data['cantidad'],
            'usuario_carga'      => $data['usuario_id'],
            'usuario_finaliza'   => $data['usuario_id'],
            'activo'             => 1,
            'fecha_actualizacion' => $fecha_actualizacion,
            'fecha_fin'          => $fecha_fin,
            'repetir'            => $data['repetir'],
            'es_criterio'        => 0,
            'es_asesor_activo'   => 0,
        ]);

        return $recordatorio;
    }


    /**
     * Actualiza las notificaciones existentes asociadas a un recordatorio.
     *
     * Este método se conecta a la base de datos secundaria (`mysql4`) donde
     * se almacenan las notificaciones generadas para los usuarios, y realiza:
     *
     *  - Búsqueda de todas las notificaciones cuyo campo `data->id` coincida con el recordatorio
     *  - Reconstrucción del JSON manteniendo consistencia con los nuevos datos del recordatorio
     *  - Persistencia de los cambios en la tabla externa
     *
     * Es útil cuando un recordatorio fue modificado y se requiere que la
     * notificación ya entregada al asesor refleje los cambios.
     *
     * @param  Recordatorio $recordatorio Datos actualizados
     * @return void
     */
    public function actualizarNotificaciones(Recordatorio $recordatorio)
    {
        // Obtener notificaciones que pertenecen al recordatorio
        $notificaciones = DB::connection('mysql4')
            ->table('notifications')
            ->where('data->id', $recordatorio->id)
            ->get();

        foreach ($notificaciones as $notificacion) {
            // Decodificar JSON almacenado
            $data = json_decode($notificacion->data, true);

            // Actualizar campos modificados
            $data['fecha'] = $recordatorio->fecha_inicio;
            $data['hora'] = $recordatorio->hora;
            $data['activo'] = $recordatorio->activo;
            $data['descripcion'] = $recordatorio->descripcion;

            // Persistir cambios en la base externa
            DB::connection('mysql4')
                ->table('notifications')
                ->where('id', $notificacion->id)
                ->update([
                    'data' => json_encode($data),
                ]);
        }
    }
}
