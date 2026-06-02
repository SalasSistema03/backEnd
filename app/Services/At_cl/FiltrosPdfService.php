<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Database\Eloquent\Builder;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Propiedades_padron;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FiltrosPdfService
{
    /**
     * Aplicar filtros unificados para ambos sectores (Venta y Alquiler)
     *
     * @param array $filtros Los filtros incluyen 'sector' => 'Venta' o 'Alquiler'
     * @return Builder
     */
    public function aplicarFiltrosUnificados(array $filtros): Builder
    {
        $query = Propiedad::query();
        $sector = $filtros['sector'] ?? 'Venta';

        // Filtro por código según sector (cod_venta o cod_alquiler no nulo)
        if ($sector === 'Alquiler') {
            $query->whereNotNull('cod_alquiler');
        } else {
            $query->whereNotNull('cod_venta');
        }

        // Aplicar filtros comunes
        $this->aplicarFiltroCalle($query, $filtros);
        $this->aplicarFiltroZona($query, $filtros);
        $this->aplicarFiltroTipo($query, $filtros);
        $this->aplicarFiltroEstadoPorSector($query, $filtros, $sector);
        $this->aplicarFiltroCartel($query,$filtros);

        $this->aplicarFiltroImportePorSector($query, $filtros, $sector);
        $this->aplicarOrdenPorSector($query, $filtros, $sector);

        // Eager loading de relaciones comunes
        return $query->with([
            'fotos',
            'documentacion',
            'calle',
            'zona',
            'tipoInmueble',
            'precio',
            'folios.empresa',
            'propietarios'
        ]);
    }

    /**
     * Método legacy para mantener compatibilidad con código existente (Alquiler)
     */
    public function aplicarFiltrosA(array $filtros): Builder
    {
        $filtros['sector'] = 'Alquiler';
        return $this->aplicarFiltrosUnificados($filtros);
    }

    /**
     * Método legacy para mantener compatibilidad con código existente (Venta)
     */
    public function aplicarFiltrosV(array $filtros): Builder
    {
        $filtros['sector'] = 'Venta';
        return $this->aplicarFiltrosUnificados($filtros);
    }

    // ========== FILTROS COMUNES ==========

    private function aplicarFiltroCalle(Builder $query, array $filtro): void
    {
        if (!empty($filtro['calle'])) {
            $query->where('id_calle', $filtro['calle']);
        }
    }

    private function aplicarFiltroZona(Builder $query, array $filtro): void
    {
        // Soporta tanto 'zona_id' (formato antiguo) como 'zona' (formato nuevo)
        $zonas = [];

        if (!empty($filtro['zona']) && is_array($filtro['zona'])) {
            $zonas = array_values(array_filter($filtro['zona'], fn($v) => $v !== null && $v !== ''));
        } elseif (!empty($filtro['zona_id'])) {
            $zonas = is_array($filtro['zona_id'])
                ? $filtro['zona_id']
                : [$filtro['zona_id']];
        }

        if (!empty($zonas)) {
            Log::info('Filtro zona', [
                'zona' => $filtro['zona'] ?? null,
                'zona_id' => $filtro['zona_id'] ?? null,
                'zonas' => $zonas,
            ]);
            $query->whereIn('id_zona', $zonas);
        }
    }

    private function aplicarFiltroTipo(Builder $query, array $filtros): void
    {
        if (!empty($filtros['tipo'])) {
            $query->where('id_inmueble', $filtros['tipo']);
        }
    }

    private function aplicarFiltroCartel(Builder $query, array $filtros): void
    {
        if (!empty($filtros['cartel'])) {
            $query->where('cartel', $filtros['cartel']);
        }
    }

    // ========== FILTROS POR SECTOR ==========

    /**
     * Filtro de estado según sector (id_estado_alquiler o id_estado_venta)
     */
    private function aplicarFiltroEstadoPorSector(Builder $query, array $filtros, string $sector): void
    {
        if (empty($filtros['estado_id'])) {
            return;
        }

        $campoEstado = ($sector === 'Alquiler') ? 'id_estado_alquiler' : 'id_estado_venta';
        $query->where($campoEstado, $filtros['estado_id']);
    }



    private function aplicarFiltroImportePorSector(Builder $query, array $filtros, string $sector): void
    {
        $importeMinimo = $filtros['importe_minimo'] ?? null;
        $importeMaximo = $filtros['importe_maximo'] ?? null;

        if (empty($importeMinimo) && empty($importeMaximo)) {
            return;
        }

        if ($sector === 'Venta') {
            $query->whereHas('ultimoPrecio', function ($q) use ($importeMinimo, $importeMaximo) {
                $q->where(function ($subQ) use ($importeMinimo, $importeMaximo) {
                    if ($importeMinimo && $importeMaximo) {
                        $subQ->whereBetween('moneda_venta_dolar', [$importeMinimo, $importeMaximo])
                            ->orWhereBetween('moneda_venta_pesos', [$importeMinimo, $importeMaximo]);
                    } elseif ($importeMinimo) {
                        $subQ->where('moneda_venta_dolar', '>=', $importeMinimo)
                            ->orWhere('moneda_venta_pesos', '>=', $importeMinimo);
                    } elseif ($importeMaximo) {
                        $subQ->where('moneda_venta_dolar', '<=', $importeMaximo)
                            ->orWhere('moneda_venta_pesos', '<=', $importeMaximo);
                    }
                });
            });
        } elseif ($sector === 'Alquiler') {
            $query->whereHas('ultimoPrecio', function ($q) use ($importeMinimo, $importeMaximo) {
                $q->where(function ($subQ) use ($importeMinimo, $importeMaximo) {
                    if ($importeMinimo && $importeMaximo) {
                        $subQ->whereBetween('moneda_alquiler_pesos', [$importeMinimo, $importeMaximo])
                            ->orWhereBetween('moneda_alquiler_dolar', [$importeMinimo, $importeMaximo]);
                    } elseif ($importeMinimo) {
                        $subQ->where('moneda_alquiler_pesos', '>=', $importeMinimo)
                            ->orWhere('moneda_alquiler_dolar', '>=', $importeMinimo);
                    } elseif ($importeMaximo) {
                        $subQ->where('moneda_alquiler_pesos', '<=', $importeMaximo)
                            ->orWhere('moneda_alquiler_dolar', '<=', $importeMaximo);
                    }
                });
            });
        }

        /* Log::info('SQL generado en aplicarFiltroImportePorSector:', [
        'sql' => $query->toSql(),
        'parametros' => $query->getBindings()
    ]); */
    }



    //Aplicar Ordenamiento, anda todo menos el de precio a revisar
    private function aplicarOrdenPorSector(Builder $query, array $filtros, string $sector): void
    {
        if (empty($filtros['orden'])) {
            return;
        }

        $campoCodigo = ($sector === 'Alquiler') ? 'cod_alquiler' : 'cod_venta';
        $campoEstado = ($sector === 'Alquiler') ? 'id_estado_alquiler' : 'id_estado_venta';

        $ordenamientos = [
            'estado' => [$campoEstado, 'asc'],
            'tipo' => ['id_inmueble', 'asc'],
            'zona' => ['id_zona', 'asc'],
            'calle' => ['id_calle', 'asc'],
            'codigo' => [$campoCodigo, 'asc'],
        ];

        /* // Orden por precio (requiere ordenamiento post-query por la relación)
         if ($filtros['orden'] === 'precio_asc') {
            // Se manejará después de la consulta
            return;
        }
        if ($filtros['orden'] === 'precio_desc') {
            return;
        } */

        if (isset($ordenamientos[$filtros['orden']])) {
            [$columna, $direccion] = $ordenamientos[$filtros['orden']];
            $query->orderBy($columna, $direccion);
        }
    }




    /**
     * Orden por precio (debe aplicarse después de obtener la colección)
     *
     * @param \Illuminate\Support\Collection $propiedades
     * @param string $orden 'precio_asc' o 'precio_desc'
     * @param string $sector 'Venta' o 'Alquiler'
     * @return \Illuminate\Support\Collection
     */
    public function ordenarPorPrecio($propiedades, string $orden, string $sector)
    {
        $campoPrecio = ($sector === 'Alquiler')
            ? 'precio.moneda_alquiler_pesos'
            : 'precio.moneda_venta_dolar'; // O usa moneda_venta_pesos según necesidad

        if ($orden === 'precio_asc') {
            return $propiedades->sortBy(function ($propiedad) use ($sector) {
                if ($sector === 'Alquiler') {
                    return $propiedad->precio?->moneda_alquiler_pesos ?? 0;
                }
                // Para venta, priorizamos dólar, si no hay, pesos
                return $propiedad->precio?->moneda_venta_dolar
                    ?? $propiedad->precio?->moneda_venta_pesos
                    ?? 0;
            });
        }

        if ($orden === 'precio_desc') {
            return $propiedades->sortByDesc(function ($propiedad) use ($sector) {
                if ($sector === 'Alquiler') {
                    return $propiedad->precio?->moneda_alquiler_pesos ?? 0;
                }
                return $propiedad->precio?->moneda_venta_dolar
                    ?? $propiedad->precio?->moneda_venta_pesos
                    ?? 0;
            });
        }

        return $propiedades;
    }








































































































    // ========== MÉTODOS AUXILIARES EXISTENTES (sin cambios) ==========

    public function traerEstadoVenta($propiedades)
    {
        $propiedades = $propiedades->map(function ($propiedad) {
            $propiedad->estado_venta = $propiedad->estadoVenta;
            return $propiedad;
        });
        return $propiedades;
    }

    public function traerPropietarios($propiedades)
    {
        $propiedades_padron = Propiedades_padron::select(
            'propiedades_padron.*',
            DB::raw("CONCAT(padron.nombre, ' ', padron.apellido) as nombre_completo")
        )
            ->join('padron', 'propiedades_padron.padron_id', '=', 'padron.id')
            ->whereIn('propiedades_padron.propiedad_id', $propiedades->pluck('id'))
            ->get();

        $propiedades = $propiedades->map(function ($propiedad) use ($propiedades_padron) {
            $propiedad->propiedades_padron = $propiedades_padron->where('propiedad_id', $propiedad->id)->first();
            return $propiedad;
        });
        return $propiedades;
    }

    public function camposSeleccionados($propiedades, $camposSeleccionados)
    {
        $propiedades = $propiedades->map(function ($propiedad) use ($camposSeleccionados) {
            $propiedad->campos_seleccionados = $camposSeleccionados;
            return $propiedad;
        });
        return $propiedades;
    }

    public function traerFolio($propiedades)
    {
        foreach ($propiedades as $propiedad) {
            $folios = Empresas_propiedades::where('propiedad_id', $propiedad->id)->get();
            $propiedad->setRelation('folio', $folios);
        }
        return $propiedades;
    }
}
