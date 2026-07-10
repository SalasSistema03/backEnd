<?php

namespace App\Services\impuesto\EXP;

use App\Models\impuesto\Exp_edificio;
use App\Models\impuesto\Exp_Unidades;
use App\Models\impuesto\Exp_unidades_sys;
use App\Models\impuesto\Exp_administrador_consorcio;
use App\Models\impuesto\Exp_broche;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ExpensasService
{
    public function getPadronUnidadesService(): array
    {
        $unidades = Exp_unidades_sys::all();

        // Usamos ->values() después de ordenar para resetear los índices numéricos,
        // garantizando que en el JSON se exporte como un Array y no como un Objeto.
        $edificios = Exp_edificio::all()->sortBy('nombre_consorcio')->values();

        $unidadesPadron = Exp_Unidades::all();
        $administradores = Exp_administrador_consorcio::all();

        // Agrupamos por id_casa directamente en la colección
        $unidadesPadronByCasa = $unidadesPadron->groupBy('id_casa');

        return [
            'unidades'                  => $unidades,
            'edificios'                 => $edificios,
            'unidades_padron_by_casa'   => $unidadesPadronByCasa,
            'administradores'           => $administradores,
        ];
    }

    public function filtrarUnidadesCompleto(string $search, array $filtros): array
    {
        // 1. Ejecución del servicio interno tal como estaba en tu monolito
        $unidadesServices = new UnidadesServices();
        $unidadesServices->PadronUnidadesSyS();

        // 2. Inicialización de la Query dinámica
        $query = Exp_unidades_sys::query();

        // Filtros de Estado
        $activos = in_array('ACTIVO', $filtros, true);
        $inactivos = in_array('INACTIVO', $filtros, true);

        if ($activos && !$inactivos) {
            $query->where('estado', '=', 'Activo');
        } elseif (!$activos && $inactivos) {
            $query->where('estado', '=', 'Inactivo');
        }

        // Filtros de Administración
        $adminFilters = array_values(array_intersect($filtros, ['L', 'P', 'I']));
        if (!empty($adminFilters)) {
            $query->whereIn('administra', $adminFilters);
        }

        // Motor de búsqueda por texto (Búsqueda global)
        if ($search !== '') {
            $needle = "%{$search}%";
            $query->where(function ($q) use ($needle) {
                $q->where('folio', 'like', $needle)
                    ->orWhere('casa', 'like', $needle)
                    ->orWhere('ubicacion', 'like', $needle)
                    ->orWhere('comision', 'like', $needle)
                    ->orWhere('administra', 'like', $needle)
                    ->orWhere('estado', 'like', $needle);
            });
        }

        // Obtención de las unidades filtradas y ordenadas
        $unidades = $query->orderBy('folio')->get();

        // 3. Carga de datos contextuales necesarios para el Frontend
        $edificios = Exp_edificio::all();
        $administradores = Exp_administrador_consorcio::all();

        // keyBy('id') transforma la colección en un diccionario/objeto asociativo por ID
        $adminsById = $administradores->keyBy('id');

        $unidadesPadron = Exp_Unidades::all();
        $unidadesPadronByCasa = $unidadesPadron->groupBy('id_casa');

        return [
            'unidades'                  => $unidades,
            'edificios'                 => $edificios,
            'administradores'           => $administradores,
            'admins_by_id'              => $adminsById,
            'unidades_padron_by_casa'   => $unidadesPadronByCasa,
        ];
    }


    /**
     * Procesa y guarda la carga masiva de unidades de forma transaccional.
     *
     */
    public function completarCargaUnidadesService(array $data): void
    {
        $repetir   = $data['repetir'];
        $idCasa    = $data['id'];
        $edificio  = $data['edificio'];
        $estadoSys = $data['estado'];

        DB::beginTransaction();

        try {
            foreach ($repetir as $rep) {
                $payload = [
                    'id_casa'       => $idCasa,
                    'id_edificio'   => $edificio,
                    'piso'          => $rep['piso'] ?? null,
                    'depto'         => $rep['depto'] ?? null,
                    'unidad'        => $rep['unidad'] ?? null,
                    'tipo'          => $rep['tipo'] ?? null,
                    'observaciones' => $rep['comentario'] ?? null, // Mapeo de 'comentario' a 'observaciones'
                    'estado'        => $rep['estado'] ?? null
                ];

                // Si viene un ID válido, actualiza; de lo contrario, crea un nuevo registro
                if (isset($rep['id']) && $rep['id']) {
                    Exp_unidades::where('id', $rep['id'])->update($payload);
                } else {
                    Exp_unidades::create($payload);
                }

                // Actualizar el estado en las unidades del sistema principal
                Exp_unidades_sys::where('casa', $idCasa)->update([
                    'estado' => $estadoSys
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Re-lanzamos la excepción para que el controlador se entere del fallo
            throw $e;
        }
    }

    public function eliminarUnidadService(int $id)
    {
        Exp_unidades::where('id', $id)->delete();
    }



    /* BROCHES */
    public function getBrochesService(?int $mes = null, ?int $anio = null): array
    {
        // Si no nos pasan mes/año desde Vue, usamos el mes y año actual por defecto
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $edificios = Exp_edificio::all();
        $empresas = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();

        // Mantenemos tu consulta a mysql9 intacta, pero usando las variables dinámicas
        $broches = DB::connection('mysql9')
            ->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_broche.*',
                'exp_broche.id as id_broche',
                'exp_broche.vencimiento as vencimientobroche', // <-- ¡EL SALVAVIDAS!
                'exp_unidades.*',
                'exp_unidades.id as id_unidad',
                'exp_administrador_consorcio.*',
                'exp_administrador_consorcio.id as id_administra',
                'exp_edificios.*',
                'exp_edificios.id as id_edificio',
                'exp_unidades_sys.*',
                'exp_unidades_sys.id as id_unidades_sys'
            )
            ->whereMonth('exp_broche.vencimiento', $mes)
            ->whereYear('exp_broche.vencimiento', $anio)
            ->get();

        // Retornamos todo empaquetado para el controlador
        return [
            'edificios' => $edificios,
            'empresas'  => $empresas,
            'broches'   => $broches,
            'periodo_actual' => [
                'mes' => $mes,
                'anio' => $anio
            ]
        ];
    }


    public function buscarUnidadesParaBroche(array $filtros)
    {
        $query = Exp_unidades_sys::join('exp_unidades', 'exp_unidades_sys.casa', '=', 'exp_unidades.id_casa')
            ->join('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->join('exp_administrador_consorcio', 'exp_edificios.id_administrador_consorcio', '=', 'exp_administrador_consorcio.id')
            ->select(
                // ¡OJO ACÁ! Como te mencioné antes, usar .* con joins es peligroso.
                // Trata de pedir solo lo que necesitas, pero por ahora lo mantenemos.
                'exp_unidades_sys.*',
                'exp_unidades.*',
                'exp_unidades.id as id_unidad',
                'exp_edificios.*',
                'exp_edificios.direccion as direccion_edificio',
                'exp_edificios.altura as altura_edificio',
                'exp_administrador_consorcio.*',
                'exp_administrador_consorcio.id as id_administrador'
            );

        $query->when($filtros['empresa'] ?? null, function ($q, $empresa) {
            $q->where('exp_unidades_sys.id_empresa', $empresa);
        });

        $query->when($filtros['folio'] ?? null, function ($q, $folio) {
            $q->where('exp_unidades_sys.folio', $folio);
        });

        $query->when($filtros['edificio'] ?? null, function ($q, $edificio) {
            $q->where('exp_edificios.id', $edificio);
        });

        $query->when($filtros['administrador'] ?? null, function ($q, $administrador) {
            $q->where('exp_administrador_consorcio.id', $administrador);
        });

        return $query->get();
    }


    public function guardarBrocheExpensa(array $data)
    {
        // Extraemos el año y mes usando Carbon (es más limpio que explode)
        $fechaVencimiento = Carbon::parse($data['vencimiento']);
        $anioNuevo = $fechaVencimiento->year;
        $mesNuevo = $fechaVencimiento->month;

        // CONSULTA OPTIMIZADA: Buscamos directamente en la Base de Datos
        $existeDuplicado = Exp_broche::where('unidad', $data['id_unidad'])
            ->whereYear('vencimiento', $anioNuevo)
            ->whereMonth('vencimiento', $mesNuevo)
            ->exists();

        if ($existeDuplicado) {
            // Lanzamos una excepción con código 409 (Conflicto - Duplicado)
            // (404 es para "No encontrado", 409 es el correcto para datos duplicados)
            throw new Exception('Esta unidad ya se encuentra cargada para este mes y año.', 409); // 409 Conflicto - Duplicado
        }

        // Si no existe, lo creamos
        return Exp_broche::create([
            'vencimiento'    => $data['vencimiento'],
            'extraordinaria' => $data['importe_extraordinaria'],
            'ordinaria'      => $data['importe_ordinaria'],
            'total'          => $data['total'],
            'periodo'        => $data['periodo'],
            'anio'           => $data['anio'],
            'unidad'         => $data['id_unidad'],
            'administra'     => $data['id_administrador'],
        ]);
    }


    public function eliminarBroche(int $id)
    {
        // Buscamos explícitamente en la conexión mysql9
        $broche = DB::connection('mysql9')->table('exp_broche')->where('id', $id)->first();

        if (!$broche) {
            throw new Exception('El broche no existe o ya fue eliminado.', 404);
        }

        // Si existe, lo eliminamos
        return DB::connection('mysql9')->table('exp_broche')->where('id', $id)->delete();
    }










    public function obtenerDatosBrochePdf($mes, $anio, $administrador = null)
    {
        return DB::connection('mysql9')->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_unidades_sys.folio',
                'exp_broche.*',
                'exp_administrador_consorcio.*',
                'exp_edificios.*',
                'exp_edificios.id as id_edificio',
                'exp_edificios.direccion as direccion_edificio',
                'exp_edificios.altura as altura_edificio',
                'exp_administrador_consorcio.direccion as direccion_administra',
                'exp_administrador_consorcio.altura as altura_administra',
                'exp_unidades.piso',
                'exp_unidades.depto',
                'exp_unidades.observaciones'
            )
            ->whereMonth('exp_broche.vencimiento', $mes)
            ->whereYear('exp_broche.vencimiento', $anio)
            // Usamos when() igual que hicimos en el buscador para mantenerlo limpio
            ->when($administrador, function ($query) use ($administrador) {
                return $query->where('exp_administrador_consorcio.id', $administrador);
            })
            ->orderBy('exp_administrador_consorcio.id')
            ->orderBy('exp_edificios.id')
            ->orderBy('exp_unidades_sys.folio')
            ->get();
    }



    public function actualizarBroche($id, array $datos)
    {
        // 1. Verificamos que el registro exista antes de tocarlo
        $broche = DB::connection('mysql9')->table('exp_broche')->where('id', $id)->first();

        if (!$broche) {
            throw new \Exception("El broche con ID {$id} no fue encontrado.");
        }

        // 2. Hacemos el UPDATE mapeando los datos de Vue a las columnas de SQL
        DB::connection('mysql9')->table('exp_broche')
            ->where('id', $id)
            ->update([
                'vencimiento' => $datos['vencimiento'],
                'periodo'     => $datos['periodo'],
                'anio'        => $datos['anio'],
                // Vue nos mandó esto como 'importe_...', lo guardamos en la columna real de tu DB
                'extraordinaria' => $datos['importe_extraordinaria'],
                'ordinaria'      => $datos['importe_ordinaria'],
                'total'          => $datos['total']
            ]);

        return true;
    }
}
