<?php

namespace App\Models\At_cl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\At_cl\Foto;
use App\Models\At_cl\Empresas;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\sys\Propiedades_sys;
use App\Models\sys\Contratos_detalle_sys;
use App\Models\agenda\Notas;
use App\Models\proceso\Historial_estado_repactado;



/**
 * Propiedad inmobiliaria.
 *
 * Representa una unidad publicada para venta o alquiler. Concentra los datos
 * físicos, la ubicación, los estados de venta y alquiler, los precios,
 * los propietarios, las empresas asociadas y los historiales de estado.
 *
 * Utiliza la conexión `mysql`, correspondiente a la base de datos del sistema
 * inmobiliario (`sistema_atcl`).
 */
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
        'zona_prop',
        'flyer_v',
        'reel_v',
        'web_v',
        'captador_int_v',
        'asesor',
        'mascota',
        'flyer_a',
        'reel_a',
        'web_a',
        'captador_int_a',
        'latitud',
        'longitud',
        'fecha_ofrecimiento'
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
     * Asesor responsable de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioAsesor()
    {
        return $this->belongsTo(Usuario::class, 'asesor', 'id');
    }

    /**
     * Captador interno asociado mediante el campo `captador_int`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioCaptadorInt()
    {
        return $this->belongsTo(Usuario::class, 'captador_int', 'id');
    }

    /**
     * Historiales de estado repactado asociados a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historiales_estado_repactado()
    {
        // hasMany(RelatedModel::class, 'foreign_key', 'local_key')
        return $this->hasMany(Historial_estado_repactado::class, 'propiedad_id', 'id');
    }


    /**
     * Captador interno de venta asociado mediante `captador_int_v`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioCaptadorIntV()
    {
        return $this->belongsTo(Usuario::class, 'captador_int_v', 'id');
    }

    /**
     * Captador interno de alquiler asociado mediante `captador_int_a`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioCaptadorIntA()
    {
        return $this->belongsTo(Usuario::class, 'captador_int_a', 'id');
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
     * Relación con el modelo `Estado_alquiler`.
     *
     * Indica el estado de alquiler asociado a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoAlquiler()
    {
        return $this->belongsTo(Estado_alquiler::class, 'id_estado_alquiler');
    }

    /**
     * Relación con el modelo `Estado_venta`.
     *
     * Indica el estado de venta asociado a la propiedad.
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

    /**
     * Empresas asociadas a la propiedad a través de `empresa_propiedad`.
     *
     * Cada vínculo incluye el folio que identifica la propiedad dentro de la
     * empresa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function empresas()
    {
        return $this->belongsToMany(Empresas::class, 'empresa_propiedad', 'propiedad_id', 'empresa_id')
            ->withPivot('folio');
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

    /**
     * Obtiene el precio más reciente según `created_at`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precio()
    {


        return $this->hasOne(Precio::class, 'propiedad_id')
            ->latest();
    }

    /**
     * Obtiene el último precio registrado según la clave primaria.
     *
     * `latestOfMany()` usa `id` por defecto para determinar cuál es el
     * registro más reciente de la relación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precioActual()
    {
        return $this->hasOne(Precio::class, 'propiedad_id')->latestOfMany();
    }

    /**
     * Obtiene el último precio usando la clave foránea convencional.
     *
     * Se utiliza para ordenar por precio y validar que la propiedad tenga un
     * precio válido.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ultimoPrecio()
    {
        return $this->hasOne(Precio::class)->latestOfMany();
    }


    /**
     * Fotos asociadas a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }

    /**
     * Videos asociados a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function video()
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Notas activas de la agenda vinculadas a la propiedad.
     *
     * La tabla de notas pertenece a la conexión `mysql6`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Notas()
    {
        return $this->hasMany(Notas::class, 'propiedad_id')
            ->where('activo', 1);
    }

    /**
     * Documentación asociada a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentacion()
    {
        return $this->hasMany(Documentacion::class);
    }

    /**
     * Tasaciones de la propiedad, ordenadas de la más reciente a la más antigua.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasaciones()
    {
        return $this->hasMany(Tasacion::class, 'propiedad_id')->latest();
    }


    /**
     * Relación con el modelo `Usuario` (última modificación).
     *
     * Establece la relación con el modelo `Usuario`, indicando que cada propiedad
     * tiene un usuario que realizó la última modificación (referenciado por `last_modified_by`).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'last_modified_by', 'id');
    }



    /**
     * Aplica los filtros de búsqueda usados por el listado y el mapa de
     * propiedades.
     *
     * El valor de `busqueda` identifica el tipo de operación: 1 venta,
     * 2 alquiler y 3 folio. Los demás filtros se combinan sobre la misma
     * consulta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filtros
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFiltrar($query, array $filtros)
    {
        // Filtra por tipo de operación: venta, alquiler o folio.
        if (!empty($filtros['busqueda'])) {
            if ($filtros['busqueda'] == 1) {
                $query->whereNotNull('cod_venta');
            } elseif ($filtros['busqueda'] == 2) {
                $query->whereNotNull('cod_alquiler');
            } else if ($filtros['busqueda'] == 3) {
                $query->has('folios');
            }
        } else {
            $query->where(function ($q) {
                $q->whereNotNull('cod_venta')
                    ->orWhereNotNull('cod_alquiler');
            });
        }

        // Filtra por código según el tipo de operación.
        if (!empty($filtros['codigo'])) {
            if (!empty($filtros['busqueda'])) {
                if ($filtros['busqueda'] == 1) {
                    $query->where('cod_venta', $filtros['codigo']);
                } elseif ($filtros['busqueda'] == 2) {
                    $query->where('cod_alquiler', $filtros['codigo']);
                } elseif ($filtros['busqueda'] == 3) {
                    $query->whereHas('folios', function ($q) use ($filtros) {
                        $q->where('folio', $filtros['codigo']);
                    });
                }
            } else {
                $query->where(function ($q) use ($filtros) {
                    $q->where('cod_venta', $filtros['codigo'])
                        ->orWhere('cod_alquiler', $filtros['codigo']);
                });
            }
        }

        // Filtra por calle, tipo de inmueble y zona.
        if (!empty($filtros['calle_id'])) {
            $query->where('id_calle', $filtros['calle_id'])
                ->orderBy('numero_calle', 'asc');
        }

        if (!empty($filtros['inmuebles'])) {
            $query->whereIn('id_inmueble', $filtros['inmuebles']);
        }

        if (!empty($filtros['zonas'])) {
            $query->whereIn('id_zona', $filtros['zonas']);
        }

        // Filtra por cochera, mascota y cantidad de dormitorios.
        if (!empty($filtros['cochera'])) {
            $query->where('cochera', $filtros['cochera']);
        }

        if (!empty($filtros['mascotas'])) {
            $query->where('mascota', $filtros['mascotas']);
        }

        if (isset($filtros['habitaciones']) && $filtros['habitaciones'] !== '') {
            $query->where('cantidad_dormitorios', $filtros['habitaciones']);
        }

        // Filtra por cartel y moneda.
        if (isset($filtros['cartel']) && $filtros['cartel'] !== '') {
            $query->where('cartel', $filtros['cartel']);
        }

        if (isset($filtros['moneda']) && $filtros['moneda'] !== '') {
            $query->where('moneda', $filtros['moneda']);
        }

        // Filtra por rango de precios del precio actual: venta en dólares o alquiler en pesos.
        if (!empty($filtros['desde']) || !empty($filtros['hasta'])) {
            $query->whereHas('precioActual', function ($q) use ($filtros) {
                $q->where(function ($subQ) use ($filtros) {
                    // Venta
                    if (empty($filtros['busqueda']) || $filtros['busqueda'] == 1) {
                        $subQ->orWhere(function ($sq1) use ($filtros) {
                            $sq1->whereNotNull('moneda_venta_dolar');
                            if (!empty($filtros['desde'])) {
                                $sq1->where('moneda_venta_dolar', '>=', $filtros['desde']);
                            }
                            if (!empty($filtros['hasta'])) {
                                $sq1->where('moneda_venta_dolar', '<=', $filtros['hasta']);
                            }
                        });
                    }

                    // Alquiler
                    if (empty($filtros['busqueda']) || $filtros['busqueda'] == 2) {
                        $subQ->orWhere(function ($sq2) use ($filtros) {
                            $sq2->whereNotNull('moneda_alquiler_pesos');
                            if (!empty($filtros['desde'])) {
                                $sq2->where('moneda_alquiler_pesos', '>=', $filtros['desde']);
                            }
                            if (!empty($filtros['hasta'])) {
                                $sq2->where('moneda_alquiler_pesos', '<=', $filtros['hasta']);
                            }
                        });
                    }
                });
            });
        }

        // Si no se solicitó ampliar, se excluyen los estados finalizados o de baja.
        if (!isset($filtros['ampliar']) || $filtros['ampliar'] == 0 || $filtros['ampliar'] === false) {
            $estadosVentaExcluidos = Estado_venta::whereIn('id', ['3', '4', '5', '6', '7'])->pluck('id')->toArray();
            $estadosAlquilerExcluidos = Estado_alquiler::whereIn('id', ['3', '4', '5', '6', '7'])->pluck('id')->toArray();

            if (!empty($filtros['busqueda'])) {
                if ($filtros['busqueda'] == 1) {
                    // Se excluyen los estados finalizada, baja temporal, reset, retirada y baja.
                    $query->whereNotIn('id_estado_venta', $estadosVentaExcluidos);
                } elseif ($filtros['busqueda'] == 2) {
                    // Se excluyen los estados alquilada, baja temporal, reset, pendiente y baja.
                    $query->whereNotIn('id_estado_alquiler', $estadosAlquilerExcluidos);
                }
            } else {
                $query->where(function ($q) use ($estadosVentaExcluidos, $estadosAlquilerExcluidos) {
                    $q->whereNotIn('id_estado_venta', $estadosVentaExcluidos)
                        ->orWhereNotIn('id_estado_alquiler', $estadosAlquilerExcluidos);
                });
            }
        }

        return $query;
    }

    /**
     * Obtiene una propiedad con sus relaciones de ubicación, tipo y precio.
     *
     * @param string $id
     * @return self
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function getPropertyDetails(string $id)
    {
        return self::with(['calle', 'zona', 'tipoInmueble', 'precio'])
            ->findOrFail($id);
    }

    /**
     * Propietarios registrados en el padrón y vinculados a la propiedad.
     *
     * El vínculo conserva los datos de baja y observaciones, y carga los
     * teléfonos de cada propietario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function propietarios()
    {
        return $this->belongsToMany(Padron::class, 'propiedades_padron', 'propiedad_id', 'padron_id')
            ->withPivot('observaciones', 'baja', 'fecha_baja', 'observaciones_baja', 'last_modified_by')
            ->withTimestamps()
            ->with('telefonos');
    }

    /**
     * Registros de la tabla `empresa_propiedad` de la propiedad.
     *
     * Cada registro contiene el folio y la empresa asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function empresasPropiedad()
    {
        return $this->hasMany(Empresas_propiedades::class, 'propiedad_id');
    }

    /**
     * Folios de la propiedad.
     *
     * Es la misma relación que `empresasPropiedad()` y se utiliza para
     * recorrer los folios en las búsquedas de contratos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folios()
    {
        return $this->hasMany(Empresas_propiedades::class, 'propiedad_id');
    }

    /**
     * Observaciones asociadas a la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function observacionesPropiedades()
    {
        return $this->hasMany(Observaciones_propiedades::class, 'propiedad_id');
    }

    /**
     * Busca las casas del sistema de contratos que coinciden con los folios
     * de la propiedad.
     *
     * Por cada folio se consulta la conexión `mysql2` y se exige que la
     * carpeta de la casa y la empresa del contrato coincidan con el vínculo.
     *
     * @return array
     */
    public function buscarCasa()
    {
        $datos = [];

        // Se recorren los folios cargados desde empresa_propiedad.
        foreach ($this->folios as $folio) {
            if ($folio->folio != null) {

                $casasConContrato = Propiedades_sys::select('propiedades.id_casa')
                    ->join('contratos_cabecera as c', 'propiedades.id_casa', '=', 'c.id_casa')
                    ->where('propiedades.carpeta', $folio->folio)
                    ->where('c.id_empresa', $folio->empresa_id)
                    ->distinct()
                    ->get();

                // Solo se agregan los folios que tienen al menos un contrato.
                if ($casasConContrato->count() > 0) {
                    $datos[] = [
                        'id_casa' => $casasConContrato->pluck('id_casa')->toArray(),
                        'carpeta' => $folio->folio,
                        'empresa_id' => $folio->empresa_id
                    ];
                }
            }
        }

        return $datos;
    }

    /**
     * Obtiene el contrato con el id más alto entre todos los folios.
     *
     * En cada folio se toma el contrato con el id más alto y luego se
     * conserva el mayor de todos.
     *
     * @return array
     */
    public function buscarContratoMasReciente()
    {
        $datos = [];
        $contratoMasAlto = null;

        foreach ($this->folios as $folio) {
            if ($folio->folio != null) {
                // Obtiene el contrato con el id más alto para este folio.
                $contrato = Propiedades_sys::select('propiedades.id_casa', 'c.id_contrato_cabecera', 'c.comienza', 'c.rescicion')
                    ->join('contratos_cabecera as c', 'propiedades.id_casa', '=', 'c.id_casa')
                    ->where('propiedades.carpeta', $folio->folio)
                    ->where('c.id_empresa', $folio->empresa_id)
                    ->orderBy('c.id_contrato_cabecera', 'desc')
                    ->first();

                if ($contrato) {
                    // Si es el primer contrato o tiene un id mayor, se reemplaza el resultado.
                    if ($contratoMasAlto === null || $contrato->id_contrato_cabecera > $contratoMasAlto->id_contrato_cabecera) {
                        $contratoMasAlto = $contrato;
                        $datos = [
                            'id_casa' => $contrato->id_casa,
                            'carpeta' => $folio->folio,
                            'empresa_id' => $folio->empresa_id,
                            'id_contrato_cabecera' => $contrato->id_contrato_cabecera,
                            'comienza' => $contrato->comienza,
                            'rescicion' => $contrato->rescicion
                        ];
                    }
                }
            }
        }

        return $datos;
    }

    /**
     * Obtiene el detalle con mayor monto de alquiler de un contrato.
     *
     * @param int|string $id_contrato_cabecera
     * @return array|null
     */
    public function buscarDetalleContratoMasAlto($id_contrato_cabecera)
    {
        // Se ordena por monto para quedarse con el detalle de mayor alquiler.
        $detalle = Contratos_detalle_sys::select('monto_alquiler', 'hasta_fecha')
            ->where('id_contrato_cabecera', $id_contrato_cabecera)
            ->orderBy('monto_alquiler', 'desc')
            ->first();

        return $detalle ? [
            'monto_alquiler' => $detalle->monto_alquiler,
            'hasta_fecha' => $detalle->hasta_fecha
        ] : null;
    }

    /**
     * Obtiene el último historial de estado de alquiler.
     *
     * `latestOfMany('id')` selecciona el registro con el id más alto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function historialEstadosAlquiler()
    {
        return $this->hasOne(HistorialEstadosAlquiler::class, 'id_propiedad')
            ->latestOfMany('id');
    }

    /**
     * Obtiene un registro del historial de estados de venta.
     *
     * La relación no define un orden, por lo que no garantiza el registro
     * más reciente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function historialEstadosVenta()
    {
        return $this->hasOne(HistorialEstadosVenta::class, 'id_propiedad');
    }

    /**
     * Busca propiedades por código o por nombre de calle según el sector.
     *
     * En Ventas se consideran los estados 1, 2 y 5; en Alquiler, los estados
     * 1 y 2. Si el código no devuelve resultados, la búsqueda se repite por
     * nombre de calle. En Depto.Tecnico se busca por cualquiera de los dos
     * códigos sin filtrar por estado.
     *
     * @param string $codigo_calle
     * @param string $sector
     * @return \Illuminate\Support\Collection
     */
    public function buscarPorCodigoCalle($codigo_calle, $sector)
    {
        if ($sector == 'Ventas') {
            // Busca por coincidencia parcial del código de venta.
            $propiedad = Propiedad::where('cod_venta', 'like', '%' . $codigo_calle . '%')
                ->whereIn('id_estado_venta', [1, 2, 5])
                ->get();

            // Si no hay resultados, busca por nombre de calle.
            if (!$propiedad || $propiedad->isEmpty()) {
                $propiedad = Propiedad::whereHas('calle', function ($query) use ($codigo_calle) {
                    $query->where('name', 'like', '%' . $codigo_calle . '%')
                        ->where('cod_venta', '!=', null)
                        ->whereIn('id_estado_venta', [1, 2, 5]);
                })
                    ->get();
            }
        }

        if ($sector == 'Alquiler') {
            // Busca por coincidencia parcial del código de alquiler.
            $propiedad = Propiedad::where('cod_alquiler', 'like', '%' . $codigo_calle . '%')
                ->whereIn('id_estado_alquiler', [1, 2])
                ->get();

            // Si no hay resultados, busca por nombre de calle.
            if (!$propiedad || $propiedad->isEmpty()) {
                $propiedad = Propiedad::whereHas('calle', function ($query) use ($codigo_calle) {
                    $query->where('name', 'like', '%' . $codigo_calle . '%')
                        ->where('cod_alquiler', '!=', null)
                        ->whereIn('id_estado_alquiler', [1, 2]);
                })
                    ->get();
            }
        }

        if ($sector == 'Depto.Tecnico') {
            // Busca por código de alquiler o de venta, sin filtro de estado.
            $propiedad = Propiedad::where('cod_alquiler', 'like', '%' . $codigo_calle . '%')
                ->orWhere('cod_venta', 'like', '%' . $codigo_calle . '%')
                ->get();

            // Si no hay resultados, busca por nombre de calle.
            if (!$propiedad || $propiedad->isEmpty()) {
                $propiedad = Propiedad::whereHas('calle', function ($query) use ($codigo_calle) {
                    $query->where('name', 'like', '%' . $codigo_calle . '%');
                })
                    ->get();
            }
        }

        // Normaliza los datos que se devuelven al frontend.
        $propiedad = $propiedad->map(function ($prop) {
            return [
                'id' => $prop->id,
                'cod_venta' => $prop->cod_venta,
                'cod_alquiler' => $prop->cod_alquiler,
                'calle' => $prop->calle->name,
                'numero_calle' => $prop->numero_calle,
                'estado_venta' => $prop->estadoVenta->name ?? null,
                'estado_alquiler' => $prop->estadoAlquiler->name ?? null,
            ];
        });

        return $propiedad;
    }
}
