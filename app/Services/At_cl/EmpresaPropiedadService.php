<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para la gestión de relaciones entre propiedades y empresas
 *
 * Esta clase centraliza toda la lógica de asociación, validación,
 * actualización y creación de vínculos entre propiedades y empresas,
 * incluyendo la validación de folios y el límite máximo permitido.
 *
 * @category   Services
 * @package    App\Services\At_cl
 * @since      Class available since Release 1.0.0
 */
class EmpresaPropiedadService
{


    /**
     * Asocia nuevos folios a una propiedad
     *
     * Este método valida que los folios no estén duplicados y crea
     * las asociaciones entre la propiedad y las empresas correspondientes.
     *
     * @param array $folios Array de folios a asociar
     * @param int $propiedadId ID de la propiedad
     * @throws \Exception Si hay folios duplicados
     * @return void
     */
    public function asociarNuevoFolio(array $folios, int $propiedadId)
    {
        try {
            DB::beginTransaction();

            $errores = [];
            $sucursales = [
                1 => 'Central',
                2 => 'Candioti',
                3 => 'Tribunales'
            ];
            $foliosPorEmpresa = [];

            // Validar folios duplicados
            foreach ($folios as $folioArray) {
                foreach ($folioArray as $empresaId => $folio) {
                    if (is_null($folio) || $folio == '') {
                        continue;
                    }

                    $existe = Empresas_propiedades::where('empresa_id', $empresaId)
                        ->where('folio', $folio)
                        ->exists();

                    if ($existe) {
                        $errores[] = "Folio {$folio} ya está asociado a la sucursal {$sucursales[$empresaId]}";
                    } else {
                        $foliosPorEmpresa[] = [
                            'empresa_id' => $empresaId,
                            'folio' => $folio
                        ];
                    }
                }
            }

            if (!empty($errores)) {
                throw new \Exception(implode("\n", $errores));
            }

            // Crear asociaciones de folios
            foreach ($foliosPorEmpresa as $datos) {
                Empresas_propiedades::create([
                    'empresa_id' => $datos['empresa_id'],
                    'folio' => $datos['folio'],
                    'propiedad_id' => $propiedadId
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al asociar folios: ' . $e->getMessage());
        }
    }



    /**
     * Actualiza los folios existentes de una propiedad
     *
     * Este método gestiona la actualización, creación y eliminación
     * de folios asociados a una propiedad.
     *
     * @param int $propiedadId ID de la propiedad
     * @param array $folios Array de folios actualizados
     * @throws \Exception Si hay folios duplicados
     * @return void
     */
    public function actualizarFolioExistente(int $propiedadId, array $folios): void
    {
        try {
            DB::beginTransaction();

            $sucursales = [
                1 => 'Central',
                2 => 'Candioti',
                3 => 'Tribunales'
            ];

            $registrosExistentes = Empresas_propiedades::where('propiedad_id', $propiedadId)->get();
            $empresasAMantener = [];

            // Procesar cada folio
            foreach ($folios as $empresaId => $folio) {
                $empresaId = (int) $empresaId;

                // Eliminar folio si está vacío o es '-'
                if ($folio === null || $folio === '' || $folio === '-') {
                    $registroExistente = $registrosExistentes->where('empresa_id', $empresaId)->first();
                    if ($registroExistente) {
                        $registroExistente->delete();
                    }
                    continue;
                }

                $empresasAMantener[] = $empresaId;

                $registro = Empresas_propiedades::where('propiedad_id', $propiedadId)
                    ->where('empresa_id', $empresaId)
                    ->first();

                // Si el folio no cambió, continuar
                if ($registro && (string) $registro->folio === (string) $folio) {
                    continue;
                }

                // Validar que el folio no exista en otra propiedad
                $folioExisteEnEmpresa = Empresas_propiedades::where('empresa_id', $empresaId)
                    ->where('folio', $folio)
                    ->when($registro, function ($q) use ($registro) {
                        $q->where('id', '!=', $registro->id);
                    })
                    ->exists();

                if ($folioExisteEnEmpresa) {
                    throw new \Exception("El folio {$folio} ya existe para la empresa {$sucursales[$empresaId]}");
                }

                // Actualizar o crear registro
                if ($registro) {
                    $registro->update(['folio' => $folio]);
                } else {
                    Empresas_propiedades::create([
                        'empresa_id' => $empresaId,
                        'folio' => $folio,
                        'propiedad_id' => $propiedadId,
                    ]);
                }
            }

            // Eliminar registros que no están en los nuevos folios
            foreach ($registrosExistentes as $registroExistente) {
                if (!in_array($registroExistente->empresa_id, $empresasAMantener)) {
                    $registroExistente->delete();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al actualizar folios: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los últimos folios asociados a una propiedad
     *
     * @param int $propiedadId ID de la propiedad
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerUltimosFolios(int $propiedadId)
    {
        return Empresas_propiedades::where('propiedad_id', $propiedadId)->get();
    }
}
