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
use App\Services\agenda\AgendaService;




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


    public function sincronizarPermisos(array $permisos, int $usuario_id): void
    {
        [$permisosRecibidos, $sectoresRecibidos] = $this->separarPermisosYSectores($permisos);

        $this->sincronizarPermisosUsuario($permisosRecibidos, $usuario_id);
        
        if (!empty($sectoresRecibidos)) {
            (new AgendaService())->sincronizarSectores($sectoresRecibidos, $usuario_id);
        }
    }

    private function separarPermisosYSectores(array $permisos): array
    {
        $permisosRecibidos = [];
        $sectoresRecibidos = [];

        foreach ($permisos as $permiso) {
            if (!is_array($permiso) || count($permiso) < 3) {
                continue;
            }

            $permisosRecibidos[] = [
                $permiso[0] ?? null, 
                $permiso[1] ?? null, 
                $permiso[2] ?? null
            ];

            // Extraer sector si existe
            if (count($permiso) == 4 && $permiso[3] !== null) {
                $sectoresRecibidos[] = $permiso[3];
            }
        }

        return [
            array_unique($permisosRecibidos, SORT_REGULAR),
            array_unique($sectoresRecibidos)
        ];
    }

    private function sincronizarPermisosUsuario(array $permisosNuevos, int $usuario_id): void
    {
        $permisosActuales = $this->obtenerPermisosActuales($usuario_id);

        // Eliminar permisos obsoletos
        $this->eliminarPermisosObsoletos($permisosActuales, $permisosNuevos, $usuario_id);

        // Agregar nuevos permisos
        $this->agregarPermisosNuevos($permisosActuales, $permisosNuevos, $usuario_id);
    }

    private function obtenerPermisosActuales(int $usuario_id): array
    {
        return Permiso::where('usuario_id', $usuario_id)
            ->select('nav_id', 'vista_id', 'boton_id')
            ->get()
            ->map(fn($p) => [$p->nav_id, $p->vista_id, $p->boton_id])
            ->toArray();
    }

    private function eliminarPermisosObsoletos(
        array $permisosActuales, 
        array $permisosNuevos, 
        int $usuario_id
    ): void {
        $permisosAEliminar = array_diff(
            array_map('json_encode', $permisosActuales),
            array_map('json_encode', $permisosNuevos)
        );

        if (empty($permisosAEliminar)) {
            return;
        }

        foreach (array_map('json_decode', $permisosAEliminar) as $permiso) {
            Permiso::where('usuario_id', $usuario_id)
                ->where('nav_id', $permiso[0])
                ->where('vista_id', $permiso[1])
                ->where('boton_id', $permiso[2])
                ->delete();
        }
    }

    private function agregarPermisosNuevos(
        array $permisosActuales, 
        array $permisosNuevos, 
        int $usuario_id
    ): void {
        $permisosAAgregar = array_diff(
            array_map('json_encode', $permisosNuevos),
            array_map('json_encode', $permisosActuales)
        );

        if (empty($permisosAAgregar)) {
            return;
        }

        $datosInsertar = [];
        foreach (array_map('json_decode', $permisosAAgregar) as $permiso) {
            $datosInsertar[] = [
                'nav_id' => $permiso[0],
                'vista_id' => $permiso[1],
                'boton_id' => $permiso[2],
                'usuario_id' => $usuario_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Inserción masiva más eficiente
        Permiso::insert($datosInsertar);
    }
}
