<?php

namespace App\Services\At_cl;

use Illuminate\Support\Facades\DB;
use App\Models\usuarios_y_permisos\Permiso;

/**
 * Servicio encargado de verificar los permisos de acceso a botones, vistas
 * y secciones específicas dentro del sistema de propiedades.
 *
 * Este servicio centraliza la lógica de permisos basada en la relación entre
 * usuarios, botones y vistas configuradas en la tabla de permisos.
 */
class PermitirAccesoPropiedadService
{
    /**
     * ID del usuario al que se le verificarán los permisos.
     *
     * @var int
     */
    protected $userId;
    /**
     * Constructor del servicio.
     *
     * @param int $userId  ID del usuario autenticado.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }


    /**
     * Verifica si el usuario tiene permiso para un botón específico.
     *
     * Relación utilizada:
     *  - Permiso → Boton (btn_nombre)
     *
     * @param string $btnNombre  Nombre del botón a validar.
     * @return bool              True si tiene permiso, false en caso contrario.
     */
    public function tieneAcceso($btnNombre)
    {
        // Busca un registro de permiso donde el usuario tenga asociado el boton indicado.
        $permiso = Permiso::where('usuario_id', $this->userId)
            ->whereHas('boton', function ($query) use ($btnNombre) {
                $query->where('btn_nombre', $btnNombre);
            })
            ->first();

        return $permiso !== null; // Retorna true si tiene acceso, false si no
    }

    /**
     * Valida permisos sobre botones relacionados con el padrón.
     *
     * Funcionalmente similar a tieneAcceso(), pero mantenido separado
     * para permitir reglas de negocio diferentes en el futuro.
     *
     * @param string $btnNombre  Nombre del botón asociado al padrón.
     * @return bool              True si tiene permiso, false en caso contrario.
     */
    public function tieneAccesoPadron($btnNombre)
    {
        // Filtra permisos por usuario y por el botón específico del padrón.
        $permiso = Permiso::where('usuario_id', $this->userId)
            ->whereHas('boton', function ($query) use ($btnNombre) {
                $query->where('btn_nombre', $btnNombre);
            })
            ->first();

        return $permiso !== null;
    }

    /**
     * Verifica si el usuario tiene permiso para acceder a una vista del sistema.
     *
     * Relación utilizada:
     *  - Permiso → Vista (vista_nombre)
     *
     * @param string $vistaNombre  Identificador único de la vista.
     * @return bool                True si el usuario puede acceder, false si no.
     */
    public function tieneAccesoAVista(string $vistaNombre): bool
    {
        // Busca un permiso donde la vista asociada coincida con el nombre dado.
        $permiso = Permiso::where('usuario_id', $this->userId)
            ->whereHas('vista', function ($query) use ($vistaNombre) {
                $query->where('vista_nombre', $vistaNombre);
            })
            ->first();

        return $permiso !== null;
    }
}
