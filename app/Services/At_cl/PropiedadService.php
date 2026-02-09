<?php

namespace App\Services\At_cl;

use App\Models\At_cl\HistorialEstadosAlquiler;
use App\Models\At_cl\HistorialEstadosVenta;
use App\Models\At_cl\Propiedad;
use App\Models\sys\Propiedades_sys;
use App\Models\sys\Contratos_cabecera_sys;
use App\Models\sys\Contratos_detalle_sys;
use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Support\Facades\Log;

class PropiedadService
{
    /**
     * Obtiene una propiedad por ID con todas sus relaciones necesarias.
     *
     * Relaciones cargadas: calle, zona, tipoInmueble, precio, propietarios, usuario.
     *
     * @param int|string $id  ID de la propiedad.
     * @return \App\Models\At_cl\Propiedad
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function obtenerPropiedadConId($id)
    {
        return Propiedad::with(['calle', 'zona', 'tipoInmueble', 'precio', 'propietarios', 'usuario'])
            ->findOrFail($id);
    }

    /*
     * Obtiene las empresas asociadas a una propiedad.
     *
     * @param int|string $id ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerEmpresaPropiedad($id)
    {
        return Empresas_propiedades::where('propiedad_id', $id)->get();
    }


    /**
     * Obtiene el folio con el vencimiento de contrato más grande y su fecha de inicio.
     *
     * @param int|string $id ID de la propiedad
     * @return object|null Objeto con folio, inicio_contrato y vencimiento_contrato
     */
    public function obtenerContratoMasReciente($id)
    {
        // Obtener los folios de la propiedad
        $folios = Empresas_propiedades::where('propiedad_id', $id)->pluck('folio');

        if ($folios->isEmpty()) {
            return null;
        }

        // Obtener los id_casa de esos folios
        $idCasas = Propiedades_sys::whereIn('carpeta', $folios)->pluck('id_casa');

        if ($idCasas->isEmpty()) {
            return null;
        }

        // Obtener el contrato con mayor vencimiento
        $contrato = Contratos_cabecera_sys::whereIn('id_casa', $idCasas)
            ->where('id_empresa', 1)
            ->orderBy('rescicion', 'desc')
            ->first();


        if (!$contrato) {
            return null;
        }

        // Obtener el folio de ese contrato
        $propiedad = Propiedades_sys::where('id_casa', $contrato->id_casa)->first();

        return (object) [
            'folio' => $propiedad->carpeta,
            'inicio_contrato' => $contrato->comienza,
            'vencimiento_contrato' => $contrato->rescicion,
            'id_casa' => $contrato->id_casa,
        ];
    }




    /**
     * Obtiene el monto de alquiler del contrato activo (documentos = 'N').
     *
     * Si no encuentra historial vigente, devuelve 0.
     *
     * @param array|Collection $idCasas
     * @return float|int
     */
    public function obtenerAlquiler($idCasas, $folio)
    {
        $empresaId = Empresas_propiedades::where('folio', $folio)->value('empresa_id');

        $id_contrato_cabecera = Contratos_cabecera_sys::where('id_casa', $idCasas)
            ->where('id_empresa', $empresaId)
            ->where('documentos', 'N')
            ->max('id_contrato_cabecera');

        $alquiler = Contratos_detalle_sys::where('id_contrato_cabecera', $id_contrato_cabecera)
            ->whereDate('desde_fecha', '<=', now())
            ->whereDate('hasta_fecha', '>=', now())
            ->get('monto_alquiler');
        /* dd($alquiler->first()->monto_alquiler);  */
        if ($alquiler->isEmpty()) {
            return 0;
        }
        return $alquiler->first()->monto_alquiler;
    }

    /**
     * Obtiene el monto de alquiler del contrato activo (documentos = 'S').
     *
     * A diferencia de obtenerAlquiler(), este método **no valida vacío**.
     * Asume que siempre habrá un contrato vigente.
     *
     * @param array|Collection $idCasas
     * @return float|int|null
     */
    public function obtenerAlquilerN($idCasas)
    {
        $id_contrato_cabecera = Contratos_cabecera_sys::whereIn('id_casa', $idCasas)
            ->where('id_empresa', 1)
            ->where('documentos', 'S')
            ->max('id_contrato_cabecera');

        $alquiler = Contratos_detalle_sys::where('id_contrato_cabecera', $id_contrato_cabecera)
            ->whereDate('desde_fecha', '<=', now())
            ->whereDate('hasta_fecha', '>=', now())
            ->get('monto_alquiler');
        /* dd($alquiler->first()->monto_alquiler); */
        return $alquiler->first()->monto_alquiler;
    }


    /**
     * Obtiene los propietarios asociados a una propiedad.
     *
     * @param int|string $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerPropietarios($id)
    {
        $propiedad = $this->obtenerPropiedadConId($id);
        return $propiedad->propietarios;
    }

    /**
     * Busca propiedades por:
     *  - Código de alquiler/venta
     *  - Coincidencia parcial en nombre de calle o número
     *
     * Devuelve una colección con datos ya transformados para mostrar en la vista.
     *
     * @param string $codigo
     * @param string $calle
     * @return \Illuminate\Support\Collection
     */
    public function buscarPropiedades(string $codigo = '', string $calle = '')
    {
        $props = Propiedad::with(['calle', 'barrio', 'zona', 'tipoInmueble'])
            ->when(
                $codigo,
                fn($q) => $q
                    ->where('cod_alquiler', $codigo)
                    ->orWhere('cod_venta', $codigo)
            )
            ->when($calle, function ($q) use ($calle) {
                $q->whereHas(
                    'calle',
                    fn($query) =>
                    $query->where('name', 'like', "%{$calle}%")
                )
                    ->orWhere('numero_calle', 'like', "%{$calle}%");
            })
            ->get();

        return $props->map(function ($prop) {
            $data = $prop->toArray();
            $data['calle'] = isset($prop->calle->name)
                ? $prop->calle->name . ' ' . $prop->numero_calle
                : $prop->numero_calle;
            $data['barrio'] = $prop->barrio->name ?? null;
            $data['zona'] = $prop->zona->name ?? null;
            $data['inmueble'] = $prop->tipoInmueble->inmueble ?? null;

            // Agregá de nuevo si querés usarlo en la vista
            $data['id_zona'] = $prop->id_zona;

            unset($data['id_calle'], $data['id_barrio']); // ahora sí podés eliminar estos si no se usan
            return $data;
        });
    }

    /**
     * Obtiene una propiedad por ID sin relaciones.
     *
     * @param int|string $id
     * @return \App\Models\At_cl\Propiedad|null
     */
    public function obtenerPropiedadesPorId(string $id)
    {
        return Propiedad::find($id);
    }


    //Este servicio guarda el historial de estados cuando se realiza SOLO UN CAMBIO EN EL ESTADO de venta o alquiler de una propiedad
    public function guardarHistorialEstadosSerbive(
        $id_propiedad,
        $nuevo_estado_venta,
        $nuevo_estado_alquiler,
        $descripcion_estado_alquiler,
        $descripcion_estado_venta,
        $fecha_baja_temporal_alquiler,
        $fecha_baja_temporal_venta
    ) {
        // Usuario actual
        $usuario = session('usuario');
        $usuario_id = $usuario->id;

        // Últimos historiales (pueden ser null si nunca se guardó nada)
        $ultimo_historial_estado_venta = $this->obtenerUltimoHistorialEstadosVenta($id_propiedad);
        $ultimo_historial_estado_alquiler = $this->obtenerUltimoHistorialEstadosAlquiler($id_propiedad);

        /**
         * BLOQUE VENTA
         * Guardamos un nuevo historial de venta si:
         * - No existe historial previo, o
         * - Cambió el estado, o
         * - Cambió el comentario, o
         * - Cambió la fecha de baja temporal
         */
        if (
            !$ultimo_historial_estado_venta ||
            $ultimo_historial_estado_venta->id_estado_venta != $nuevo_estado_venta ||
            $ultimo_historial_estado_venta->comentario != $descripcion_estado_venta ||
            $ultimo_historial_estado_venta->reactiva_fecha != $fecha_baja_temporal_venta
        ) {
            $historialEstadoVenta = new HistorialEstadosVenta();
            $historialEstadoVenta->id_propiedad   = $id_propiedad;
            $historialEstadoVenta->id_estado_venta = $nuevo_estado_venta;
            $historialEstadoVenta->comentario     = $descripcion_estado_venta;
            $historialEstadoVenta->fecha          = now();
            $historialEstadoVenta->id_usuario     = $usuario_id;
            $historialEstadoVenta->reactiva_fecha = $fecha_baja_temporal_venta ?: null;
            $historialEstadoVenta->save();
        }

        /**
         * BLOQUE ALQUILER
         * Guardamos un nuevo historial de alquiler si:
         * - No existe historial previo, o
         * - Cambió el estado, o
         * - Cambió el comentario, o
         * - Cambió la fecha de baja temporal
         */
        if (
            !$ultimo_historial_estado_alquiler ||
            $ultimo_historial_estado_alquiler->id_estado_alquiler != $nuevo_estado_alquiler ||
            $ultimo_historial_estado_alquiler->comentario_alquiler != $descripcion_estado_alquiler ||
            $ultimo_historial_estado_alquiler->reactiva_fecha_alquiler != $fecha_baja_temporal_alquiler
        ) {
            $historialEstadoAlquiler = new HistorialEstadosAlquiler();
            $historialEstadoAlquiler->id_propiedad          = $id_propiedad;
            $historialEstadoAlquiler->id_estado_alquiler    = $nuevo_estado_alquiler;
            $historialEstadoAlquiler->comentario_alquiler   = $descripcion_estado_alquiler;
            $historialEstadoAlquiler->fecha_alquiler        = now();
            $historialEstadoAlquiler->id_usuario            = $usuario_id;
            $historialEstadoAlquiler->reactiva_fecha_alquiler = $fecha_baja_temporal_alquiler ?: null;
            $historialEstadoAlquiler->save();
        }
    }

    //Obtengo el ultimo historial de estados por el id de la propiedad (Se consume en el controlador de propiedad llamdo "edit") - SOLO DE VENTA
    public function obtenerUltimoHistorialEstadosVenta($id_propiedad)
    {
        return HistorialEstadosVenta::where('id_propiedad', $id_propiedad)
            ->orderBy('id', 'desc')
            ->first(); // devuelve el último registro guardado
    }

    //Obtengo el ultimo historial de estados por el id de la propiedad (Se consume en el controlador de propiedad llamdo "edit") - SOLO DE ALQUILER
    public function obtenerUltimoHistorialEstadosAlquiler($id_propiedad)
    {
        //tragio el ultimo historial de estados por el id de la propiedad
        return HistorialEstadosAlquiler::where('id_propiedad', $id_propiedad)
            ->orderBy('id', 'desc')
            ->first(); // devuelve el último registro guardado
    }

    public function crearPropiedad(array $datos, int $userId): Propiedad
    {
        try {
            return Propiedad::create([
                'id_calle' => $datos['calle_id'],
                'numero_calle' => $datos['altura'],
                'ph' => $datos['ph'],
                'piso' => $datos['piso'],
                'departamento' => $datos['dto'],
                'id_inmueble' => $datos['inmueble_id'],
                'id_zona' => $datos['zona_id'],
                'id_provincia' => $datos['provincia_id'],
                'llave' => $datos['llave'],
                'comentario_llave' => $datos['observaciones_llave'],
                'cartel' => $datos['cartel'],
                'comentario_cartel' => $datos['observaciones_cartel'],
                'id_estado_general' => $datos['comodidades']['estado_general'] ?? null,
                'cantidad_dormitorios' => $datos['comodidades']['dormitorios'] ?? null,
                'banios' => $datos['comodidades']['banios'] ?? null,
                'mLote' => $datos['comodidades']['lotes'] ?? null,
                'mCubiertos' => $datos['comodidades']['lote_cubierto'] ?? null,
                'cochera' => $datos['comodidades']['cochera'] ?? null,
                'numero_cochera' => $datos['comodidades']['numero_cochera'] ?? null,
                'asfalto' => $datos['comodidades']['asfalto'] ?? null,
                'gas' => $datos['comodidades']['gas'] ?? null,
                'cloaca' => $datos['comodidades']['cloaca'] ?? null,
                'agua' => $datos['comodidades']['agua'] ?? null,
                'descipcion_propiedad' => $datos['descripcion']['texto'] ?? null,
                'cod_venta' => $datos['venta']['cod_venta'] ?? null,
                'id_estado_venta' => $datos['venta']['estado_venta'] ?? null,
                'exclusividad_venta' => $datos['venta']['exclusividad_venta'] ?? null,
                'comparte_venta' => $datos['venta']['comparte_venta'] ?? null,
                'condicionado_venta' => $datos['venta']['condicionado_venta'] ?? null,
                'venta_fecha_alta' => $datos['venta']['venta_fecha_alta'] ?? null,
                'fecha_autorizacion_venta' => $datos['venta']['fecha_autorizacion_venta'] ?? null,
                'comentario_autorizacion' => $datos['venta']['comentario_autorizacion'] ?? null,
                'zona_prop' => $datos['venta']['zona_prop'] ?? null,
                'flyer' => $datos['venta']['flyer'] ?? null,
                'reel' => $datos['venta']['reel'] ?? null,
                'web' => $datos['venta']['web'] ?? null,
                'captador_int' => $datos['venta']['captador_int'] ?? null,
                'asesor' => $datos['venta']['asesor'] ?? null,
                'cod_alquiler' => $datos['alquiler']['cod_alquiler'] ?? null,
                'id_estado_alquiler' => $datos['alquiler']['estado_alquiler'] ?? null,
                'autorizacion_alquiler' => $datos['alquiler']['autorizacion_alquiler'] ?? null,
                'fecha_autorizacion_alquiler' => $datos['alquiler']['fecha_autorizacion_alquiler'] ?? null,
                'exclusividad_alquiler' => $datos['alquiler']['exclusividad_alquiler'] ?? null,
                'clausula_de_venta' => $datos['alquiler']['clausula_de_venta'] ?? null,
                'tiempo_clausula' => $datos['alquiler']['tiempo_clausula'] ?? null,
                'alquiler_fecha_alta' => $datos['alquiler']['alquiler_fecha_alta'] ?? null,
                'mascota' => $datos['alquiler']['mascota'] ?? null,
                'condicion' => $datos['condicionAlquiler']['condicion'] ?? null,
                'last_modified_by' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear propiedad: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('No se pudo crear la propiedad: ' . $e->getMessage());
        }
    }
}
