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

                Historial_estado_repactado::create([
                    'monto_alquiler_viejo' => $r->monto_alquiler,
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
     * Lista los repactados ya cargados en un período y empresa, con folio opcional.
     *
     * Parte de historial_estado_repactado, que guarda un registro por propiedad y
     * período, y lo enriquece con los datos que muestra el listado: el estado del
     * catálogo, la dirección, localidad y tipo de la propiedad en el sistema de
     * contratos, el propietario activo con sus teléfonos y el inquilino del
     * contrato del que provino la carga.
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

        // view_propiedades aporta dirección, localidad y tipo tal como se listan
        // en el sistema de contratos. Un folio puede repetir filas por historial
        // de inquilinos, pero esos campos son iguales en todas las de la misma casa.
        $propiedadesLegado = [];
        $casasPorFolio = [];

        foreach (
            DB::connection('mysql2')->table('view_propiedades')
                ->where('id_empresa', $empresa)
                ->whereIn('carpeta', $folios->values()->all())
                ->get(['carpeta', 'id_casa', 'direccion', 'nombre_localidad', 'tipo_propiedad']) as $fila
        ) {

            $folio = (int) $fila->carpeta;
            $casasPorFolio[$folio][] = (int) $fila->id_casa;
            $propiedadesLegado[$folio] = $propiedadesLegado[$folio] ?? [
                'direccion' => $fila->direccion,
                'localidad' => $fila->nombre_localidad,
                'tipo' => $fila->tipo_propiedad,
            ];
        }

        $casasPorFolio = array_map(fn($casas) => array_unique($casas), $casasPorFolio);
        $inquilinos = $this->obtenerInquilinos($empresa, $historiales, $folios, $casasPorFolio);

        $propietarios = Propiedad::with(['propietarios.telefonos'])
            ->whereIn('id', $historiales->pluck('propiedad_id'))
            ->get()
            ->keyBy('id');

        return $historiales->map(function ($historial) use ($folios, $estados, $propiedadesLegado, $inquilinos, $propietarios) {
            $folio = $folios->get($historial->propiedad_id);
            $legado = $propiedadesLegado[(int) $folio] ?? [];
            $propiedad = $propietarios->get($historial->propiedad_id);

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

            $inquilino = $inquilinos->get($historial->id) ?? [];

            return [
                'id' => $historial->id,
                'folio' => $folio,
                'cuentaCorriente' => $historial->cuenta_corriente ?: null,
                'estado' => $estados[$historial->estado] ?? null,
                'cierre' => $historial->fecha_cierre ?: null,
                'condicion' => $historial->condicion ?: null,
                'situacion' => $historial->situacion ?: null,
                'apellidoNombre' => $inquilino['apellidoNombre'] ?? null,
                'telefono' => !empty($inquilino['telefono']) ? $inquilino['telefono'] : null,
                'direccion' => $legado['direccion'] ?? null,
                'localidad' => $legado['localidad'] ?? null,
                'tipo' => $legado['tipo'] ?? null,
                'propietario' => $propietario ? trim($propietario->nombre . ' ' . $propietario->apellido) : null,
                'telefonoPropietario' => $telefonoPropietario,
                'viejo' => $historial->monto_alquiler_viejo,
                'nuevo' => $historial->nuevo_valor_alquiler ?: null,
                'total' => $historial->total ?: null,
                'plazoActualizacion' => $historial->plazo_y_actualizacion_contrato ?: null,
                'inicioContrato' => $historial->inicio_contrato,
                'vencimientoContrato' => $historial->vencimiento_contrato,
            ];
        })->values()->all();
    }

    /**
     * Resuelve el inquilino de cada repactado cargado.
     *
     * historial_estado_repactado no guarda el inquilino, así que se reconstruye
     * desde el contrato que originó la carga: por el folio se buscan las casas de
     * la empresa en view_propiedades y, con las mismas fechas de inicio y de fecha
     * efectiva de cierre registradas al cargar, se identifica contratos_cabecera.
     * Recién entonces se traen los datos de padron en el sistema de contratos.
     *
     * @param int $empresa Empresa a la que pertenecen los folios.
     * @param \Illuminate\Support\Collection $historiales Repactados del período.
     * @param \Illuminate\Support\Collection $folios Folios por propiedad.
     * @param array $casasPorFolio Casas de cada folio en el sistema de contratos.
     * @return \Illuminate\Support\Collection Inquilino por id de historial.
     */
    private function obtenerInquilinos(int $empresa, $historiales, $folios, array $casasPorFolio)
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

        $inquilinoPorContrato = [];

        foreach ($contratos as $contrato) {
            $clave = $contrato->id_casa . '|' . $contrato->comienza . '|' . $contrato->fecha_cierre;
            $inquilinoPorContrato[$clave] = $contrato->id_inquilino;
        }

        $idsInquilino = [];

        foreach ($historiales as $historial) {
            $folio = (int) $folios->get($historial->propiedad_id);

            foreach ($casasPorFolio[$folio] ?? [] as $idCasa) {
                $clave = $idCasa . '|' . $historial->inicio_contrato . '|' . $historial->vencimiento_contrato;

                if (isset($inquilinoPorContrato[$clave])) {
                    $idsInquilino[$historial->id] = $inquilinoPorContrato[$clave];
                    break;
                }
            }
        }

        if (empty($idsInquilino)) {
            return collect();
        }

        $personas = DB::connection('mysql2')->table('padron')
            ->whereIn('id_inquilino', array_unique($idsInquilino))
            ->get(['id_inquilino', 'razon_social', 'telefonos'])
            ->keyBy(fn($persona) => (int) $persona->id_inquilino);

        $resultado = [];

        foreach ($idsInquilino as $idHistorial => $idInquilino) {
            $persona = $personas->get((int) $idInquilino);

            $resultado[$idHistorial] = [
                'apellidoNombre' => $persona->razon_social ?? null,
                'telefono' => $persona->telefonos ?? null,
            ];
        }

        return collect($resultado);
    }
}
