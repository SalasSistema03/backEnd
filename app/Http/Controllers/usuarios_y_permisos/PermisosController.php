<?php

namespace App\Http\Controllers\usuarios_y_permisos;

use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Permiso;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Models\usuarios_y_permisos\Nav;

class PermisosController
{
    public function exportarPermisosPdf($usuario_id)
    {
        // 1. Buscamos el usuario
        $usuario = Usuario::findOrFail($usuario_id);

        // 2. Traemos TODO el árbol del sistema ordenado
        $todoElArbol = Nav::with(['vistas' => function ($query) {
            $query->orderBy('vista_nombre');
        }, 'vistas.botones'])->orderBy('menu')->get();

        // 3. Traemos los permisos específicos del usuario
        $permisosUsuario = Permiso::where('usuario_id', $usuario_id)->get();

        // 4. Creamos arrays simples con los IDs que tiene permitidos para cruzar en la vista
        $navsPermitidos = $permisosUsuario->pluck('nav_id')->filter()->toArray();
        $vistasPermitidas = $permisosUsuario->pluck('vista_id')->filter()->toArray();
        $botonesPermitidos = $permisosUsuario->pluck('boton_id')->filter()->toArray();

        $data = [
            'usuario' => $usuario,
            'arbol' => $todoElArbol,
            'navs_permitidos' => $navsPermitidos,
            'vistas_permitidas' => $vistasPermitidas,
            'botones_permitidos' => $botonesPermitidos,
        ];

        $html = view('pdfs.usuarios.permisosUsuario', compact('data'))->render();

        return response()->streamDownload(function () use ($html, $usuario) {
            echo Browsershot::html($html)
                ->format('A4')
                // ->landscape()  <-- eliminá o comentá esta línea, A4 por defecto ya es vertical
                ->margins(8, 8, 8, 8)
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', false)
                ->pdf();
        }, 'Permisos_' . $usuario->username . '.pdf');
    }
}
