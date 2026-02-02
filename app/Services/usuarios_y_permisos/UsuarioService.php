<?php

namespace App\Services\usuarios_y_permisos;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\agenda\Agenda;
use App\Models\agenda\Sectores;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\agenda\AgendaService;
use App\Services\usuarios_y_permisos\PermisoService;

class UsuarioService
{

    public function devolverUsuario()
    {
        // Obtener el usuario autenticado desde el token JWT
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        return $user;
    }

    public function esAdmin()
    {
        $user = auth('api')->user();
        if ($user->admin == 1) {
            return response()->json(['message' => 'Usuario es admin'], 200);
        }
        return response()->json(['message' => 'Usuario no es admin'], 401);
    }

    public function getNombresDeUsuarios()
    {
        $usuarios = Usuario::select('id', 'name', 'username')->get();
        return response()->json($usuarios);
    }

    public function getDatosGenerales($id_usuario)
    {
        $usuario = Usuario::where('id', $id_usuario)
            ->select('id', 'name', 'username', 'password', 'telf_interno', 'telf_laboral', 'fecha_nac', 'email_interno', 'email_externo')
            ->first();

        $permisos = Permiso::where('usuario_id', $id_usuario)->get();
        $agenda = Agenda::where('usuario_id', $id_usuario)->get();
        //$sectores = Sectores::select('id', 'nombre')->get();

        return response()->json([
            'usuario' => $usuario,
            'permisos' => $permisos,
            'agenda' => $agenda,
            //'sectores' => $sectores
        ]);
    }



    public function updateDatosGenerales(Request $request, $id_usuario)
    {
        $usuario = Usuario::where('id', $id_usuario)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Actualizar datos básicos del usuario
        $datosActualizar = $this->prepararDatosActualizacion($request);

        if (empty($datosActualizar)) {
            return response()->json(['error' => 'No hay datos para actualizar'], 400);
        }

        $usuario->update($datosActualizar);

        // Delegar la gestión de permisos al servicio correspondiente
        if ($request->has('permisos')) {
            (new PermisoService())->sincronizarPermisos(
                $request->input('permisos'),
                $id_usuario
            );
        }

        return response()->json([
            'message' => 'Datos generales actualizados correctamente',
            'id_usuario' => $id_usuario,
            'datos_actualizados' => $datosActualizar
        ]);
    }

    private function prepararDatosActualizacion(Request $request): array
    {
        $campos = [
            'nombreCompleto' => 'name',
            'contraseña' => 'password',
            'telefonoInterno' => 'telf_interno',
            'telefonoLaboral' => 'telf_laboral',
            'fechaNacimiento' => 'fecha_nac',
            'emailInterno' => 'email_interno',
            'emailExterno' => 'email_externo'
        ];

        $datosActualizar = [];
        foreach ($campos as $campo => $columna) {
            if ($request->filled($campo)) {
                $datosActualizar[$columna] = $request->input($campo);
            }
        }

        // Hash de contraseña si viene
        /* if (isset($datosActualizar['password'])) {
            $datosActualizar['password'] = bcrypt($datosActualizar['password']);
        } */

        return $datosActualizar;
    }
}
