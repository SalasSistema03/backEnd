<?php

namespace App\Http\Controllers;

use App\Models\cliente\Usuario_sector;
use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    public function marcarUnaComoLeida(string $id)
    {
        $usuarioId = auth('api')->id();
        $usuario = Usuario::find($usuarioId);

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        $notificacion = $usuario->notifications()->where('id', $id)->first();

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

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
        $esAsesor = Usuario_sector::where('id_usuario', $usuarioId)
            ->where('venta', 'S')->exists();

        if ($esAsesor === true) {
            $notificaciones = $usuario->notifications()
                ->where('data->usuarioNotificar', $usuarioId)
                ->orderBy('created_at', 'desc')
                ->where('read_at', '=', null)
                ->get();
        }
        return response()->json(['success' => true, 'data' => $notificaciones]);
    }
}
