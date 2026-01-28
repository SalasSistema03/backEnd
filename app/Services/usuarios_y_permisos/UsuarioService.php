<?php

namespace App\Services\usuarios_y_permisos;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\agenda\Agenda;
use App\Models\agenda\Sectores;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
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

    public function esAdmin(){
        $user = auth('api')->user();
        if($user->admin == 1){
            return response()->json(['message' => 'Usuario es admin'],200);
        }
        return response()->json(['message' => 'Usuario no es admin'],401);
    } 

    public function getNombresDeUsuarios(){
        $usuarios = Usuario::select('id', 'name', 'username')->get();
        return response()->json($usuarios);
    }

    public function getDatosGenerales($id_usuario){
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

    public function updateDatosGenerales(Request $request, $id_usuario){
    // Log del ID del usuario
    Log::info('estoy en updateDatosGenerales', ['id_usuario' => $id_usuario]);

    // También puedes ver todos los datos de una vez
    Log::info('Todos los datos del request:', $request->all());

    $usuario = Usuario::where('id', $id_usuario)->first();

    if (!$usuario) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    // Opción 1: Actualizar solo los campos que no son nulos
    $datosActualizar = [];
    
    if ($request->filled('nombreCompleto')) {
        $datosActualizar['name'] = $request->input('nombreCompleto');
    }
    
    if ($request->filled('contraseña')) {
        $datosActualizar['password'] = $request->input('contraseña');
    }
    
    if ($request->filled('telefonoInterno')) {
        $datosActualizar['telf_interno'] = $request->input('telefonoInterno');
    }
    
    if ($request->filled('telefonoLaboral')) {
        $datosActualizar['telf_laboral'] = $request->input('telefonoLaboral');
    }
    
    if ($request->filled('fechaNacimiento')) {
        $datosActualizar['fecha_nac'] = $request->input('fechaNacimiento');
    }
    
    if ($request->filled('emailInterno')) {
        $datosActualizar['email_interno'] = $request->input('emailInterno');
    }
    
    if ($request->filled('emailExterno')) {
        $datosActualizar['email_externo'] = $request->input('emailExterno');
    }

    // Solo actualizar si hay datos para cambiar
    if (!empty($datosActualizar)) {
        $usuario->update($datosActualizar);
        Log::info('Datos actualizados:', $datosActualizar);
    } else {
        Log::info('No hay datos para actualizar');
    }

    // Procesar permisos si vienen en el request
    if ($request->has('permisos')) {
        $this->procesarPermisos($request->input('permisos'), $id_usuario);
    }

    return response()->json([
        'message' => 'Datos generales actualizados correctamente',
        'id_usuario' => $id_usuario,
        'datos_actualizados' => $datosActualizar,
        'datos_recibidos' => $request->all()
    ]);
}

private function procesarPermisos($permisos, $usuario_id) {
    // Separar permisos y sectores del array recibido
    $permisosRecibidos = [];
    $sectoresRecibidos = [];
    
    foreach ($permisos as $permiso) {
        if (is_array($permiso) && count($permiso) >= 3) {
            $permisosRecibidos[] = [$permiso[0] ?? null, $permiso[1] ?? null, $permiso[2] ?? null];
            
            // Si tiene 4 elementos, extraer el sector
            if (count($permiso) == 4 && $permiso[3] !== null) {
                $sectoresRecibidos[] = $permiso[3];
            }
        }
    }
    
    // Eliminar duplicados de permisos y sectores
    $permisosRecibidos = array_unique($permisosRecibidos, SORT_REGULAR);
    $sectoresRecibidos = array_unique($sectoresRecibidos);
    
    // Procesar permisos (método incremental como ValidacionesController)
    $this->procesarPermisosIncremental($permisosRecibidos, $usuario_id);
    
    // Procesar sectores (método incremental como ValidacionesController)
    $this->procesarSectoresIncremental($sectoresRecibidos, $usuario_id);
    
    Log::info('Permisos y sectores procesados', [
        'usuario_id' => $usuario_id,
        'total_permisos' => count($permisosRecibidos),
        'total_sectores' => count($sectoresRecibidos)
    ]);
}

private function procesarPermisosIncremental($permisosNuevos, $usuario_id) {
    // Obtener permisos actuales del usuario
    $permisosActuales = Permiso::where('usuario_id', $usuario_id)
        ->select('nav_id', 'vista_id', 'boton_id')
        ->get()
        ->map(function($p) {
            return [$p->nav_id, $p->vista_id, $p->boton_id];
        })
        ->toArray();
    
    // Eliminar permisos que ya no están seleccionados
    $permisosAEliminar = array_diff(
        array_map('json_encode', $permisosActuales),
        array_map('json_encode', $permisosNuevos)
    );
    
    if (!empty($permisosAEliminar)) {
        $permisosAEliminarArray = array_map('json_decode', $permisosAEliminar);
        
        foreach ($permisosAEliminarArray as $permisoEliminar) {
            Permiso::where('usuario_id', $usuario_id)
                ->where('nav_id', $permisoEliminar[0])
                ->where('vista_id', $permisoEliminar[1])
                ->where('boton_id', $permisoEliminar[2])
                ->delete();
        }
        
        Log::info('Permisos eliminados', [
            'usuario_id' => $usuario_id,
            'cantidad' => count($permisosAEliminarArray)
        ]);
    }
    
    // Agregar nuevos permisos que no existían
    $permisosAAgregar = array_diff(
        array_map('json_encode', $permisosNuevos),
        array_map('json_encode', $permisosActuales)
    );
    
    if (!empty($permisosAAgregar)) {
        $permisosAAgregarArray = array_map('json_decode', $permisosAAgregar);
        
        foreach ($permisosAAgregarArray as $permisoAgregar) {
            Permiso::create([
                'nav_id' => $permisoAgregar[0],
                'vista_id' => $permisoAgregar[1],
                'boton_id' => $permisoAgregar[2],
                'usuario_id' => $usuario_id
            ]);
        }
        
        Log::info('Permisos agregados', [
            'usuario_id' => $usuario_id,
            'cantidad' => count($permisosAAgregarArray)
        ]);
    }
}

private function procesarSectoresIncremental($sectores, $usuario_id) {
    // Obtener sectores actuales del usuario
    $sectoresActuales = Agenda::where('usuario_id', $usuario_id)
        ->pluck('sector_id')
        ->toArray();
    
    // Eliminar sectores que ya no están seleccionados
    $sectoresAEliminar = array_diff($sectoresActuales, $sectores);
    if (!empty($sectoresAEliminar)) {
        Agenda::where('usuario_id', $usuario_id)
            ->whereIn('sector_id', $sectoresAEliminar)
            ->delete();
            
        Log::info('Sectores eliminados', [
            'usuario_id' => $usuario_id,
            'sectores' => $sectoresAEliminar
        ]);
    }
    
    // Agregar nuevos sectores que no existían
    $sectoresAAgregar = array_diff($sectores, $sectoresActuales);
    foreach ($sectoresAAgregar as $sector) {
        Agenda::create([
            'usuario_id' => $usuario_id,
            'sector_id' => $sector
        ]);
    }
    
    if (!empty($sectoresAAgregar)) {
        Log::info('Sectores agregados', [
            'usuario_id' => $usuario_id,
            'sectores' => $sectoresAAgregar
        ]);
    }
    
    // Actualizar la marca de tiempo de los sectores existentes
    if (!empty($sectores)) {
        Agenda::where('usuario_id', $usuario_id)
            ->whereIn('sector_id', $sectores)
            ->update(['updated_at' => now()]);
    }
}

}