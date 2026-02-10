<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\At_cl\Foto;
use App\Models\At_cl\Empresas;
use App\Models\usuarios_y_permisos\Usuario;


class Propiedad extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    // En el modelo Precio.php


    /**
     * Especifica el nombre de la tabla asociada al modelo.
     *
     * El nombre de la tabla en la base de datos es 'propiedades'. Esto se define explícitamente
     * porque Laravel utiliza convenciones que asumen que el nombre de la tabla es el pluralizado
     * del nombre del modelo, pero en este caso la tabla se llama 'propiedades'.
     *
     * @var string
     */
    protected $table = 'propiedades';

    /**
     * Indica los campos que pueden ser asignados masivamente.
     *
     * Estos campos son los que se pueden incluir al crear o actualizar una instancia del modelo.
     * El uso de `$fillable` previene ataques de asignación masiva (Mass Assignment).
     *
     * @var array
     */
    protected $fillable = [
        'id_barrio',
        'id_calle',
        'numero_calle',
        'id_estado_general',
        'id_inmueble',
        'id_localidad',
        'id_provincia',
        'id_zona',
        'id_estado_alquiler',
        'id_estado_venta',
        'cod_alquiler',
        'cod_venta',
        'gas',
        'asfalto',
        'cloaca',
        /* 'folio', */
        'agua',
        /* 'id_precio', */
        'id_tasacion',
        'cantidad_dormitorios',
        'empresa',
        'piso',
        'departamento',
        'llave',
        'comentario_llave',
        'cartel',
        'comentario_cartel',
        'cochera',
        'numero_cochera',
        'mLote',
        'mCubiertos',
        'banios',
        'moneda',
        'comparte_venta',
        'autorizacion_venta',
        'fecha_autorizacion_venta',
        'exclusividad_venta',
        'condicionado_venta',
        'autorizacion_alquiler',
        'exclusividad_alquiler',
        'clausula_de_venta',
        'tiempo_clausula',
        'fecha_autorizacion_alquiler',
        'vencimientoDeContrato',
        'descipcion_propiedad',
        'last_modified_by',
        'created_at',
        'updated_at',
        'ph',
        'condicion',
        'comentario_autorizacion',
        'publicado',
        'venta_fecha_alta',
        'alquiler_fecha_alta',
        'fecha_publicacion_ig',
        'zona_prop',
        'flyer',
        'reel',
        'web',
        'captador_int',
        'asesor',
        'mascota',

    ];


    /**
     * Relación con el modelo `Barrio`.
     *
     * Este método establece una relación de tipo "pertenece a" (belongsTo) con el modelo `Barrio`.
     * Esto significa que cada propiedad tiene un barrio asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function barrio()
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }



    /**
     * Relación con el modelo `Calle`.
     *
     * Este método establece una relación de tipo "pertenece a" (belongsTo) con el modelo `Calle`.
     * Esto significa que cada propiedad tiene una calle asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function calle()
    {
        return $this->belongsTo(Calle::class, 'id_calle');
    }

    /**
     * Relación con el modelo `EstadoGeneral`.
     *
     * Establece la relación con el modelo `Estado_general`, indicando que cada propiedad
     * tiene un estado general asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoGeneral()
    {
        return $this->belongsTo(Estado_general::class, 'id_estado_general');
    }

    /**
     * Relación con el modelo `EstadoAlquiler`.
     *
     * Establece la relación con el modelo `Estado_alquiler`, indicando que cada propiedad
     * tiene un estado general asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoAlquiler()
    {
        return $this->belongsTo(Estado_alquiler::class, 'id_estado_alquiler');
    }

    /**
     * Relación con el modelo `EstadoAlquiler`.
     *
     * Establece la relación con el modelo `Estado_alquiler`, indicando que cada propiedad
     * tiene un estado general asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoVenta()
    {
        return $this->belongsTo(Estado_venta::class, 'id_estado_venta');
    }
    /**
     * Relación con el modelo `TipoInmueble`.
     *
     * Establece la relación con el modelo `Tipo_inmueble`, indicando que cada propiedad
     * tiene un tipo de inmueble asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoInmueble()
    {
        return $this->belongsTo(Tipo_inmueble::class, 'id_inmueble');
    }

    /**
     * Relación con el modelo `Localidad`.
     *
     * Establece la relación con el modelo `Localidad`, indicando que cada propiedad
     * tiene una localidad asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'id_localidad');
    }

    /**
     * Relación con el modelo `Provincia`.
     *
     * Establece la relación con el modelo `Provincia`, indicando que cada propiedad
     * tiene una provincia asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresas::class, 'empresa_propiedad')
            ->withPivot('folio')
            ->withTimestamps();
    }

    /**
     * Relación con el modelo `Zona`.
     *
     * Establece la relación con el modelo `Zona`, indicando que cada propiedad
     * tiene una zona asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }

    public function precio()
    {


        return $this->hasOne(Precio::class, 'propiedad_id')
            ->latest();
    }
    public function precioActual()
    {
        return $this->hasOne(Precio::class, 'propiedad_id')->latestOfMany(); // ahora sí es el último según created_at
    }
    public function ultimoPrecio()
    {
        return $this->hasOne(Precio::class)->latestOfMany();
    }


    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }

    public function video()
    {
        return $this->hasMany(Video::class);
    }

    public function documentacion()
    {
        return $this->hasMany(Documentacion::class);
    }
    public function tasaciones()
    {
        return $this->hasMany(Tasacion::class, 'propiedad_id');
    }


    /**
     * Relación con el modelo `Usuario` (última modificación).
     *
     * Establece la relación con el modelo `Usuario`, indicando que cada propiedad
     * tiene un usuario que realizó la última modificación (referenciado por `last_modified_by`).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    /* public function lastModifiedBy()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by');
    } */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }

    

    public function scopeFiltrar($query, array $filtros)
    {
        // Filtro por tipo de búsqueda (venta/alquiler)
        if (!empty($filtros['busqueda'])) {
            if ($filtros['busqueda'] == 1) {
                $query->whereNotNull('cod_venta');
            } elseif ($filtros['busqueda'] == 2) {
                $query->whereNotNull('cod_alquiler');
            }
        }

        // Código
        if (!empty($filtros['codigo'])) {
            if ($filtros['busqueda'] == 1) {
                $query->where('cod_venta', $filtros['codigo']);
            } elseif ($filtros['busqueda'] == 2) {
                $query->where('cod_alquiler', $filtros['codigo']);
            }
        }

        // Calle
        if (!empty($filtros['calle_id'])) {
            $query->where('id_calle', $filtros['calle_id']);
        }

        // Tipos de inmueble
        if (!empty($filtros['inmuebles'])) {
            $query->whereIn('id_inmueble', $filtros['inmuebles']);
        }

        // Zonas
        if (!empty($filtros['zonas'])) {
            $query->whereIn('id_zona', $filtros['zonas']);
        }

        // Cochera
        if (!empty($filtros['cochera'])) {
            $query->where('cochera', $filtros['cochera']);
        }

        // Mascotas
        if (!empty($filtros['mascotas'])) {
            $query->where('mascota', $filtros['mascotas']);
        }

        // Habitaciones
        if (!empty($filtros['habitaciones'])) {
            $query->where('cantidad_dormitorios', $filtros['habitaciones']);
        }

        // Rango de precios
        if (!empty($filtros['desde']) || !empty($filtros['hasta'])) {
            $query->whereHas('precioActual', function ($q) use ($filtros) {
                // Venta
                if (!empty($filtros['busqueda']) && $filtros['busqueda'] == 1) {
                    $q->whereNotNull('moneda_venta_dolar');

                    if (!empty($filtros['desde'])) {
                        $q->where('moneda_venta_dolar', '>=', $filtros['desde']);
                    }
                    if (!empty($filtros['hasta'])) {
                        $q->where('moneda_venta_dolar', '<=', $filtros['hasta']);
                    }
                }

                // Alquiler
                if (!empty($filtros['busqueda']) && $filtros['busqueda'] == 2) {
                    $q->whereNotNull('moneda_alquiler_pesos');

                    if (!empty($filtros['desde'])) {
                        $q->where('moneda_alquiler_pesos', '>=', $filtros['desde']);
                    }
                    if (!empty($filtros['hasta'])) {
                        $q->where('moneda_alquiler_pesos', '<=', $filtros['hasta']);
                    }
                }
            });
        }

        // Si el checkbox de ampliar no está marcado, filtrar por estados
        if (!isset($filtros['ampliar']) || $filtros['ampliar'] == 0 || $filtros['ampliar'] === false) {
            if (!empty($filtros['busqueda'])) {
                if ($filtros['busqueda'] == 1) {
                    // Para venta, excluir vendidas y baja temporal (IDs 3, 4, 5, 6, 7)
                    $estadosVentaExcluidos = Estado_venta::whereIn('id', ['3', '4', '5', '6', '7'])
                        ->pluck('id')
                        ->toArray();
                    $query->whereNotIn('id_estado_venta', $estadosVentaExcluidos);
                } elseif ($filtros['busqueda'] == 2) {
                    // Para alquiler, excluir alquiladas y baja temporal (IDs 3, 4, 5, 6, 7)
                    $estadosAlquilerExcluidos = Estado_alquiler::whereIn('id', ['3', '4', '5', '6', '7'])
                        ->pluck('id')
                        ->toArray();
                    $query->whereNotIn('id_estado_alquiler', $estadosAlquilerExcluidos);
                }
            }
        }

        return $query;
    }
    // En el modelo Propiedad
    public static function getPropertyDetails(string $id)
    {
        return self::with(['calle', 'zona', 'tipoInmueble', 'precio'])
            ->findOrFail($id);
    }

    public function propietarios()
    {
        return $this->belongsToMany(Padron::class, 'propiedades_padron', 'propiedad_id', 'padron_id')
            ->withPivot('observaciones', 'baja', 'fecha_baja', 'observaciones_baja', 'last_modified_by')
            ->withTimestamps();
    }

    public function empresasPropiedad()
    {
        return $this->belongsToMany(Empresas_propiedades::class, 'propiedad_id', 'empresa_id');
    }
    // App\Models\At_cl\Propiedad.php
    public function folios()
    {
        return $this->hasMany(Empresas_propiedades::class, 'propiedad_id');
    }
}
