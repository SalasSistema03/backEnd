<?php

namespace App\Services\proceso;


use App\Models\usuarios_y_permisos\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tasacion;
use App\Models\proceso\Historial_estado_repactado;
use App\Models\proceso\Estado_repactado;
use App\Models\proceso\Proceso_propiedad;
use App\Models\proceso\Historial_tasaciones_repactado;
use App\Services\At_cl\TasacionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     * ese momento. También descarta un contrato si existe otro posterior para la
     * misma casa y empresa. Cada contrato se vincula con su propiedad a través del
     * folio y la empresa, y se registra en historial_estado_repactado el alquiler
     * vigente, las fechas del contrato y el usuario que realizó la carga.
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
        // La condición documentos = 'N' toma el mismo tipo de contrato que se usa
        // en PropiedadService::obtenerAlquiler; más abajo se descartan los que ya
        // tienen un contrato posterior para la misma casa y empresa.
        $resultados = DB::connection('mysql2')->select(
            "
            SELECT DISTINCT cc.id_contrato_cabecera, vp.carpeta AS folio, cc.comienza, cc.finaliza AS finaliza, cc.id_inquilino,
                            cc.id_casa, vp.direccion,
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

    'VTO. CONTRATO' AS motivo

FROM contratos_cabecera AS cc, contratos_detalle AS cd, view_propiedades AS vp

WHERE
    cc.id_contrato_cabecera = cd.id_contrato_cabecera
    AND cc.id_casa = vp.id_casa
    AND LEFT(cc.finaliza, 7) = ?
    AND (
        cc.rescicion = '0000-00-00'
        OR cc.rescicion >= cc.finaliza
    )
    AND cc.id_empresa = ?
    AND cc.documentos = 'N'

GROUP BY
    cc.id_contrato_cabecera,
    cc.id_casa
            ",
            [$periodo, $empresa]
        );
        //Log::info($resultados);


        if (empty($resultados)) {
            return [
                'success' => true,
                'message' => 'No se encontraron repactados para el período seleccionado.',
                'data' => [],
            ];
        }

        $casas = array_values(array_unique(array_map(
            fn($resultado) => (int) $resultado->id_casa,
            $resultados
        )));
        $ultimoContratoPorCasa = DB::connection('mysql2')->table('contratos_cabecera')
            ->where('id_empresa', $empresa)
            ->whereIn('id_casa', $casas)
            ->selectRaw('id_casa, MAX(id_contrato_cabecera) AS ultimo_id')
            ->groupBy('id_casa')
            ->pluck('ultimo_id', 'id_casa')
            ->all();

        $resultados = array_values(array_filter(
            $resultados,
            fn($resultado) => $this->esContratoMasRecientePorCasa(
                (int) $resultado->id_casa,
                (int) $resultado->id_contrato_cabecera,
                $ultimoContratoPorCasa
            )
        ));

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
     * Lista los repactados usando uno o más filtros opcionales.
     *
     * Parte de historial_estado_repactado, excluye los historiales de contratos
     * reemplazados y toma la versión de mayor ID válida de cada propiedad para el
     * período seleccionado. Luego enriquece las filas con el estado, los datos de
     * la propiedad y el inquilino del contrato actual.
     *
     * @param Request $request Filtros opcionales de período, empresa, folio, estado, dirección y usuario.
     * @return array Repartición de filas para el listado, en formato camelCase.
     */
    public function getRepactadoFiltrado(Request $request)
    {
        $datos = $request->validate([
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'digits:4'],
            'empresa' => ['nullable', 'integer', 'in:1,2,3'],
            'folio' => ['nullable', 'integer', 'between:0,100000'],
            'estado' => ['nullable', 'integer', 'min:1'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'usuario' => ['nullable', 'string', 'max:100'],
        ]);

        $mes = isset($datos['mes']) ? (int) $datos['mes'] : null;
        $anio = isset($datos['anio']) ? (int) $datos['anio'] : null;
        $empresa = isset($datos['empresa']) ? (int) $datos['empresa'] : null;
        $folioBuscado = isset($datos['folio']) ? (int) $datos['folio'] : null;
        $estadoBuscado = isset($datos['estado']) ? (int) $datos['estado'] : null;
        $direccionBuscada = trim((string) ($datos['direccion'] ?? ''));
        $usuarioBuscado = trim((string) ($datos['usuario'] ?? ''));

        $hayFiltros = collect([
            $mes,
            $anio,
            $empresa,
            $folioBuscado,
            $estadoBuscado,
            $direccionBuscada,
            $usuarioBuscado,
        ])->contains(fn($filtro) => $filtro !== null && $filtro !== '');

        if (!$hayFiltros) {
            throw ValidationException::withMessages([
                'filtros' => ['Ingresá al menos un filtro para buscar.'],
            ]);
        }

        // Los folios de la empresa se guardan indexados por propiedad, que es la
        // clave con la que historial_estado_repactado relaciona cada repactado.
        $empresaPropiedadesQuery = Empresas_propiedades::query()->orderBy('id');

        if ($empresa !== null) {
            $empresaPropiedadesQuery->where('empresa_id', $empresa);
        }

        if ($folioBuscado !== null) {
            $empresaPropiedadesQuery->where('folio', $folioBuscado);
        }

        $empresaPropiedades = $empresaPropiedadesQuery
            ->get(['id', 'folio', 'propiedad_id', 'empresa_id']);
        $empresaPropiedadesPorPropiedad = $empresaPropiedades->unique('propiedad_id');
        $folios = $empresaPropiedadesPorPropiedad->pluck('folio', 'propiedad_id');
        $empresasPorPropiedad = $empresaPropiedadesPorPropiedad->pluck('empresa_id', 'propiedad_id');
        $idsEmpresaPropiedad = $empresaPropiedades->mapWithKeys(
            fn($empresaPropiedad) => [
                $empresaPropiedad->propiedad_id . '|' . $empresaPropiedad->empresa_id => $empresaPropiedad->id,
            ]
        );
        $paresEmpresaFolio = $empresaPropiedades->mapWithKeys(
            fn($empresaPropiedad) => [
                $empresaPropiedad->folio . '|' . $empresaPropiedad->empresa_id => true,
            ]
        );

        if ($folios->isEmpty()) {
            return [];
        }

        $historialesQuery = Historial_estado_repactado::query()
            ->whereIn('propiedad_id', $folios->keys()->all());

        if ($anio !== null && $mes !== null) {
            $historialesQuery->where('periodo_cargado', sprintf('%04d-%02d', $anio, $mes));
        } elseif ($anio !== null) {
            $historialesQuery->where('periodo_cargado', 'like', sprintf('%04d-%%', $anio));
        } elseif ($mes !== null) {
            $historialesQuery->where('periodo_cargado', 'like', '%-' . sprintf('%02d', $mes));
        }

        $historiales = $historialesQuery->orderByDesc('id')->get();

        if ($historiales->isEmpty()) {
            return [];
        }

        $estados = Estado_repactado::pluck('estado', 'id');

        // El sistema de contratos solo aporta la casa de cada folio (carpeta),
        // necesaria para reconstruir el inquilino del contrato. La dirección, la
        // localidad y el tipo salen de sistema_atcl, igual que el propietario.
        $casasPorFolio = [];

        $casasQuery = DB::connection('mysql2')->table('view_propiedades')
            ->whereIn('carpeta', $folios->values()->all())
            ->whereIn('id_empresa', $empresaPropiedades->pluck('empresa_id')->unique()->values()->all());

        foreach ($casasQuery->get(['carpeta', 'id_casa', 'id_empresa']) as $fila) {
            $folio = (int) $fila->carpeta;
            $idEmpresa = (int) $fila->id_empresa;

            if (!$paresEmpresaFolio->has($folio . '|' . $idEmpresa)) {
                continue;
            }

            $casasPorFolio[$folio][] = [
                'id_casa' => (int) $fila->id_casa,
                'id_empresa' => $idEmpresa,
            ];
        }

        $casasPorFolio = array_map(
            fn($casas) => collect($casas)
                ->unique(fn($casa) => $casa['id_empresa'] . '|' . $casa['id_casa'])
                ->values()
                ->all(),
            $casasPorFolio
        );
        $contratosHistorial = $this->obtenerDatosContrato($historiales, $folios, $casasPorFolio);
        $idsHistorialesReemplazados = $contratosHistorial['historialesReemplazados'];

        if ($idsHistorialesReemplazados !== []) {
            $historiales = $historiales->reject(
                fn($historial) => in_array((int) $historial->id, $idsHistorialesReemplazados, true)
            );
        }

        $historiales = $historiales
            ->groupBy('propiedad_id')
            ->map(fn($versiones) => $versiones->first())
            ->values();

        if ($estadoBuscado !== null) {
            $historiales = $historiales
                ->filter(fn($historial) => (int) $historial->estado === $estadoBuscado)
                ->values();
        }

        if ($historiales->isEmpty()) {
            return [];
        }

        $datosContrato = $contratosHistorial['datos'];

        // La propiedad y sus datos de dominio (calle, localidad, tipo y propietario
        // con teléfonos) pertenecen a sistema_atcl.
        $propiedades = Propiedad::with(['propietarios.telefonos', 'calle', 'localidad', 'tipoInmueble'])
            ->whereIn('id', $historiales->pluck('propiedad_id'))
            ->get()
            ->keyBy('id');

        $columnasTasacion = [
            'id',
            'propiedad_id',
            'tasacion_pesos_venta',
            'tasacion_dolar_venta',
            'tasacion_pesos_alquiler',
            'fecha_tasacion',
        ];

        if (Schema::connection('mysql')->hasColumn('tasacion', 'id_usuario')) {
            $columnasTasacion[] = 'id_usuario';
        }

        $tasaciones = Tasacion::query()
            ->whereIn('propiedad_id', $historiales->pluck('propiedad_id')->unique()->all())
            ->orderByDesc('id')
            ->get($columnasTasacion)
            ->unique('propiedad_id')
            ->keyBy('propiedad_id');

        $idsUsuariosTasacion = $tasaciones->pluck('id_usuario')->filter()->unique()->values();
        $usuariosTasacion = $idsUsuariosTasacion->isEmpty()
            ? collect()
            : Usuario::query()->whereIn('id', $idsUsuariosTasacion)->get(['id', 'name', 'username'])->keyBy('id');

        $filas = $historiales->map(function ($historial) use (
            $folios,
            $idsEmpresaPropiedad,
            $empresasPorPropiedad,
            $estados,
            $datosContrato,
            $propiedades,
            $tasaciones,
            $usuariosTasacion
        ) {
            $folio = $folios->get($historial->propiedad_id);
            $propiedad = $propiedades->get($historial->propiedad_id);
            $tasacion = $tasaciones->get($historial->propiedad_id);
            $datos = $datosContrato->get($historial->id) ?? [];
            $idEmpresa = $datos['idEmpresa'] ?? $empresasPorPropiedad->get($historial->propiedad_id);
            $idEmpresaPropiedad = $idsEmpresaPropiedad->get(
                $historial->propiedad_id . '|' . $idEmpresa
            );
            $usuarioTasacion = $tasacion
                ? $usuariosTasacion->get($tasacion->id_usuario)
                : null;

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
                'propiedadId' => (int) $historial->propiedad_id,
                'empresaPropiedadId' => $idEmpresaPropiedad !== null ? (int) $idEmpresaPropiedad : null,
                'folio' => $folio,
                'cuentaCorriente' => $historial->cuenta_corriente ?: null,
                'estado' => $estados[$historial->estado] ?? null,
                'estadoId' => $historial->estado !== null ? (int) $historial->estado : null,
                'cierre' => $historial->fecha_cierre ?: null,
                'condicion' => $historial->condicion ?: null,
                'situacion' => $historial->situacion ?: null,
                'observaciones' => $historial->observaciones_estado ?: null,
                'comentario' => $historial->comentario_inq ?: null,
                'comentarioProp' => $historial->comentario_prop ?: null,
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
                'tasacionPesosVenta' => $tasacion?->tasacion_pesos_venta,
                'tasacionDolarVenta' => $tasacion?->tasacion_dolar_venta,
                'tasacionAlquiler' => $tasacion?->tasacion_pesos_alquiler,
                'fechaTasacion' => $tasacion?->fecha_tasacion,
                'usuarioTasacionId' => $tasacion?->id_usuario,
                'usuarioTasacion' => $usuarioTasacion?->name ?: $usuarioTasacion?->username,
                'usuarioTasacionUsername' => $usuarioTasacion?->username,
                'total' => $total,
                'plazoActualizacion' => $historial->plazo_y_actualizacion_contrato ?: null,
                'inicioContrato' => $historial->inicio_contrato,
                'vencimientoContrato' => $historial->vencimiento_contrato,
            ];
        });

        if ($direccionBuscada !== '') {
            $filas = $filas->filter(
                fn($fila) => stripos((string) ($fila['direccion'] ?? ''), $direccionBuscada) !== false
            );
        }

        if ($usuarioBuscado !== '') {
            $filas = $filas->filter(
                fn($fila) => stripos((string) ($fila['usuarioTasacion'] ?? ''), $usuarioBuscado) !== false
                    || stripos((string) ($fila['usuarioTasacionUsername'] ?? ''), $usuarioBuscado) !== false
            );
        }

        return $filas->values()->all();
    }

    /**
     * Registra una nueva tasación de propiedad y la asocia al historial de repactado.
     *
     * Los importes se guardan en la tabla `tasacion` de sistema_atcl. La tabla
     * `historial_tasaciones_repactado` vincula la tasación de la propiedad con el
     * registro de repactado y la empresa seleccionada.
     *
     * @param Request $request IDs de la relación y montos opcionales de venta/alquiler.
     * @return array Resultado con la tasación y su asociación al repactado.
     */
    public function guardarTasacionRepactado(Request $request): array
    {
        $datos = $request->validate([
            'id_empresa_propiedad' => ['required', 'integer', 'min:1'],
            'id_historial_estado_repactado' => ['required', 'integer', 'min:1'],
            'tasacion_venta' => ['nullable', 'integer', 'min:1'],
            'moneda_venta' => ['nullable', 'required_with:tasacion_venta', 'in:1,2'],
            'tasacion_alquiler' => ['nullable', 'integer', 'min:1'],
            'fecha_tasacion' => ['required', 'date'],
            'comentario_tasacion' => ['nullable', 'string'],
        ]);

        if (empty($datos['tasacion_venta']) && empty($datos['tasacion_alquiler'])) {
            throw ValidationException::withMessages([
                'tasacion_venta' => ['Ingrese un valor de venta o de alquiler.'],
            ]);
        }

        return DB::connection('mysql10')->transaction(function () use ($datos) {
            $empresaPropiedad = Empresas_propiedades::find($datos['id_empresa_propiedad']);
            $historial = Historial_estado_repactado::find($datos['id_historial_estado_repactado']);

            if (!$empresaPropiedad || !$historial) {
                throw new \RuntimeException('No se encontró la propiedad o el repactado seleccionado.');
            }

            if ((int) $empresaPropiedad->propiedad_id !== (int) $historial->propiedad_id) {
                throw new \RuntimeException('La empresa y el historial de repactado no corresponden a la misma propiedad.');
            }

            $tasacionActual = Tasacion::where('propiedad_id', $empresaPropiedad->propiedad_id)
                ->orderByDesc('id')
                ->first();

            $venta = [
                'fecha_tasacion_venta' => $datos['fecha_tasacion'],
            ];

            if (isset($datos['tasacion_venta'])) {
                $venta['tasacion_venta'] = $datos['tasacion_venta'];
                $venta['moneda_venta'] = $datos['moneda_venta'];
            } elseif ($tasacionActual) {
                $venta['tasacion_venta'] = $tasacionActual->tasacion_dolar_venta
                    ?? $tasacionActual->tasacion_pesos_venta;
                $venta['moneda_venta'] = $tasacionActual->tasacion_dolar_venta !== null ? '2' : '1';
            }

            $alquiler = [];
            $tasacionAlquiler = $datos['tasacion_alquiler']
                ?? $tasacionActual?->tasacion_pesos_alquiler;

            if ($tasacionAlquiler !== null) {
                $alquiler['tasacion_alquiler'] = $tasacionAlquiler;
            }

            $asociacion = Historial_tasaciones_repactado::create([
                'id_empresa_propiedad' => $empresaPropiedad->id,
                'id_historial_estado_repactado' => $historial->id,
            ]);

            $tasacion = DB::connection('mysql')->transaction(function () use (
                $venta,
                $alquiler,
                $empresaPropiedad,
                $datos
            ) {
                $tasacion = (new TasacionService())->crearDesdeRequest(
                    $venta,
                    (int) $empresaPropiedad->propiedad_id,
                    $alquiler
                );

                if (!$tasacion) {
                    throw new \RuntimeException('No se pudo crear la tasación.');
                }

                $tasacion->comentario_tasacion = $datos['comentario_tasacion'] ?? null;
                $tasacion->save();

                return $tasacion;
            });

            return [
                'success' => true,
                'message' => 'Tasación de repactado guardada correctamente.',
                'data' => [
                    'tasacion' => $tasacion,
                    'historial' => $asociacion,
                ],
            ];
        });
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
     * Registra una nueva versión de los datos editables de un repactado.
     *
     * Los valores económico fijos (alquiler viejo, monto del documento y
     * reconocimiento) no se recalculan desde el front: se conservan los guardados
     * al cargar el repactado. El total se compone de viejo + documento - reconocimiento.
     * Al seleccionar cualquier estado, si todavía no hay fecha de cierre, se
     * completa con la fecha actual. El historial anterior se conserva y el proceso
     * de propiedad apunta al nuevo registro.
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
            'comentario_inq' => ['nullable', 'string'],
            'comentario_prop' => ['nullable', 'string'],
            'nuevo_valor_alquiler' => ['nullable', 'numeric', 'min:0'],
            'monto_documento' => ['nullable', 'numeric', 'min:0'],
            'reconocimiento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $historialActualizado = DB::connection('mysql10')->transaction(function () use ($datos) {
            $historial = Historial_estado_repactado::query()
                ->lockForUpdate()
                ->find($datos['id']);

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

            $nuevoHistorial = $historial->replicate();
            $nuevoHistorial->fill([
                'estado' => $estado->id,
                'cuenta_corriente' => $datos['cuenta_corriente'] ?? null,
                'plazo_y_actualizacion_contrato' => $datos['plazo_y_actualizacion_contrato'] ?? null,
                'condicion' => $datos['condicion'] ?? null,
                'situacion' => $datos['situacion'] ?? null,
                'observaciones_estado' => $datos['observaciones_estado'] ?? null,
                'comentario_inq' => $datos['comentario_inq'] ?? null,
                'comentario_prop' => $datos['comentario_prop'] ?? null,
                'nuevo_valor_alquiler' => $datos['nuevo_valor_alquiler'] ?? null,
                'monto_documento' => $montoDocumento,
                'reconocimiento' => $reconocimiento,
                'total' => $total,
                'fecha_cierre' => $fechaCierre,
                'quien_guardo' => auth('api')->id() ?? $historial->quien_guardo,
            ]);
            $nuevoHistorial->save();

            Proceso_propiedad::where('id_historial_estado_repactado', $historial->id)
                ->update(['id_historial_estado_repactado' => $nuevoHistorial->id]);

            return $nuevoHistorial;
        });

        return [
            'success' => true,
            'message' => 'Repactado actualizado correctamente.',
            'data' => $historialActualizado,
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
     * Solo se conservan los contratos de mayor ID por casa; los historiales que
     * coinciden únicamente con un contrato anterior se marcan como reemplazados.
     * Con el contrato actual se traen los datos del inquilino y los montos asociados.
     *
     * @param \Illuminate\Support\Collection $historiales Repactados del período.
     * @param \Illuminate\Support\Collection $folios Folios por propiedad.
     * @param array $casasPorFolio Casas y empresas de cada folio en contratos.
     * @return array Datos de contrato por historial e IDs de historiales reemplazados.
     */
    private function obtenerDatosContrato($historiales, $folios, array $casasPorFolio)
    {
        if (empty($casasPorFolio)) {
            return [
                'datos' => collect(),
                'historialesReemplazados' => [],
            ];
        }

        $idsCasa = [];
        $idsEmpresa = [];

        foreach ($casasPorFolio as $casas) {
            foreach ($casas as $casa) {
                $idsCasa[] = (int) $casa['id_casa'];
                $idsEmpresa[] = (int) $casa['id_empresa'];
            }
        }

        $idsCasa = array_values(array_unique($idsCasa));
        $idsEmpresa = array_values(array_unique($idsEmpresa));

        if ($idsCasa === [] || $idsEmpresa === []) {
            return [
                'datos' => collect(),
                'historialesReemplazados' => [],
            ];
        }

        // La fecha de cierre se guarda calculada como la rescisión cuando es
        // anterior al vencimiento; se repite la misma fórmula del criterio de
        // carga para poder identificar el contrato por sus fechas.
        $contratos = DB::connection('mysql2')->table('contratos_cabecera')
            ->whereIn('id_empresa', $idsEmpresa)
            ->whereIn('id_casa', $idsCasa)
            ->selectRaw("
                id_contrato_cabecera,
                id_empresa,
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
        $ultimoContratoPorCasa = [];

        foreach ($contratos as $contrato) {
            $idCasa = (int) $contrato->id_casa;
            $idEmpresa = (int) $contrato->id_empresa;
            $idContrato = (int) $contrato->id_contrato_cabecera;
            $clave = $idEmpresa . '|' . $idCasa . '|' . $contrato->comienza . '|' . $contrato->fecha_cierre;
            $contratosPorClave[$clave][] = $contrato;

            if (
                !isset($ultimoContratoPorCasa[$idEmpresa . '|' . $idCasa])
                || $idContrato > $ultimoContratoPorCasa[$idEmpresa . '|' . $idCasa]
            ) {
                $ultimoContratoPorCasa[$idEmpresa . '|' . $idCasa] = $idContrato;
            }
        }

        $contratoPorHistorial = [];
        $historialesReemplazados = [];

        foreach ($historiales as $historial) {
            $folio = (int) $folios->get($historial->propiedad_id);
            $historialReemplazado = false;

            foreach ($casasPorFolio[$folio] ?? [] as $casa) {
                $idCasa = (int) $casa['id_casa'];
                $idEmpresa = (int) $casa['id_empresa'];
                $clave = $idEmpresa . '|' . $idCasa . '|' . $historial->inicio_contrato . '|' . $historial->vencimiento_contrato;

                if (isset($contratosPorClave[$clave])) {
                    $contratosVigentes = array_values(array_filter(
                        $contratosPorClave[$clave],
                        fn($contrato) => $this->esContratoMasRecientePorCasa(
                            (int) $idCasa,
                            (int) $contrato->id_contrato_cabecera,
                            $ultimoContratoPorCasa,
                            $idEmpresa
                        )
                    ));

                    if ($contratosVigentes !== []) {
                        $contratoPorHistorial[$historial->id] = $contratosVigentes;
                        break;
                    }

                    $historialReemplazado = true;
                }
            }

            if ($historialReemplazado && !isset($contratoPorHistorial[$historial->id])) {
                $historialesReemplazados[] = (int) $historial->id;
            }
        }

        if (empty($contratoPorHistorial)) {
            return [
                'datos' => collect(),
                'historialesReemplazados' => $historialesReemplazados,
            ];
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
                'idEmpresa' => (int) $contrato->id_empresa,
                'apellidoNombre' => $persona->razon_social ?? null,
                'telefono' => !empty($telefonos) ? implode(' / ', array_unique($telefonos)) : null,
                'idContrato' => $idContrato,
                'reconocimiento' => $reconocimiento,
                'montoDocumento' => $montoDocumento,
            ];
        }

        return [
            'datos' => collect($resultado),
            'historialesReemplazados' => $historialesReemplazados,
        ];
    }

    /**
     * Indica si el contrato asociado a una casa es el de mayor ID de su empresa.
     *
     * @param int $idCasa Identificador de casa del sistema de contratos.
     * @param int $idContrato Identificador del contrato evaluado.
     * @param array $ultimoContratoPorCasa IDs recientes indexados por casa o empresa y casa.
     * @param int|null $idEmpresa Empresa del contrato, cuando la clave incluye empresa.
     * @return bool
     */
    private function esContratoMasRecientePorCasa(
        int $idCasa,
        int $idContrato,
        array $ultimoContratoPorCasa,
        ?int $idEmpresa = null
    ): bool {
        $clave = $idEmpresa !== null ? $idEmpresa . '|' . $idCasa : $idCasa;

        return isset($ultimoContratoPorCasa[$clave])
            && (int) $ultimoContratoPorCasa[$clave] === $idContrato;
    }
}
