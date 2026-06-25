<?php
namespace App\Services\impuesto\EXP;

use App\Models\impuesto\Exp_edificio;
use App\Models\impuesto\Exp_Unidades;
use App\Models\impuesto\Exp_unidades_sys;
use App\Models\impuesto\Exp_administrador_consorcio;

use Illuminate\Support\Facades\DB;

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


}