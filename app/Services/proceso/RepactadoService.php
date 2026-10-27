<?php

namespace App\Services\proceso;


use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\proceso\Historial_estado_repactado;
use App\Models\proceso\Estado_repactado;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de carga de repactados.
 *
 * Reúne los contratos que corresponde repactar en un período y empresa, y deja
 * registrada esa carga en historial_estado_repactado.
 */
class RepactadoService
{
    /**
     * Carga el repactado de los contratos que terminan en el período y la empresa indicados.
     *
     * La consulta base reúne los contratos de la empresa cuyo vencimiento o rescisión
     * cae dentro del período, descartando los que ya habían sido rescindidos antes de
     * ese momento. Cada contrato se vincula con su propiedad a través del folio y la
     * empresa, y se registra en historial_estado_repactado el alquiler vigente, las
     * fechas del contrato y el usuario que realizó la carga.
     *
     * La carga se ejecuta dentro de una transacción: si un registro falla, se revierten
     * los inserts ya realizados.
     *
     * @param Request $request Período y empresa del repactado (mes, anio, empresa).
     * @return array Estructura con success, message, data, insertados, omitidos y existentes.
     * @throws \RuntimeException Cuando ningún folio tiene propiedad asociada a la empresa.
     */
    public function getRepactadoDelMes(Request $request)
    {
        $datos = $request->validate([
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => ['required', 'integer', 'digits:4'],
            'empresa' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $mes = (int) $datos['mes'];
        $anio = (int) $datos['anio'];
        $empresa = (int) $datos['empresa'];

        // El período se expresa como AAAAMM para poder compararlo con LEFT(fecha, 7).
        $periodo = sprintf('%04d-%02d', $anio, $mes);

        // contratos_cabecera aporta el contrato y su empresa, contratos_detalle los
        // alquileres pactados y view_propiedades el folio (carpeta) y la dirección de
        // la propiedad. Se toma el mayor monto de alquiler del contrato porque cada
        // actualización de alquiler genera un detalle con su propio importe.
        // El campo finaliza calcula la fecha efectiva de cierre (rescisión anticipada o
        // vencimiento) y motivo distingue el motivo por el que termina el contrato.
        // El filtro por período exige que el vencimiento o la rescisión ocurran en el
        // mes analizado y descarta los contratos ya rescindidos con anterioridad.
        // La condición documentos = 'N' toma el mismo contrato que se considera
        // vigente en PropiedadService::obtenerAlquiler.
        $resultados = DB::connection('mysql2')->select(
            "
            SELECT DISTINCT
                cc.id_contrato_cabecera,
                vp.carpeta AS folio,
                cc.comienza,

                IF(
                    cc.rescicion <> '0000-00-00',
                    IF(
                        cc.rescicion <= cc.finaliza,
                        cc.rescicion,
                        cc.finaliza
                    ),
                    cc.rescicion
                ) AS finaliza,

                cc.id_inquilino,
                cc.id_casa,
                vp.direccion,

                MAX(cd.monto_alquiler) AS monto_alquiler,

                (SELECT cd2.reconocimiento
                 FROM contratos_detalle cd2
                 WHERE cd2.id_contrato_cabecera = cc.id_contrato_cabecera
                 ORDER BY cd2.monto_alquiler DESC
                 LIMIT 1) AS reconocimiento,

                (SELECT rp.documentos
                 FROM repactados rp
                 WHERE rp.id_contrato_cabecera = cc.id_contrato_cabecera
                 ORDER BY rp.id_repactado DESC
                 LIMIT 1) AS monto_documento,

                IF(
                    cc.rescicion <> '0000-00-00',
                    IF(
                        cc.rescicion < cc.finaliza,
                        'RESCICION',
                        'VTO. CONTRATO'
                    ),
                    'VTO. CONTRATO'
                ) AS motivo

            FROM contratos_cabecera AS cc, contratos_detalle AS cd, view_propiedades AS vp

            WHERE
                cc.id_contrato_cabecera = cd.id_contrato_cabecera
                AND cc.id_casa = vp.id_casa
                AND (
                    LEFT(cc.finaliza, 7) = ?
                    OR LEFT(cc.rescicion, 7) = ?
                )
                AND LEFT(cc.rescicion, 7) >= ?
                AND cc.id_empresa = ?
                AND cc.documentos = 'N'

            GROUP BY
                cc.id_contrato_cabecera,
                cc.id_casa
            ",
            [$periodo, $periodo, $periodo, $empresa]
        );
        //Log::info($resultados);


        if (empty($resultados)) {
            return [
                'success' => true,
                'message' => 'No se encontraron repactados para el período seleccionado.',
                'data' => [],
            ];
        }

        // Puede haber contratos duplicados para la misma propiedad y período. Se
        // unifican por folio para no cargar dos veces la misma propiedad y para
        // conservar el monto del documento y el reconocimiento de cualquiera de
        // las copias.
        $resultados = $this->unificarPorFolio($resultados);

        $usuarioId = auth('api')->id();
        $usuario = $usuarioId ? Usuario::find($usuarioId) : null;

        // La propiedad se resuelve por folio y empresa desde empresa_propiedad, que es
        // la tabla donde se cargan los folios de cada empresa.
        $carga = DB::connection('mysql10')->transaction(function () use ($resultados, $empresa, $periodo, $usuario) {
            $insertados = 0;
            $omitidos = [];
            $existentes = [];

            foreach ($resultados as $r) {
                $propiedadId = Empresas_propiedades::where('folio', $r->folio)
                    ->where('empresa_id', $empresa)
                    ->value('propiedad_id');

                // Sin propiedad cargada para ese folio no hay a qué asociar el repactado.
                if (!$propiedadId) {
                    $omitidos[] = $r->folio;
                    continue;
                }

                // Una propiedad no puede tener dos repactados cargados en el mismo período.
                $existe = Historial_estado_repactado::where('periodo_cargado', $periodo)
                    ->where('propiedad_id', $propiedadId)
                    ->exists();

                if ($existe) {
                    $existentes[] = $r->folio;
                    continue;
                }

                $montoDocumento = (float) ($r->monto_documento ?? 0);
                $reconocimiento = (float) ($r->reconocimiento ?? 0);

                Historial_estado_repactado::create([
                    'monto_alquiler_viejo' => $r->monto_alquiler,
                    'monto_documento' => $montoDocumento,
                    'reconocimiento' => $reconocimiento,
                    // El total del repactado se compone del alquiler viejo, el monto
                    // del documento y el reconocimiento que se descuenta.
                    'total' => round((float) $r->monto_alquiler + $montoDocumento - $reconocimiento, 2),
                    'periodo_cargado' => $periodo,
                    'propiedad_id' => $propiedadId,
                    'inicio_contrato' => $r->comienza,
                    'vencimiento_contrato' => $r->finaliza,
                    'quien_guardo' => $usuario->id ?? null,
                ]);

                $insertados++;
            }

            return [
                'insertados' => $insertados,
                'omitidos' => $omitidos,
                'existentes' => $existentes,
            ];
        });

        // Si no se cargó ningún repactado y tampoco había cargas previas del período,
        // la causa fue que los folios encontrados no tienen propiedad asociada a la
        // empresa consultada.
        if ($carga['insertados'] === 0 && empty($carga['existentes'])) {
            throw new \RuntimeException(
                'No se pudieron cargar repactados: no hay propiedades asociadas a la empresa seleccionada.'
            );
        }

        return [
            'success' => true,
            'message' => 'Repactados cargados correctamente.',
            'data' => $resultados,
            'insertados' => $carga['insertados'],
            'omitidos' => $carga['omitidos'],
            'existentes' => $carga['existentes'],
        ];
    }

    /**
     * Unifica los contratos duplicados de una misma propiedad en el período.
     *
     * El sistema de contratos puede tener más de un contrato con la misma casa y
     * las mismas fechas. Se toma el primero como base y se completan el monto del
     * documento, el reconocimiento y el alquiler con los valores de las copias,
     * porque esos datos pueden estar cargados en una sola de ellas.
     *
     * @param array $resultados Filas devueltas por la consulta de contratos.
     * @return array Filas unificadas por folio.
     */
    private function unificarPorFolio(array $resultados): array
    {
        $porFolio = [];

        foreach ($resultados as $resultado) {
            $folio = (int) $resultado->folio;

            if (!isset($porFolio[$folio])) {
                $porFolio[$folio] = $resultado;
                continue;
            }

            $base = $porFolio[$folio];

            if ((float) ($base->monto_documento ?? 0) === 0.0 && (float) ($resultado->monto_documento ?? 0) !== 0.0) {
                $base->monto_documento = $resultado->monto_documento;
            }

            if ((float) ($base->reconocimiento ?? 0) === 0.0 && (float) ($resultado->reconocimiento ?? 0) !== 0.0) {
                $base->reconocimiento = $resultado->reconocimiento;
            }

            if ((float) $resultado->monto_alquiler > (float) $base->monto_alquiler) {
                $base->monto_alquiler = $resultado->monto_alquiler;
            }
        }

        return array_values($porFolio);
    }

    /**
     * Lista los repactados ya cargados en un período y empresa, con folio opcional.
     *
     * Parte de historial_estado_repactado, que guarda un registro por propiedad y
     * período, y lo enriquece con los datos que muestra el listado: el estado del
     * catálogo, la dirección, localidad y tipo de la propiedad y el propietario
     * activo con sus teléfonos (todos desde `sistema_atcl`), y el inquilino del
     * contrato del que provino la carga (desde `mysql2`, consultado por folio/carpeta).
     *
     * @param Request $request Período, empresa y folio opcional (mes, anio, empresa, folio).
     * @return array Repartición de filas para el listado, en formato camelCase.
     */
    public function getRepactadoFiltrado(Request $request)
    {
        $datos = $request->validate([
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => ['required', 'integer', 'digits:4'],
            'empresa' => ['required', 'integer', 'in:1,2,3'],
            'folio' => ['nullable', 'integer', 'between:0,100000'],
        ]);

        $periodo = sprintf('%04d-%02d', (int) $datos['anio'], (int) $datos['mes']);
        $empresa = (int) $datos['empresa'];
        $folioBuscado = isset($datos['folio']) ? (int) $datos['folio'] : null;

        // Los folios de la empresa se guardan indexados por propiedad, que es la
        // clave con la que historial_estado_repactado relaciona cada repactado.
        $folios = Empresas_propiedades::where('empresa_id', $empresa)->pluck('folio', 'propiedad_id');

        if ($folioBuscado !== null) {
            $folios = $folios->filter(fn($folio) => (int) $folio === $folioBuscado);
        }

        if ($folios->isEmpty()) {
            Log::info('hay folios vacios');
            return [];
        }

        $historiales = Historial_estado_repactado::where('periodo_cargado', $periodo)
            ->whereIn('propiedad_id', $folios->keys()->all())
            ->get();

        if ($historiales->isEmpty()) {
            return [];
        }

        $estados = Estado_repactado::pluck('estado', 'id');

        // El sistema de contratos solo aporta la casa de cada folio (carpeta),
        // necesaria para reconstruir el inquilino del contrato. La dirección, la
        // localidad y el tipo salen de sistema_atcl, igual que el propietario.
        $casasPorFolio = [];

        foreach (
            DB::connection('mysql2')->table('view_propiedades')
                ->where('id_empresa', $empresa)
                ->whereIn('carpeta', $folios->values()->all())
                ->get(['carpeta', 'id_casa']) as $fila
        ) {
            $folio = (int) $fila->carpeta;
            $casasPorFolio[$folio][] = (int) $fila->id_casa;
        }

        $casasPorFolio = array_map(fn($casas) => array_unique($casas), $casasPorFolio);
        $datosContrato = $this->obtenerDatosContrato($empresa, $historiales, $folios, $casasPorFolio);

        // La propiedad y sus datos de dominio (calle, localidad, tipo y propietario
        // con teléfonos) pertenecen a sistema_atcl.
        $propiedades = Propiedad::with(['propietarios.telefonos', 'calle', 'localidad', 'tipoInmueble'])
            ->whereIn('id', $historiales->pluck('propiedad_id'))
            ->get()
            ->keyBy('id');

        return $historiales->map(function ($historial) use ($folios, $estados, $datosContrato, $propiedades) {
            $folio = $folios->get($historial->propiedad_id);
            $propiedad = $propiedades->get($historial->propiedad_id);

            // De los propietarios de la propiedad se toma el activo; si no hay
            // ninguno marcado como activo se recurre al primero registrado.
            $propietario = $propiedad
                ? ($propiedad->propietarios->firstWhere('pivot.baja', 'no') ?? $propiedad->propietarios->first())
                : null;

            $telefonoPropietario = null;

            if ($propietario) {
                $telefonoPropietario = $propietario->telefonos->pluck('phone_number')->filter()->implode(' / ');
                $telefonoPropietario = $telefonoPropietario !== '' ? $telefonoPropietario : null;
            }

            $datos = $datosContrato->get($historial->id) ?? [];

            // Para las cargas anteriores a la incorporación de estos campos se
            // completa con los valores del contrato en el sistema de contratos.
            $montoDocumento = (float) ($historial->monto_documento ?? 0);

            if ($montoDocumento === 0.0 && !empty($datos['montoDocumento'])) {
                $montoDocumento = (float) $datos['montoDocumento'];
            }

            $reconocimiento = (float) ($historial->reconocimiento ?? 0);

            if ($reconocimiento === 0.0 && !empty($datos['reconocimiento'])) {
                $reconocimiento = (float) $datos['reconocimiento'];
            }

            $total = round((float) $historial->monto_alquiler_viejo + $montoDocumento - $reconocimiento, 2);

            // La dirección se arma con calle, número, piso y departamento de la
            // propiedad de sistema_atcl.
            $direccion = null;

            if ($propiedad) {
                $partes = array_filter(
                    [$propiedad->calle->name ?? null, $propiedad->numero_calle],
                    fn($parte) => $parte !== null && $parte !== ''
                );

                if ($propiedad->piso !== null && $propiedad->piso !== '') {
                    $partes[] = 'Piso ' . $propiedad->piso;
                }

                if ($propiedad->departamento !== null && $propiedad->departamento !== '') {
                    $partes[] = 'Depto ' . $propiedad->departamento;
                }

                $direccion = $partes !== [] ? implode(' ', $partes) : null;
            }

            return [
                'id' => $historial->id,
                'folio' => $folio,
                'cuentaCorriente' => $historial->cuenta_corriente ?: null,
                'estado' => $estados[$historial->estado] ?? null,
                'estadoId' => $historial->estado !== null ? (int) $historial->estado : null,
                'cierre' => $historial->fecha_cierre ?: null,
                'condicion' => $historial->condicion ?: null,
                'situacion' => $historial->situacion ?: null,
                'observaciones' => $historial->observaciones_estado ?: null,
                'apellidoNombre' => $datos['apellidoNombre'] ?? null,
                'telefono' => !empty($datos['telefono']) ? $datos['telefono'] : null,
                'direccion' => $direccion,
                'localidad' => $propiedad?->localidad?->name,
                'tipo' => $propiedad?->tipoInmueble?->inmueble,
                'comodidades' => $this->formatearComodidades($propiedad),
                'codAlquiler' => $propiedad?->cod_alquiler,
                'codVenta' => $propiedad?->cod_venta,
                'propietario' => $propietario ? trim($propietario->nombre . ' ' . $propietario->apellido) : null,
                'telefonoPropietario' => $telefonoPropietario,
                'viejo' => $historial->monto_alquiler_viejo,
                'montoDocumento' => $montoDocumento,
                'reconocimiento' => $reconocimiento,
                'nuevo' => $historial->nuevo_valor_alquiler ?: null,
                'total' => $total,
                'plazoActualizacion' => $historial->plazo_y_actualizacion_contrato ?: null,
                'inicioContrato' => $historial->inicio_contrato,
                'vencimientoContrato' => $historial->vencimiento_contrato,
            ];
        })->values()->all();
    }

    /**
     * Devuelve el catálogo de estados de repactado.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEstados()
    {
        return Estado_repactado::orderBy('id')->get(['id', 'estado']);
    }

    /**
     * Actualiza los datos editables de un repactado cargado.
     *
     * Los valores económico fijos (alquiler viejo, monto del documento y
     * reconocimiento) no se recalculan desde el front: se conservan los guardados
     * al cargar el repactado. El total se compone de viejo + documento - reconocimiento.
     * Al seleccionar cualquier estado, si todavía no hay fecha de cierre, se
     * completa con la fecha actual.
     *
     * @param Request $request Datos editables del repactado.
     * @return array Estructura con success, message y data.
     * @throws \RuntimeException Cuando el repactado o el estado no existen.
     */
    public function updateRepactado(Request $request)
    {
        $datos = $request->validate([
            'id' => ['required', 'integer'],
            'estado' => ['required', 'integer'],
            'cuenta_corriente' => ['nullable', 'string', 'in:Muy Buena,Buena,Mala,Deudor'],
            'plazo_y_actualizacion_contrato' => ['nullable', 'string', 'max:100'],
            'condicion' => ['nullable', 'string', 'in:No Renueva,Renueva,Retiro'],
            'situacion' => ['nullable', 'string', 'in:Nuevo,Prop Retira,Prorroga,Reingresa'],
            'observaciones_estado' => ['nullable', 'string'],
            'nuevo_valor_alquiler' => ['nullable', 'numeric', 'min:0'],
            'monto_documento' => ['nullable', 'numeric', 'min:0'],
            'reconocimiento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $historial = Historial_estado_repactado::find($datos['id']);

        if (!$historial) {
            throw new \RuntimeException('El repactado seleccionado no existe.');
        }

        $estado = Estado_repactado::find($datos['estado']);

        if (!$estado) {
            throw new \RuntimeException('El estado seleccionado no existe.');
        }

        // El documento y el reconocimiento se guardan al cargar el repactado. Si
        // la carga es anterior a esos campos, se completan con los valores que
        // envía el listado (obtenidos del contrato).
        $montoDocumento = (float) ($historial->monto_documento ?? 0);

        if ($montoDocumento === 0.0 && !empty($datos['monto_documento'])) {
            $montoDocumento = (float) $datos['monto_documento'];
        }

        $reconocimiento = (float) ($historial->reconocimiento ?? 0);

        if ($reconocimiento === 0.0 && !empty($datos['reconocimiento'])) {
            $reconocimiento = (float) $datos['reconocimiento'];
        }

        $total = round((float) $historial->monto_alquiler_viejo + $montoDocumento - $reconocimiento, 2);

        // Cualquier estado seleccionado deja registrada la fecha de cierre. Si ya
        // tenía una, se conserva para no pisar la fecha original.
        $fechaCierre = $historial->fecha_cierre ?: now()->toDateString();

        $historial->update([
            'estado' => $estado->id,
            'cuenta_corriente' => $datos['cuenta_corriente'] ?? null,
            'plazo_y_actualizacion_contrato' => $datos['plazo_y_actualizacion_contrato'] ?? null,
            'condicion' => $datos['condicion'] ?? null,
            'situacion' => $datos['situacion'] ?? null,
            'observaciones_estado' => $datos['observaciones_estado'] ?? null,
            'nuevo_valor_alquiler' => $datos['nuevo_valor_alquiler'] ?? null,
            'monto_documento' => $montoDocumento,
            'reconocimiento' => $reconocimiento,
            'total' => $total,
            'fecha_cierre' => $fechaCierre,
            'quien_guardo' => auth('api')->id() ?? $historial->quien_guardo,
        ]);

        return [
            'success' => true,
            'message' => 'Repactado actualizado correctamente.',
            'data' => $historial->fresh(),
        ];
    }

    /**
     * Arma el texto de comodidades de la propiedad.
     *
     * Se compone con la cantidad de dormitorios y la existencia de cochera de
     * la propiedad de sistema_atcl.
     *
     * @param Propiedad|null $propiedad Propiedad asociada al repactado.
     * @return string|null
     */
    private function formatearComodidades(?Propiedad $propiedad): ?string
    {
        if (!$propiedad) {
            return null;
        }

        $partes = [];

        if ($propiedad->cantidad_dormitorios !== null && $propiedad->cantidad_dormitorios !== '') {
            $partes[] = (int) $propiedad->cantidad_dormitorios . ' DORM.';
        }

        $cochera = strtoupper(trim((string) $propiedad->cochera));
        $tieneCochera = in_array($cochera, ['SI', 'S', '1'], true);
        $partes[] = 'COCHERA: ' . ($tieneCochera ? 'SI' : 'NO');

        return implode(' - ', $partes);
    }

    /**
     * Resuelve el contrato y el inquilino de cada repactado cargado.
     *
     * historial_estado_repactado no guarda el inquilino, así que se reconstruye
     * desde el contrato que originó la carga: por el folio se buscan las casas de
     * la empresa en view_propiedades y, con las mismas fechas de inicio y de fecha
     * efectiva de cierre registradas al cargar, se identifica contratos_cabecera.
     * Con el contrato se traen la razón social y los teléfonos del inquilino, el
     * reconocimiento del detalle vigente y el monto del documento del repactado.
     *
     * @param int $empresa Empresa a la que pertenecen los folios.
     * @param \Illuminate\Support\Collection $historiales Repactados del período.
     * @param \Illuminate\Support\Collection $folios Folios por propiedad.
     * @param array $casasPorFolio Casas de cada folio en el sistema de contratos.
     * @return \Illuminate\Support\Collection Datos por id de historial.
     */
    private function obtenerDatosContrato(int $empresa, $historiales, $folios, array $casasPorFolio)
    {
        if (empty($casasPorFolio)) {
            return collect();
        }

        // La fecha de cierre se guarda calculada como la rescisión cuando es
        // anterior al vencimiento; se repite la misma fórmula del criterio de
        // carga para poder identificar el contrato por sus fechas.
        $contratos = DB::connection('mysql2')->table('contratos_cabecera')
            ->where('id_empresa', $empresa)
            ->whereIn('id_casa', array_merge([], ...array_values($casasPorFolio)))
            ->selectRaw("
                id_contrato_cabecera,
                id_casa,
                id_inquilino,
                comienza,
                IF(
                    rescicion <> '0000-00-00',
                    IF(rescicion <= finaliza, rescicion, finaliza),
                    finaliza
                ) AS fecha_cierre
            ")
            ->get();

        // Puede haber contratos duplicados con la misma casa y las mismas fechas.
        // Se agrupan para luego tomar de cualquiera de ellos el repactado y el
        // reconocimiento, que puede estar cargado en una sola de las copias.
        $contratosPorClave = [];

        foreach ($contratos as $contrato) {
            $clave = $contrato->id_casa . '|' . $contrato->comienza . '|' . $contrato->fecha_cierre;
            $contratosPorClave[$clave][] = $contrato;
        }

        $contratoPorHistorial = [];

        foreach ($historiales as $historial) {
            $folio = (int) $folios->get($historial->propiedad_id);

            foreach ($casasPorFolio[$folio] ?? [] as $idCasa) {
                $clave = $idCasa . '|' . $historial->inicio_contrato . '|' . $historial->vencimiento_contrato;

                if (isset($contratosPorClave[$clave])) {
                    $contratoPorHistorial[$historial->id] = $contratosPorClave[$clave];
                    break;
                }
            }
        }

        if (empty($contratoPorHistorial)) {
            return collect();
        }

        $idsInquilino = [];

        foreach ($contratoPorHistorial as $grupo) {
            $idsInquilino[] = (int) $grupo[0]->id_inquilino;
        }

        $personas = DB::connection('mysql2')->table('padron')
            ->whereIn('id_inquilino', array_unique($idsInquilino))
            ->get(['id_padron', 'id_inquilino', 'razon_social', 'telefonos'])
            ->keyBy(fn($persona) => (int) $persona->id_inquilino);

        // Los teléfonos se guardan en padron_telefonos, vinculados por id_padron.
        // El campo padron.telefonos suele estar vacío o con un guion.
        $telefonosPorPadron = [];

        if ($personas->isNotEmpty()) {
            foreach (
                DB::connection('mysql2')->table('padron_telefonos')
                    ->whereIn('id_padron', $personas->pluck('id_padron')->all())
                    ->get(['id_padron', 'telefono']) as $registroTelefono
            ) {
                $numero = trim((string) $registroTelefono->telefono);

                if ($numero !== '') {
                    $telefonosPorPadron[(int) $registroTelefono->id_padron][] = $numero;
                }
            }
        }

        $idsContrato = [];

        foreach ($contratoPorHistorial as $grupo) {
            foreach ($grupo as $contrato) {
                $idsContrato[] = (int) $contrato->id_contrato_cabecera;
            }
        }

        $idsContrato = array_values(array_unique($idsContrato));

        // El reconocimiento corresponde al detalle de mayor alquiler, que es el
        // mismo que usa la carga para guardar el monto viejo.
        $reconocimientoPorContrato = DB::connection('mysql2')->table('contratos_detalle')
            ->whereIn('id_contrato_cabecera', $idsContrato)
            ->orderBy('monto_alquiler')
            ->get(['id_contrato_cabecera', 'reconocimiento'])
            ->groupBy(fn($detalle) => (int) $detalle->id_contrato_cabecera)
            ->map(fn($detalles) => (float) $detalles->last()->reconocimiento);

        // El monto del documento se toma del último repactado del contrato en el
        // sistema de contratos.
        $documentoPorContrato = DB::connection('mysql2')->table('repactados')
            ->whereIn('id_contrato_cabecera', $idsContrato)
            ->orderBy('id_repactado')
            ->get(['id_contrato_cabecera', 'documentos'])
            ->groupBy(fn($repactado) => (int) $repactado->id_contrato_cabecera)
            ->map(fn($repactados) => (float) $repactados->last()->documentos);

        $resultado = [];

        foreach ($contratoPorHistorial as $idHistorial => $grupo) {
            $contrato = $grupo[0];
            $persona = $personas->get((int) $contrato->id_inquilino);
            $telefonos = $persona ? ($telefonosPorPadron[(int) $persona->id_padron] ?? []) : [];

            // Respaldo para registros viejos que todavía tengan el número en padron.
            if (empty($telefonos) && $persona) {
                $respaldo = trim((string) $persona->telefonos);

                if ($respaldo !== '' && $respaldo !== '-') {
                    $telefonos = [$respaldo];
                }
            }

            $idContrato = (int) $contrato->id_contrato_cabecera;
            $reconocimiento = 0.0;
            $montoDocumento = 0.0;

            foreach ($grupo as $integrante) {
                $idIntegrante = (int) $integrante->id_contrato_cabecera;

                if ($reconocimiento === 0.0 && $reconocimientoPorContrato->has($idIntegrante)) {
                    $reconocimiento = (float) $reconocimientoPorContrato->get($idIntegrante);
                }

                if ($documentoPorContrato->has($idIntegrante)) {
                    $montoDocumento = (float) $documentoPorContrato->get($idIntegrante);
                    $idContrato = $idIntegrante;
                }
            }

            $resultado[$idHistorial] = [
                'apellidoNombre' => $persona->razon_social ?? null,
                'telefono' => !empty($telefonos) ? implode(' / ', array_unique($telefonos)) : null,
                'idContrato' => $idContrato,
                'reconocimiento' => $reconocimiento,
                'montoDocumento' => $montoDocumento,
            ];
        }

        return collect($resultado);
    }
}
