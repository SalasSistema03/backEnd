<?php

namespace App\Http\Controllers\proceso\Concerns;

use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Http\JsonResponse;

/**
 * Permiso de carga de inventario, verificado en el servidor (la vista solo lo oculta).
 * Mismo criterio que Permisos::verificarAccesoBotones_Elementos: tabla permisos (mysql4).
 */
trait VerificaPermisoInventario
{
    private const PERMISO_CARGA = 'GuardarInventario';

    private function sinPermiso(): ?JsonResponse
    {
        $usuario = Usuario::find(auth('api')->id());

        $tienePermiso = $usuario && $usuario->permisos()
            ->whereHas('boton', fn($q) => $q->where('btn_nombre', self::PERMISO_CARGA))
            ->exists();

        return $tienePermiso ? null : response()->json([
            'success' => false,
            'message' => 'No tenés permiso para modificar el inventario.',
        ], 403);
    }
}
