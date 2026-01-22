<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    public function marcarUnaComoLeida(string $id)
{
    $usuario = Usuario::find(session('usuario_id'));

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
}
