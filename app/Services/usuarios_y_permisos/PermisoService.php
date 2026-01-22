<?php

namespace App\Services\usuarios_y_permisos;

use App\Models\usuarios_y_permisos\Nav;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\Vista;
use App\Services\usuarios_y_permisos\UsuarioService;
use App\Services\usuarios_y_permisos\NavService;

class PermisoService
{

    public function getMenuData(){
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

    private function getTodosLosPermisos($userId){
        $permisos = Permiso::where('usuario_id', $userId)->get();
        return $permisos;
    }


    
}
