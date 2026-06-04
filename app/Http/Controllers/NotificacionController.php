<?php

namespace App\Http\Controllers;

use App\Models\cliente\Usuario_sector;
use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class NotificacionController extends Controller
{
    public function marcarUnaComoLeida(string $id)
    {
        Log::info('Marcar notificación como leída', ['notificacionId' => $id]);
        $usuarioId = auth('api')->id();
        $usuario = Usuario::find($usuarioId);

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        $notificacion = DatabaseNotification::on('mysql4')
            ->where('id', $id)
            ->where('data->usuarioNotificar', $usuarioId)
            ->where('notifiable_type', 'App\Models\usuarios_y_permisos\Usuario')
            ->first();

        if ($notificacion) {
            $notificacion->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
    }

    public function traerNotificaciones()
    {
        $usuarioId = auth('api')->id();
        $usuario = Usuario::find($usuarioId);
       // Log::alert('Usuario para santiago', ['usuarioId' => $usuarioId]);

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
        $esAsesor = Usuario_sector::where('id_usuario', $usuarioId)
            ->where('venta', 'S')->exists();
        //Log::info('Es asesor para santiago', ['esAsesor' => $esAsesor]);

        if ($esAsesor === true) {
            $notificaciones = DatabaseNotification::on('mysql4')
                ->where('data->usuarioNotificar', $usuarioId)
                ->where('notifiable_type', 'App\Models\usuarios_y_permisos\Usuario')
                ->orderBy('created_at', 'desc')
                ->whereNull('read_at')
                ->get();
                  //Log::info('Notificaciones', [$notificaciones]);
        }

        return response()->json(['success' => true, 'data' => $notificaciones]);
    }

}
