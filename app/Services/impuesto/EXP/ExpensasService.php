<?php

namespace App\Services\impuesto\EXP;

use App\Models\impuesto\Bancos;
use App\Models\impuesto\Exp_edificio;
use App\Models\impuesto\Exp_Unidades;
use App\Models\impuesto\Exp_unidades_sys;
use App\Models\impuesto\Exp_administrador_consorcio;
use App\Models\impuesto\Exp_broche;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ExpensasService
{
    public function getPadronUnidadesService(): array
    {
        $unidades = Exp_unidades_sys::all();

        // Normaliza los índices después del ordenamiento para serializar la colección como un arreglo JSON.
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
        // Construye la consulta base para aplicar los filtros de forma condicional.
        $query = Exp_unidades_sys::query();

        // Aplica el estado solo cuando se selecciona una de las opciones de forma exclusiva.
        $activos = in_array('ACTIVO', $filtros, true);
        $inactivos = in_array('INACTIVO', $filtros, true);

        if ($activos && !$inactivos) {
            $query->where('estado', '=', 'Activo');
        } elseif (!$activos && $inactivos) {
            $query->where('estado', '=', 'Inactivo');
        }

        // Limita la consulta a los códigos de administración seleccionados.
        $adminFilters = array_values(array_intersect($filtros, ['L', 'P', 'I']));
        if (!empty($adminFilters)) {
            $query->whereIn('administra', $adminFilters);
        }

        // Busca el texto en los campos que identifican una unidad y su estado.
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

        // Ejecuta la consulta después de aplicar los filtros y ordena por folio.
        $unidades = $query->orderBy('folio')->get();

        // Carga los catálogos que el frontend necesita para mostrar filtros y relaciones.
        $edificios = Exp_edificio::orderBy('nombre_consorcio', 'ASC')->get();
        $administradores = Exp_administrador_consorcio::all();

        // Indexa los administradores por ID para facilitar su búsqueda en la respuesta.
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
     * Guarda las unidades del padrón y actualiza su estado en una única transacción.
     *
     * @param array $data Datos de la carga masiva, incluyendo casa, edificio, estado y unidades.
     * @return void
     * @throws \Throwable Si alguna operación falla y la transacción debe revertirse.
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
                    'observaciones' => $rep['comentario'] ?? null,
                    'estado'        => $rep['estado'] ?? null
                ];

                // Reutiliza el registro existente cuando llega su ID; si no, crea una nueva unidad.
                if (isset($rep['id']) && $rep['id']) {
                    Exp_unidades::where('id', $rep['id'])->update($payload);
                } else {
                    Exp_unidades::create($payload);
                }
            }
            // Sincroniza el estado de las unidades del sistema principal para la misma casa.
            Exp_unidades_sys::where('casa', $idCasa)->update([
                'estado' => $estadoSys
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Propaga el error para que el controlador informe el fallo al cliente.
            throw $e;
        }
    }

    public function eliminarUnidadService(int $id)
    {
        Exp_unidades::where('id', $id)->delete();
    }



    // Operaciones de consulta, creación, actualización y eliminación de broches.
    public function getBrochesService(?int $mes = null, ?int $anio = null): array
    {
        // Usa el período actual cuando la consulta no recibe mes o año.
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $edificios = Exp_edificio::all();
        $empresas = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();

        // Consulta la conexión mysql9 con el período recibido o con el período actual.
        $broches = DB::connection('mysql9')
            ->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_broche.*',
                'exp_broche.id as id_broche',
                'exp_broche.vencimiento as vencimientobroche',
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

        // Devuelve los catálogos, broches y período para que el controlador arme la respuesta.
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
                'exp_unidades_sys.id as id_unidades_sys',
                'exp_unidades_sys.folio',
                'exp_unidades.id as id_unidad',
                'exp_unidades.unidad',
                'exp_unidades.piso',
                'exp_unidades.depto',
                'exp_edificios.id as id_edificio',
                'exp_edificios.direccion as direccion_edificio',
                'exp_edificios.altura as altura_edificio',
                'exp_edificios.nombre_consorcio',
                'exp_administrador_consorcio.id as id_administrador',
                'exp_administrador_consorcio.nombre'
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
        // Carbon interpreta la fecha y expone sus componentes sin depender de separadores textuales.
        $fechaVencimiento = Carbon::parse($data['vencimiento']);
        $anioNuevo = $fechaVencimiento->year;
        $mesNuevo = $fechaVencimiento->month;

        // Comprueba la existencia de un broche para la misma unidad y período sin cargar registros completos.
        $existeDuplicado = Exp_broche::where('unidad', $data['id_unidad'])
            ->whereYear('vencimiento', $anioNuevo)
            ->whereMonth('vencimiento', $mesNuevo)
            ->exists();

        if ($existeDuplicado) {
            // El controlador puede traducir este conflicto de duplicidad a una respuesta HTTP 409.
            throw new Exception('Esta unidad ya se encuentra cargada para este mes y año.', 409);
        }

        // Persiste el broche solo después de comprobar que no existe otro para el período.
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
        // Consulta mysql9 de forma explícita para localizar el broche solicitado.
        $broche = DB::connection('mysql9')->table('exp_broche')->where('id', $id)->first();

        if (!$broche) {
            throw new Exception('El broche no existe o ya fue eliminado.', 404);
        }

        // Elimina el registro únicamente después de confirmar que existe.
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
            // Agrega el filtro del administrador solo cuando se recibe un valor.
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
        // Confirma que el broche exista antes de intentar actualizarlo.
        $broche = DB::connection('mysql9')->table('exp_broche')->where('id', $id)->first();

        if (!$broche) {
            throw new \Exception("El broche con ID {$id} no fue encontrado.");
        }

        // Traduce los nombres del payload del frontend a las columnas persistidas del broche.
        DB::connection('mysql9')->table('exp_broche')
            ->where('id', $id)
            ->update([
                'vencimiento' => $datos['vencimiento'],
                'periodo'     => $datos['periodo'],
                'anio'        => $datos['anio'],
                'extraordinaria' => $datos['importe_extraordinaria'],
                'ordinaria'      => $datos['importe_ordinaria'],
                'total'          => $datos['total']
            ]);

        return true;
    }

    public function obtenerBancosService()
    {
        $bancos = Bancos::all();
        return $bancos;
    }
}
