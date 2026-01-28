<?php

namespace App\Services\usuarios_y_permisos;

use App\Models\usuarios_y_permisos\Nav;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\Vista;
use App\Models\usuarios_y_permisos\Boton;
use App\Services\usuarios_y_permisos\UsuarioService;
use App\Models\usuarios_y_permisos\Botones;
use App\Models\agenda\Sectores;
use App\Models\agenda\Agenda;




class PermisoService
{

    public function getMenuData()
    {
        //Obtenemos el id del usuario
        $userId = (new UsuarioService())->devolverUsuario()->id;
        //Obtenemos todos los permisos del usuario
        $permisos = $this->getTodosLosPermisos($userId);

        // Obtener IDs únicos de navs, vistas y botones
        $navsPermitidos = $permisos->pluck('nav_id')->unique()->values();
        $vistasPermitidas = $permisos->pluck('vista_id')->unique()->values();
        $botonesPermitidos = $permisos->pluck('boton_id')->unique()->values();

        // Obtener navs y vistas
        $navs = Nav::whereIn('id', $navsPermitidos)->get();
        //$navs = (new NavService())->getNavPermitidos($navsPermitidos);
        //$navs = $this->getPermittedNavigations($navsPermitidos);
        $vistas = Vista::whereIn('id', $vistasPermitidas)->get();
        $seccionesUnicas = $vistas->unique('Seccion');

        // Construir estructura del menú
        $menuEstructura = $this->construirMenu($navs, $vistas, $seccionesUnicas);


        return response()->json([
            'success' => true,
            'data' => [
                'menu' => $menuEstructura,
                'permisos' => [
                    'navs' => $navsPermitidos,
                    'vistas' => $vistasPermitidas,
                    'botones' => $botonesPermitidos
                ]
            ]
        ]);
    }

    private function construirMenu($navs, $vistas, $seccionesUnicas)
    {
        $menu = [];

        foreach ($navs as $nav) {
            $secciones = [];

            foreach ($seccionesUnicas as $seccion) {
                if ($nav->id == $seccion->menu_id) {
                    $items = [];

                    foreach ($vistas as $vista) {
                        if ($seccion->Seccion == $vista->Seccion && $vista->es_nav == 1) {
                            $items[] = [
                                'id' => $vista->id,
                                'nombre' => $vista->nombre_visual,
                                'ruta' => $vista->ruta
                            ];
                        }
                    }

                    if (!empty($items)) {
                        $secciones[] = [
                            'nombre' => $seccion->Seccion,
                            'items' => $items
                        ];
                    }
                }
            }

            if (!empty($secciones)) {
                $menu[] = [
                    'id' => $nav->id,
                    'nombre' => $nav->menu,
                    'secciones' => $secciones
                ];
            }
        }

        return $menu;
    }

    private function getTodosLosPermisos($userId)
    {
        $permisos = Permiso::where('usuario_id', $userId)->get();
        return $permisos;
    }


    public function getPermisosNavegacion()
    {
        // Obtener todos los navs
        $navs = Nav::all();

        // Obtener todas las vistas agrupadas por menu_id con solo los campos necesarios
        $vistasPorMenu = Vista::select('id', 'Seccion', 'nombre_visual', 'menu_id')
            ->get()
            ->groupBy('menu_id');
            
        // Obtener todos los botones agrupados por vista_id (no menu_id)
        $botonesPorVista = Botones::select('id','nombre_visual','vista_id')->get()->groupBy('vista_id');

        // Construir la estructura jerárquica
        $navegacion = [];

         $sectores = Sectores::select('id', 'nombre')->get();

        foreach ($navs as $nav) {
            $vistasConBotones = [];
            
            // Obtener las vistas de este menú
            $vistas = $vistasPorMenu->get($nav->id, []);
            
            foreach ($vistas as $vista) {
                // Agregar botones relacionados a cada vista
                $vistaConBotones = $vista->toArray();
                $vistaConBotones['botones'] = $botonesPorVista->get($vista->id, []);
                
                $vistasConBotones[] = $vistaConBotones;

            }

            $navegacion[] = [
                'id' => $nav->id,
                'menu' => $nav->menu,
                'vistas' => $vistasConBotones,
                'sectores' => $sectores
            ];
        }

        /* dd($sectores); */
        return response()->json($navegacion);
    }

    /**
     * Procesa y guarda los permisos de un usuario
     * Funcion utilizada en el register de AuthController
     *
     * @param int   $usuarioId
     * @param array $permisos
     * @return void
     */
    public function asignarPermisos(int $usuarioId, array $permisos): void
    {
        foreach ($permisos as $permiso) {

            // Esperamos: [nav_id, vista_id, boton_id?, sector_id?]
            if (!is_array($permiso) || count($permiso) < 3) {
                continue;
            }

            // Crear permiso
            Permiso::create([
                'nav_id'     => $permiso[0] ?? null,
                'vista_id'   => $permiso[1] ?? null,
                'boton_id'   => $permiso[2] ?? null,
                'usuario_id'=> $usuarioId,
            ]);

            // Si viene sector (4to elemento), crear agenda
            if (count($permiso) === 4 && !empty($permiso[3])) {
                Agenda::create([
                    'sector_id' => $permiso[3],
                    'usuario_id' => $usuarioId,
                ]);
            }
        }
    }

}
