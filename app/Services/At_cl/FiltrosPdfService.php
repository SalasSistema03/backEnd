<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Empresas_propiedades;
use Illuminate\Database\Eloquent\Builder;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Propiedades_padron;
use Illuminate\Support\Facades\DB;

class FiltrosPdfService
{
    public function aplicarFiltrosA(array $filtros): Builder
    {

        $query = Propiedad::query();

        if (!empty($filtros['calle'])) {
            $query->where('id_calle', $filtros['calle']);
        }
        if (!empty($filtros['zona_id'])) {
            $query->where('id_zona', $filtros['zona_id']);
        }
        if (!empty($filtros['tipo'])) {
            $query->where('id_inmueble', $filtros['tipo']);
        }
        if (!empty($filtros['estado_id'])) {
            $query->where('id_estado_alquiler', $filtros['estado_id']);
        }
        /* if (!empty($filtros['importe_minimo']) || !empty($filtros['importe_maximo'])) {
            $query->whereHas('precio', function ($q) use ($filtros) {
                if (!empty($filtros['importe_minimo'])) {
                    $q->where('moneda_alquiler_pesos', '>=', $filtros['importe_minimo']);
                }
                if (!empty($filtros['importe_maximo'])) {
                    $q->where('moneda_alquiler_pesos', '<=', $filtros['importe_maximo']);
                }
            });
        } */
        if (!empty($filtros['importe_minimo']) || !empty($filtros['importe_maximo'])) {
            $query->whereHas('precio', function ($q) use ($filtros) {
                $q->where(function ($subQ) use ($filtros) {
                    if (!empty($filtros['importe_minimo']) && !empty($filtros['importe_maximo'])) {
                        $subQ->whereBetween('moneda_alquiler_pesos', [
                            $filtros['importe_minimo'],
                            $filtros['importe_maximo']
                        ]);
                    } elseif (!empty($filtros['importe_minimo'])) {
                        $subQ->where('moneda_alquiler_pesos', '>=', $filtros['importe_minimo']);
                    } elseif (!empty($filtros['importe_maximo'])) {
                        $subQ->where('moneda_alquiler_pesos', '<=', $filtros['importe_maximo']);
                    }
                });
            });
        }


        return $query->with(['fotos', 'documentacion', 'calle', 'zona', 'tipoInmueble', 'precio', 'estadoAlquiler'])->orderBy('created_at', 'desc');
    }

    public function aplicarFiltrosV(array $filtros): Builder
    {
        $query = Propiedad::query();
        $this->aplicarFiltroCalle($query, $filtros);
        $this->aplicarFiltroZona($query, $filtros);
        $this->aplicarFiltroTipo($query, $filtros);
        $this->aplicarFiltroEstado($query, $filtros);
        $this->aplicarFiltroImporte($query, $filtros);
        $this->aplicarOrden($query, $filtros);



        return $query->with(['fotos', 'documentacion', 'calle', 'zona', 'tipoInmueble', 'precio', 'estadoAlquiler']);
    }

    private function aplicarFiltroCalle(Builder $query, array $filtro): void
    {
        if (!empty($filtro['calle'])) {
            $query->where('id_calle', $filtro['calle']);
        }
    }

    private function aplicarFiltroZona(Builder $query, array $filtro): void
    {
        $zonas = array_values(array_filter((array)($filtro['zona'] ?? []), fn($v) => $v !== null && $v !== ''));

        if (!empty($zonas)) {
            $query->whereIn('id_zona', $zonas);
        } elseif (!empty($filtro['zona_id'])) {
            $query->where('id_zona', $filtro['zona_id']);
        }
    }

    private function aplicarFiltroTipo(Builder $query, array $filtros): void
    {
        if (!empty($filtros['tipo'])) {
            $query->where('id_inmueble', $filtros['tipo']);
        }
    }

    private function aplicarFiltroEstado(Builder $query, array $filtros): void
    {
        if (!empty($filtros['estado_id'])) {
            $query->where('id_estado_venta', $filtros['estado_id']);
        }
    }

    private function aplicarFiltroImporte(Builder $query, array $filtros): void
    {
        if (empty($filtros['importe_minimo']) && empty($filtros['importe_maximo'])) {
            return;
        }

        $query->whereHas('precioActual', function ($q) use ($filtros) {
            $q->where(function ($subQ) use ($filtros) {
                $minimo = $filtros['importe_minimo'] ?? null;
                $maximo = $filtros['importe_maximo'] ?? null;

                if ($minimo && $maximo) {
                    $subQ->whereBetween('moneda_venta_dolar', [$minimo, $maximo])
                        ->orWhereBetween('moneda_venta_pesos', [$minimo, $maximo]);
                } elseif ($minimo) {
                    $subQ->where('moneda_venta_dolar', '>=', $minimo)
                        ->orWhere('moneda_venta_pesos', '>=', $minimo);
                } elseif ($maximo) {
                    $subQ->where('moneda_venta_dolar', '<=', $maximo)
                        ->orWhere('moneda_venta_pesos', '<=', $maximo);
                }
            });
        });
    }

    private function aplicarOrden(Builder $query, array $filtros): void
    {
        if (empty($filtros['orden'])) {
            return;
        }

        $ordenamientos = [
            'estado' => ['id_estado_venta', 'asc'],
            'tipo' => ['id_inmueble', 'asc'],
            'zona' => ['id_zona', 'asc'],
            'calle' => ['id_calle', 'asc'],
            'codigo' => ['cod_venta', 'asc'],
        ];

        if (isset($ordenamientos[$filtros['orden']])) {
            [$columna, $direccion] = $ordenamientos[$filtros['orden']];
            $query->orderBy($columna, $direccion);
        }
    }

    public function traerEstadoVenta($propiedades)
    {
        $propiedades = $propiedades->map(function ($propiedad) {
            $propiedad->estado_venta = $propiedad->estadoVenta;
            return $propiedad;
        });
        //dd($propiedades->first());
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
        //dd($propiedades->first());
        return $propiedades;
    }

    public function camposSeleccionados($propiedades, $camposSeleccionados)
    {
        // O si quieres agregarlo como un atributo adicional


        // Agregar los campos seleccionados como atributos a cada propiedad
        $propiedades = $propiedades->map(function ($propiedad) use ($camposSeleccionados) {
            // Agregar un atributo que indique qué campos están seleccionados
            $propiedad->campos_seleccionados = $camposSeleccionados;
            return $propiedad;
        });
        //dd($propiedades->first());
        return $propiedades;
    }

    /* public function traerFolio($propiedades){
     foreach ($propiedades as $propiedad) {
        $propiedad->folio = Empresas_propiedades::where('propiedad_id', $propiedad->id)->get();
        
     }
     dd($propiedades);
    } */
    public function traerFolio($propiedades)
    {
        foreach ($propiedades as $propiedad) {
            $folios = Empresas_propiedades::where('propiedad_id', $propiedad->id)->get();
            $propiedad->setRelation('folio', $folios);
        }
        return $propiedades;
    }
}
