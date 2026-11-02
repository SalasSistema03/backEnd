<?php

namespace App\Services\At_cl;

use App\Models\At_cl\HistorialEstadosAlquiler;
use App\Models\At_cl\HistorialEstadosVenta;
use App\Models\At_cl\Propiedad;
use App\Models\sys\Propiedades_sys;
use App\Models\sys\Contratos_cabecera_sys;
use App\Models\sys\Contratos_detalle_sys;
use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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

    /**
     * Obtiene los registros de empresa_propiedad (folio por empresa) de una propiedad.
     *
     * @param int|string $id ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerEmpresaPropiedad($id)
    {
        return Empresas_propiedades::where('propiedad_id', $id)->get();
    }


    /**
     * Obtiene el contrato de la empresa 1 con la fecha de rescisión más lejana
     * entre los inmuebles asociados a los folios de la propiedad.
     *
     * Los folios se buscan en empresa_propiedad. Cada folio corresponde a la
     * columna carpeta de propiedades_sys, que aporta el id_casa del contrato.
     *
     * @param int|string $id ID de la propiedad.
     * @return object|null Objeto con folio, inicio_contrato, vencimiento_contrato e id_casa, o null si no hay folios o contratos.
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
     * La empresa se determina a partir del folio en empresa_propiedad. Se toma
     * la cabecera de contrato con el id_contrato_cabecera más alto y el detalle
     * cuyo rango de fechas incluye la fecha actual.
     *
     * Si no encuentra detalle vigente, devuelve 0.
     *
     * @param array|\Illuminate\Support\Collection $idCasas IDs de casa de la propiedad.
     * @param int|string $folio Folio de la propiedad.
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

        if ($alquiler->isEmpty()) {
            return 0;
        }

        return $alquiler->first()->monto_alquiler;
    }

    /**
     * Obtiene el monto de alquiler del contrato activo de la empresa 1 (documentos = 'S').
     *
     * A diferencia de obtenerAlquiler(), este método no valida si el resultado
     * está vacío: si no hay detalle vigente, first() devuelve null y el acceso
     * a monto_alquiler produce un error.
     *
     * @param array|\Illuminate\Support\Collection $idCasas IDs de casa de la propiedad.
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
     *  - Código exacto de alquiler o venta
     *  - Coincidencia parcial en nombre de calle o número
     *
     * Devuelve una colección con datos ya transformados para mostrar en la vista.
     *
     * @param string $codigo
     * @param string $calle Texto parcial del nombre de calle o del número.
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


            $data['id_zona'] = $prop->id_zona;

            unset($data['id_calle'], $data['id_barrio']);
            return $data;
        });
    }

    /**
     * Busca propiedades en venta.
     *
     * Si se envía un código, devuelve solo la propiedad con ese cod_venta exacto,
     * sin aplicar el resto de los filtros. Si no, aplica calle, dormitorios,
     * baños y cochera, y descarta en PHP las propiedades sin cod_venta o con
     * id_estado_venta 3 (FINALIZADA), 4 (BAJA TEMPORAL), 6 (RETIRADA) o 7 (BAJA).
     *
     * @param string $codigo Código de venta exacto.
     * @param string $calle Texto parcial del nombre de calle o del número.
     * @param int|null $dormitorios Cantidad de dormitorios exacta.
     * @param int|null $banios Cantidad de baños exacta.
     * @param string $cochera Valor del campo cochera.
     * @return \Illuminate\Support\Collection Arrays con los datos de cada propiedad.
     */
    public function buscarPropiedadesVenta(string $codigo = '', string $calle = '', int $dormitorios = null, int $banios = null, string $cochera = '')
    {
        $query = Propiedad::with(['calle', 'barrio', 'zona', 'tipoInmueble']);

        // Filtro por código (solo, sin combinación)
        if ($codigo) {
            $props = $query->where('cod_venta', $codigo)->get();
            return $props->map(function ($prop) {
                $data = $prop->toArray();
                $data['calle'] = isset($prop->calle->name)
                    ? $prop->calle->name . ' ' . $prop->numero_calle
                    : $prop->numero_calle;
                $data['barrio'] = $prop->barrio->name ?? null;
                $data['zona'] = $prop->zona->name ?? null;
                $data['inmueble'] = $prop->tipoInmueble->inmueble ?? null;

                $data['id_zona'] = $prop->id_zona;

                unset($data['id_calle'], $data['id_barrio']);
                return $data;
            });
        }

        // Filtros combinados (calle, dormitorios, baños, cochera)
        $query->when($calle, function ($q) use ($calle) {
            $q->whereHas(
                'calle',
                fn($query) =>
                $query->where('name', 'like', "%{$calle}%")
                    ->orWhere('numero_calle', 'like', "%{$calle}%")
            );
        })
            ->when($dormitorios, function ($q) use ($dormitorios) {
                $q->where('cantidad_dormitorios', $dormitorios);
            })
            ->when($banios, function ($q) use ($banios) {
                $q->where('banios', $banios);
            })
            ->when($cochera, function ($q) use ($cochera) {
                $q->where('cochera', $cochera);
            });

        $props = $query->get();

        $props = $props->filter(function ($prop) {
            return $prop->cod_venta !== null
                && !in_array($prop->id_estado_venta, ['3', '4', '6', '7']);
        });

        return $props->map(function ($prop) {
            $data = $prop->toArray();
            $data['calle'] = isset($prop->calle->name)
                ? $prop->calle->name . ' ' . $prop->numero_calle
                : $prop->numero_calle;
            $data['barrio'] = $prop->barrio->name ?? null;
            $data['zona'] = $prop->zona->name ?? null;
            $data['inmueble'] = $prop->tipoInmueble->inmueble ?? null;

            $data['id_zona'] = $prop->id_zona;

            unset($data['id_calle'], $data['id_barrio']);
            return $data;
        });
    }

    /**
     * Busca propiedades en alquiler con los mismos parámetros que buscarPropiedadesVenta.
     *
     * Si se envía un código, devuelve solo la propiedad con ese cod_alquiler exacto,
     * sin aplicar el resto de los filtros. Si no, aplica calle, dormitorios,
     * baños y cochera, y conserva solo las propiedades con cod_alquiler y
     * id_estado_alquiler 1 (EN ALQUILER) o 2 (EN ALQUILER COMPARTIDO).
     *
     * @param string $codigo Código de alquiler exacto.
     * @param string $calle Texto parcial del nombre de calle o del número.
     * @param int|null $dormitorios Cantidad de dormitorios exacta.
     * @param int|null $banios Cantidad de baños exacta.
     * @param string $cochera Valor del campo cochera.
     * @return \Illuminate\Support\Collection Arrays con los datos de cada propiedad.
     */
    public function buscarPropiedadesAlquilerCompleto(string $codigo = '', string $calle = '', int $dormitorios = null, int $banios = null, string $cochera = '')
    {
        $query = Propiedad::with(['calle', 'barrio', 'zona', 'tipoInmueble']);

        // Filtro por código (solo, sin combinación)
        if ($codigo) {
            $props = $query->where('cod_alquiler', $codigo)->get();
            return $props->map(function ($prop) {
                $data = $prop->toArray();
                $data['calle'] = isset($prop->calle->name)
                    ? $prop->calle->name . ' ' . $prop->numero_calle
                    : $prop->numero_calle;
                $data['barrio'] = $prop->barrio->name ?? null;
                $data['zona'] = $prop->zona->name ?? null;
                $data['inmueble'] = $prop->tipoInmueble->inmueble ?? null;
                $data['id_zona'] = $prop->id_zona;
                unset($data['id_calle'], $data['id_barrio']);
                return $data;
            });
        }

        // Filtros combinados (calle, dormitorios, baños, cochera)
        $query->when($calle, function ($q) use ($calle) {
            $q->whereHas(
                'calle',
                fn($query) =>
                $query->where('name', 'like', "%{$calle}%")
                    ->orWhere('numero_calle', 'like', "%{$calle}%")
            );
        })
            ->when($dormitorios, function ($q) use ($dormitorios) {
                $q->where('cantidad_dormitorios', $dormitorios);
            })
            ->when($banios, function ($q) use ($banios) {
                $q->where('banios', $banios);
            })
            ->when($cochera, function ($q) use ($cochera) {
                $q->where('cochera', $cochera);
            });

        $props = $query->get();

        // Filtrar solo propiedades con cod_alquiler y estados activos (1, 2)
        $props = $props->filter(function ($prop) {
            return $prop->cod_alquiler !== null
                && in_array($prop->id_estado_alquiler, [1, 2]);
        });

        return $props->map(function ($prop) {
            $data = $prop->toArray();
            $data['calle'] = isset($prop->calle->name)
                ? $prop->calle->name . ' ' . $prop->numero_calle
                : $prop->numero_calle;
            $data['barrio'] = $prop->barrio->name ?? null;
            $data['zona'] = $prop->zona->name ?? null;
            $data['inmueble'] = $prop->tipoInmueble->inmueble ?? null;
            $data['id_zona'] = $prop->id_zona;
            unset($data['id_calle'], $data['id_barrio']);
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

    /**
     * Registra en el historial de venta y/o alquiler de una propiedad, solo si
     * cambió respecto del último registro guardado.
     *
     * Compara el estado, el comentario y la fecha de baja temporal con el último
     * historial de cada tipo. Si no hay cambios, no inserta nada. Cada bloque se
     * puede omitir con sus flags.
     *
     * @param int $id_propiedad ID de la propiedad.
     * @param int|null $nuevo_estado_venta Nuevo id_estado_venta.
     * @param int|null $nuevo_estado_alquiler Nuevo id_estado_alquiler.
     * @param string|null $descripcion_estado_alquiler Comentario del estado de alquiler.
     * @param string|null $descripcion_estado_venta Comentario del estado de venta.
     * @param string|null $fecha_baja_temporal_alquiler Fecha de reactivación del alquiler; null si no aplica.
     * @param string|null $fecha_baja_temporal_venta Fecha de reactivación de la venta; null si no aplica.
     * @param int $usuario_id Usuario que realiza el cambio.
     * @param bool $actualizarVenta Si es false, no se evalúa el historial de venta.
     * @param bool $actualizarAlquiler Si es false, no se evalúa el historial de alquiler.
     * @return void
     */
    public function guardarHistorialEstadosSerbive(
        $id_propiedad,
        $nuevo_estado_venta,
        $nuevo_estado_alquiler,
        $descripcion_estado_alquiler,
        $descripcion_estado_venta,
        $fecha_baja_temporal_alquiler,
        $fecha_baja_temporal_venta,
        $usuario_id,
        bool $actualizarVenta = true,
        bool $actualizarAlquiler = true
    ) {
        // Últimos historiales (pueden ser null si nunca se guardó nada)
        $ultimo_historial_estado_venta = $this->obtenerUltimoHistorialEstadosVenta($id_propiedad);
        $ultimo_historial_estado_alquiler = $this->obtenerUltimoHistorialEstadosAlquiler($id_propiedad);

        // Bloque venta: inserta un historial nuevo si no hay registros previos o si
        // cambió el estado, el comentario o la fecha de baja temporal.
        if ($actualizarVenta && (
            !$ultimo_historial_estado_venta ||
            $ultimo_historial_estado_venta->id_estado_venta != $nuevo_estado_venta ||
            $ultimo_historial_estado_venta->comentario != $descripcion_estado_venta ||
            $ultimo_historial_estado_venta->reactiva_fecha != $fecha_baja_temporal_venta
        )) {
            $historialEstadoVenta = new HistorialEstadosVenta();
            $historialEstadoVenta->id_propiedad   = $id_propiedad;
            $historialEstadoVenta->id_estado_venta = $nuevo_estado_venta;
            $historialEstadoVenta->comentario     = $descripcion_estado_venta;
            $historialEstadoVenta->fecha          = now();
            $historialEstadoVenta->id_usuario     = $usuario_id;
            $historialEstadoVenta->reactiva_fecha = $fecha_baja_temporal_venta ?: null;
            $historialEstadoVenta->save();
        }

        // Bloque alquiler: inserta un historial nuevo si no hay registros previos o si
        // cambió el estado, el comentario o la fecha de baja temporal.
        if ($actualizarAlquiler && (
            !$ultimo_historial_estado_alquiler ||
            $ultimo_historial_estado_alquiler->id_estado_alquiler != $nuevo_estado_alquiler ||
            $ultimo_historial_estado_alquiler->comentario_alquiler != $descripcion_estado_alquiler ||
            $ultimo_historial_estado_alquiler->reactiva_fecha_alquiler != $fecha_baja_temporal_alquiler
        )) {
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

    /**
     * Obtiene el último registro del historial de estados de venta de una propiedad.
     *
     * Lo usa guardarHistorialEstadosSerbive() para comparar con el nuevo estado, y
     * PropiedadController::actualizarPropiedad() para completar los valores que no cambian.
     *
     * @param int|string $id_propiedad ID de la propiedad.
     * @return \App\Models\At_cl\HistorialEstadosVenta|null Último registro por id, o null si no hay historial.
     */
    public function obtenerUltimoHistorialEstadosVenta($id_propiedad)
    {
        return HistorialEstadosVenta::where('id_propiedad', $id_propiedad)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Obtiene el último registro del historial de estados de alquiler de una propiedad.
     *
     * Lo usa guardarHistorialEstadosSerbive() para comparar con el nuevo estado, y
     * PropiedadController::actualizarPropiedad() para completar los valores que no cambian.
     *
     * @param int|string $id_propiedad ID de la propiedad.
     * @return \App\Models\At_cl\HistorialEstadosAlquiler|null Último registro por id, o null si no hay historial.
     */
    public function obtenerUltimoHistorialEstadosAlquiler($id_propiedad)
    {
        return HistorialEstadosAlquiler::where('id_propiedad', $id_propiedad)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Crea una propiedad con los datos del formulario de alta, dentro de una transacción.
     *
     * Los datos vienen agrupados por secciones: comodidades, descripcion, venta,
     * alquiler y condicionAlquiler. Las claves ausentes se guardan como null.
     * El usuario de la operación queda registrado en last_modified_by.
     *
     * @param array $datos Datos del formulario de alta.
     * @param int $userId ID del usuario que crea la propiedad.
     * @return Propiedad Propiedad creada.
     *
     * @throws \Exception Si la inserción falla; la transacción se revierte.
     */
    public function crearPropiedad(array $datos, int $userId): Propiedad
    {

        try {
            DB::beginTransaction();

            $propiedad = Propiedad::create([

                'id_calle' => $datos['calle_id'],
                'numero_calle' => $datos['altura'],
                'ph' => $datos['ph'],
                'piso' => $datos['piso'],
                'departamento' => $datos['dto'],
                'id_inmueble' => $datos['inmueble_id'],
                'id_zona' => $datos['zona_id'],
                'id_provincia' => $datos['provincia_id'],
                'id_localidad' => $datos['localidad_id'],
                'llave' => $datos['llave'],
                'comentario_llave' => $datos['comentario_llave'],
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
                'flyer_v' => $datos['venta']['flyer_v'] ?? null,
                'reel_v' => $datos['venta']['reel_v'] ?? null,
                'web_v' => $datos['venta']['web_v'] ?? null,
                'captador_int_v' => $datos['venta']['captador_interno_v'] ?? null,
                'asesor' => $datos['venta']['asesor_resultado'] ?? null,
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
                'updated_at' => now(),
                'captador_int_a' => $datos['alquiler']['captador_interno_a'] ?? null,
                'flyer_a' => $datos['alquiler']['flyer_a'] ?? null,
                'reel_a' => $datos['alquiler']['reel_a'] ?? null,
                'web_a' => $datos['alquiler']['web_a'] ?? null,
                'latitud' => $datos['latitud'] ?? null,
                'longitud' => $datos['longitud'] ?? null,

            ]);

            DB::commit();
            return $propiedad;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('No se pudo crear la propiedad: ' . $e->getMessage());
        }
    }

    /**
     * Busca propiedades en alquiler para el autocompletado de reservas.
     *
     * El término se compara con cod_alquiler (coincidencia parcial) o, por cada
     * palabra, con el nombre de la calle o con el número (ej: "san lorenzo 3245").
     * Solo incluye propiedades con id_estado_alquiler 1 (EN ALQUILER) o 2 (EN
     * ALQUILER COMPARTIDO), ordenadas por cod_alquiler y limitadas a 30 resultados.
     *
     * @param string $cod_alquiler Código de alquiler o texto de dirección.
     * @return \Illuminate\Http\JsonResponse Lista de propiedades con id, cod_alquiler, calle, numero, piso, departamento, folio, estado y precio.
     */
    public function buscarPropiedadesAlquiler($cod_alquiler)
    {

    //Log::info("entro al servicio");
        $codigo = $cod_alquiler;


        // Sin término de búsqueda devuelve una respuesta vacía
        if (!$codigo) {
            return response()->json([]);
        }

        // Cada palabra debe coincidir con el nombre de la calle o con el número (ej: "san lorenzo 3245")
        $palabras = preg_split('/\s+/', trim((string) $codigo), -1, PREG_SPLIT_NO_EMPTY);

        $propiedades = Propiedad::with(['calle', 'empresas', 'precioActual'])
            ->whereIn('id_estado_alquiler', [1, 2]) // Solo EN ALQUILER y EN ALQUILER COMPARTIDO
            ->whereNotNull('cod_alquiler')
            ->where(function ($q) use ($codigo, $palabras) {
                $q->where('cod_alquiler', 'like', "%{$codigo}%")
                    ->orWhere(function ($dir) use ($palabras) {
                        foreach ($palabras as $palabra) {
                            $dir->where(function ($w) use ($palabra) {
                                $w->whereHas('calle', fn($c) => $c->where('name', 'like', "%{$palabra}%"))
                                    ->orWhere('numero_calle', 'like', "%{$palabra}%");
                            });
                        }
                    });
            })
            ->orderBy('cod_alquiler')
            ->limit(30)
            ->get();


        // Formatea la salida para el front
        $resultado = $propiedades->map(function ($prop) {
            $folios = $prop->empresas->map(function ($empresa) {
                $folio = optional($empresa->pivot)->folio;
                if (!$folio) {
                    return null;
                }

                return match ($empresa->id) {
                    2 => "CAN {$folio}",
                    3 => "TRIB {$folio}",
                    default => $folio,
                };
            })
                ->filter()
                ->values()
                ->implode(', ');

            return [
                'id' => $prop->id,
                'cod_alquiler' => $prop->cod_alquiler,
                'calle' => $prop->calle->name ?? 'Sin calle',
                'numero' => $prop->numero_calle,
                'direccion' => trim(sprintf('%s %s', $prop->calle->name ?? '', $prop->numero_calle)),
                'folio' => $folios,
                'estado' => $prop->estadoAlquiler->name ?? null,
                'piso' => $prop->piso,
                'departamento' => $prop->departamento,
                'precio' => $prop->precioActual
            ];
        });
        //Log::info($resultado);
        return response()->json($resultado);
    }
}
