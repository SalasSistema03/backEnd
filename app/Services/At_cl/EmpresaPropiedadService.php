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


    public function asociarNuevoFolio(array $folios, int $propiedadId)
    {
        $errores = [];
        $sucursales = [
            1 => 'Central',
            2 => 'Candioti',
            3 => 'Tribunales'
        ];
        $foliosPorEmpresa = [];

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
        } else {
            foreach ($foliosPorEmpresa as $datos) {
                // $datos es un array con claves 'empresa_id' y 'folio'
                Empresas_propiedades::create([
                    'empresa_id' => $datos['empresa_id'],
                    'folio' => $datos['folio'],
                    'propiedad_id' => $propiedadId
                ]);
            }
        }
    }



    public function actualizarFolioExistente(int $propiedadId, array $folios): void
    {
        $sucursales = [
            1 => 'Central',
            2 => 'Candioti',
            3 => 'Tribunales'
        ];
        foreach ($folios as $empresaId => $folio) {
            if ($folio === null || $folio === '' || $folio === '-') {
                continue;
            }

            $empresaId = (int) $empresaId;

            $registro = Empresas_propiedades::where('propiedad_id', $propiedadId)
                ->where('empresa_id', $empresaId)
                ->first();

            if ($registro && (string) $registro->folio === (string) $folio) {
                continue;
            }

            $folioExisteEnEmpresa = Empresas_propiedades::where('empresa_id', $empresaId)
                ->where('folio', $folio)
                ->when($registro, function ($q) use ($registro) {
                    $q->where('id', '!=', $registro->id);
                })
                ->exists();

            if ($folioExisteEnEmpresa) {
                throw new \Exception("El folio {$folio} ya existe para la empresa {$sucursales[$empresaId]}");
            }

            if ($registro) {
                $registro->update(['folio' => $folio]);
                continue;
            }

            Empresas_propiedades::create([
                'empresa_id' => $empresaId,
                'folio' => $folio,
                'propiedad_id' => $propiedadId,
            ]);
        }
    }

    public function obtenerUltimosFolios(int $propiedadId)
    {
       $folios = Empresas_propiedades::where('propiedad_id', $propiedadId)->get();
       //dd($folios);
    }
}
