<?php

namespace App\Http\Controllers;

use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Atcl1Vista;
use App\Models\usuarios_y_permisos\AtclPadronBtn;
use App\Models\usuarios_y_permisos\Nav; // Adjusted namespace to match the correct location of the Nav model
use App\Models\usuarios_y_permisos\AtclPropiedadBtn; // Added missing import for AtclPropiedadBtn
use App\Models\usuarios_y_permisos\Botones;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\UsuarioBtn;
use App\Models\usuarios_y_permisos\Vista;
use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\UsuarioVista;
use App\Models\agenda\Sectores;
use Illuminate\Support\Facades\DB;


class ValidacionesController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::distinct('id')->get(); // Obtener usuarios únicos basados en el campo 'id'
        $nav = Nav::where('id', '>', 0)->get();

        $vistas = Vista::whereIn('menu_id', $nav->pluck('id'))->get();
        $botones = Botones::all();


        // Obtener el usuario seleccionado (si existe)
        $usuario_id = $request->input('usuario_id');
        $permisos = collect(); // Inicializar permisos como una colección vacía

        $sectoresAsignados = [];
        if ($usuario_id) {
            // Cargar los permisos del usuario seleccionado
            $permisos = Permiso::where('usuario_id', $usuario_id)->get();
            // Cargar los sectores asignados al usuario desde la tabla agenda (mysql6)
            $sectoresAsignados = DB::connection('mysql6')
                ->table('agenda')
                ->where('usuario_id', $usuario_id)
                ->pluck('sector_id')
                ->toArray();
        }
        // Obtener los sectores con sus IDs y nombres
        $sectores = Sectores::select('id', 'nombre')->get();




        return view('validaciones.validaciones', compact(
            'usuarios',
            'nav',
            'vistas',
            'botones',
            'permisos',
            'usuario_id',
            'sectores',
            'sectoresAsignados'
        ));
    }


    public function create()
    {
        //
    }



    public function store(Request $request)
    {
        $vistas = $request->input('vistas', []);
        $botones = $request->input('botones', []);
        $sectores = $request->input('sectores', []);
        $usuario_id = $request->input('usuario_id');

        // Obtener los permisos actuales del usuario
        $permisosActuales = Permiso::where('usuario_id', $usuario_id)->get();

        // Procesar los botones seleccionados
        $botonesProcesados = [];
        if (!empty($botones)) {
            foreach ($botones as $boton) {
                $partes_botones = explode('|', $boton);

                if (count($partes_botones) === 3) {
                    $nav_id = $partes_botones[0];
                    $vista_id = $partes_botones[1];
                    $boton_id = $partes_botones[2];

                    // Verificar si el permiso ya existe
                    $permisoExistente = $permisosActuales->where('boton_id', $boton_id)
                        ->where('vista_id', $vista_id)
                        ->first();

                    if (!$permisoExistente) {
                        Permiso::create([
                            'usuario_id' => $usuario_id,
                            'boton_id' => $boton_id,
                            'vista_id' => $vista_id,
                            'nav_id' => $nav_id,
                        ]);
                    }
                    $botonesProcesados[] = $boton_id;
                }
            }
        }

        // Procesar las vistas seleccionadas
        $vistasProcesadas = [];
        if (!empty($vistas)) {
            foreach ($vistas as $vista) {
                $partes_vista = explode('|', $vista);

                if (count($partes_vista) === 2) {
                    $nav_id = $partes_vista[0];
                    $vista_id = $partes_vista[1];

                    // Verificar si el permiso ya existe
                    $permisoExistente = $permisosActuales
                        ->where('vista_id', $vista_id)
                        ->where('boton_id', null)
                        ->first();

                    if (!$permisoExistente) {
                        Permiso::create([
                            'usuario_id' => $usuario_id,
                            'vista_id' => $vista_id,
                            'nav_id' => $nav_id,
                        ]);
                    }
                    $vistasProcesadas[] = $vista_id;
                }
            }
        }

        // Eliminar permisos que no están en los checkboxes seleccionados
        foreach ($permisosActuales as $permiso) {
            if (
                (!in_array($permiso->boton_id, $botonesProcesados) && $permiso->boton_id !== null) ||
                (!in_array($permiso->vista_id, $vistasProcesadas) && $permiso->vista_id !== null && $permiso->boton_id === null)
            ) {
                $permiso->delete();
            }
        }

        // Manejo de sectores
        $db = DB::connection('mysql6');

        // Obtener los sectores actuales del usuario
        $sectoresActuales = $db->table('agenda')
            ->where('usuario_id', $usuario_id)
            ->pluck('sector_id')
            ->toArray();

        // Eliminar sectores que ya no están seleccionados
        $sectoresAEliminar = array_diff($sectoresActuales, $sectores);
        if (!empty($sectoresAEliminar)) {
            $db->table('agenda')
                ->where('usuario_id', $usuario_id)
                ->whereIn('sector_id', $sectoresAEliminar)
                ->delete();
        }

        // Agregar nuevos sectores que no existían
        $sectoresAAgregar = array_diff($sectores, $sectoresActuales);
        foreach ($sectoresAAgregar as $sector) {
            $db->table('agenda')->insert([
                'usuario_id' => $usuario_id,
                'sector_id' => $sector,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Actualizar la marca de tiempo de los sectores existentes
        $db->table('agenda')
            ->where('usuario_id', $usuario_id)
            ->update(['updated_at' => now()]);

        return redirect()->back()->with('success', 'Permisos guardados correctamente.');
    }
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
