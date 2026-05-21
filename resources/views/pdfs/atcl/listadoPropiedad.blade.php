<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /*asi tiene que quedar siempre {!! file_get_contents(public_path('css/pdfStyles.css')) !!} */
            {!! file_get_contents(public_path('css/pdfStyles.css')) !!} 
    </style>
</head>

<body>

    @php
    $campos = $informacionMostrar ?? [];
    @endphp

    <div class="row mx-3">
        <div class="header col-12 row">
            <div class="col-3">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>

            <div class="col-9 text-end">
                Listado {{ $sector }}
                <br>
                @isset($contadorPropiedades)
                Total de Propiedades: {{ $contadorPropiedades }}
                @endisset
                @isset($consultaTotal)
                <span class="listado_texto_titulo">Consultas: {{ $consultaTotal }}</span>
                @endisset
                @isset($muestraTotal)
                <span class="listado_texto_titulo">Muestras: {{ $muestraTotal }}</span>
                @endisset
                @isset($ofrecimientoTotal)
                <span class="listado_texto_titulo">Ofrecimientos: {{ $ofrecimientoTotal }}</span>
                @endisset
            </div>

            <hr>
        </div>

        @if ($pertenece === 'listadoPropiedades')
        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        @if ($sector === 'Alquiler')
                        <th>Código Alquiler</th>
                        @elseif ($sector === 'Venta')
                        <th>Código Venta</th>
                        @endif
                        @if (in_array('folio', $campos))
                        <th>Folio / Empresa</th>
                        @endif
                        @if (in_array('direccion', $campos))
                        <th>Dirección</th>
                        @endif
                        @if (in_array('zona', $campos))
                        <th>Zona</th>
                        @endif
                        @if (in_array('p_d', $campos))
                        <th>Piso / Depto</th>
                        @endif
                        @if (in_array('propietario', $campos) && $sector === 'Venta')
                        <th>Propietario</th>
                        @endif
                        @if (in_array('fecha alta', $campos) && $sector === 'Venta')
                        <th>Fecha Alta</th>
                        @endif
                        @if (in_array('dormitorio', $campos))
                        <th>Dormitorios</th>
                        @endif
                        @if (in_array('cochera', $campos))
                        <th>Cochera</th>
                        @endif
                        @if (in_array('inmueble', $campos))
                        <th>Inmueble</th>
                        @endif
                        @if (in_array('estado', $campos))
                        <th>Estado</th>
                        @endif
                        @if (in_array('precio', $campos))
                        <th>Precio</th>
                        @endif
                        @if (in_array('clausula venta', $campos) && $sector === 'Venta')
                        <th>Clausula Venta</th>
                        @endif
                        @if (in_array('descripcion', $campos) && $sector === 'Venta')
                        <th>Descripcion</th>
                        @endif
                        @if (in_array('llave', $campos) && $sector === 'Venta')
                        <th>LLave</th>
                        @endif
                        @if (in_array('cartel', $campos))
                        <th>Cartel</th>
                        @endif
                        @if (in_array('autorizacion', $campos) && $sector === 'Venta')
                        <th>Autorización</th>
                        @endif
                        @if (in_array('compartida', $campos) && $sector === 'Venta')
                        <th>Compartida</th>
                        @endif
                        @if (in_array('foto', $campos))
                        <th>Foto</th>
                        @endif
                        @if (in_array('video', $campos))
                        <th>Videos</th>
                        @endif
                        @if (in_array('documentacion', $campos))
                        <th>Documentación</th>
                        @endif
                        @if (in_array('reel', $campos) && $sector === 'Venta')
                        <th>Reel</th>
                        @endif
                        @if (in_array('flyer', $campos) && $sector === 'Venta')
                        <th>Flyer</th>
                        @endif
                        @if (in_array('captador', $campos) && $sector === 'Venta')
                        <th>Captador</th>
                        @endif
                        @if (in_array('zonaprop', $campos) && $sector === 'Venta')
                        <th>ZonaProp</th>
                        @endif
                        @if (in_array('web', $campos) && $sector === 'Venta')
                        <th>WEB</th>
                        @endif
                        @if (in_array('vendedor', $campos) && $sector === 'Venta')
                        <th>Vendedor</th>
                        @endif
                        @if (in_array('usuario', $campos))
                        <th>Usuario</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="listado_tabla">
                    @foreach ($propiedades as $propiedad)
                    <tr>

                        @if ($sector === 'Alquiler')
                        <td @if ($propiedad->estadoVenta?->name === 'EN VENTA') class="listado_estado_en_ambos" @endif>
                            {{ $propiedad->cod_alquiler ?? '' }}

                        </td>
                        @elseif ($sector === 'Venta')
                        <td @if ($propiedad->estadoAlquiler?->name === 'EN ALQUILER' || $propiedad->estadoAlquiler?->name === 'ALQUILADA') class="listado_estado_en_ambos" @endif>
                            <span class="">{{ $propiedad->cod_venta ?? '' }} </span>
                            @if ($propiedad->estadoAlquiler?->name === 'ALQUILADA')
                            <span class="listado_estado_alquilado">ALQ</span>
                            @elseif($propiedad->estadoAlquiler?->name === 'EN ALQUILER')
                            <span class="listado_estado_alquilado">DISP</span>
                            @endif
                        </td>
                        @endif

                        @if (in_array('folio', $campos))
                        <td>
                            @forelse ($propiedad->folios ?? [] as $ep)
                            {{ $ep->folio ?? '' }}

                            @if ($ep->empresa->nombre == 'Dolly')
                            CAN
                            @elseif($ep->empresa->nombre == 'Flor')
                            TRIB
                            @endif

                            @if (!$loop->last)
                            <br>
                            @endif
                            @empty
                            114
                            @endforelse
                        </td>
                        @endif

                        @if (in_array('direccion', $campos))
                        <td>
                            {{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                        </td>
                        @endif

                        @if (in_array('zona', $campos))
                        <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                        @endif

                        @if (in_array('p_d', $campos))
                        <td>
                            @if ($propiedad->piso == null || $propiedad->piso == 0)
                            {{ $propiedad->departamento ?? '' }}
                            @elseif($propiedad->departamento == null)
                            {{ $propiedad->piso ?? '' }}
                            @else
                            {{ $propiedad->piso ?? '' }} / {{ $propiedad->departamento ?? '' }}
                            @endif
                        </td>
                        @endif

                        @if (in_array('propietario', $campos) && $sector === 'Venta')
                        <td>
                            @foreach ($propiedad->propietarios as $propietario)
                            {{ $propietario->nombre }} {{ $propietario->apellido }}
                            @if (!$loop->last)
                            ,
                            @endif
                            @endforeach
                        </td>
                        @endif
                        @if (in_array('fecha alta', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->venta_fecha_alta ?? '' }}</td>
                        @endif
                        @if (in_array('dormitorio', $campos))
                        <td>{{ $propiedad->cantidad_dormitorios ?? '' }}</td>
                        @endif

                        @if (in_array('cochera', $campos))
                        <td>{{ $propiedad->cochera ?? '' }}</td>
                        @endif

                        @if (in_array('inmueble', $campos))
                        <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                        @endif

                        @if (in_array('estado', $campos) && $sector === 'Alquiler')
                        <td>{{ $propiedad->estadoAlquiler->name ?? '' }}</td>
                        @elseif(in_array('estado', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->estadoVenta->name ?? '' }}</td>
                        @endif

                        @if (in_array('precio', $campos) && $sector === 'Alquiler')
                        <td style="white-space: nowrap;">
                            @if ($propiedad->precio && $propiedad->precio->moneda_alquiler_pesos && $propiedad->precio->moneda_alquiler_pesos != 0)
                            $ {{ $propiedad->precio->moneda_alquiler_pesos }}
                            @elseif($propiedad->precio && $propiedad->precio->moneda_alquiler_dolar != 0)
                            u$s {{ $propiedad->precio->moneda_alquiler_dolar }}
                            @endif
                        </td>
                        @elseif(in_array('precio', $campos) && $sector === 'Venta')
                        <td style="white-space: nowrap;">
                            @if ($propiedad->precio && $propiedad->precio->moneda_venta_dolar && $propiedad->precio->moneda_venta_dolar != null)
                            u$s {{ $propiedad->precio->moneda_venta_dolar }}
                            @elseif($propiedad->precio && $propiedad->precio->moneda_venta_pesos && $propiedad->precio->moneda_venta_pesos != null)
                            $ {{ $propiedad->precio->moneda_venta_pesos }}
                            @endif
                        </td>
                        @endif

                        @if (in_array('clausula venta', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->clausula_de_venta }}</td>
                        @endif
                        @if (in_array('descripcion', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->descipcion_propiedad }}</td>
                        @endif
                        @if (in_array('llave', $campos) && $sector === 'Venta')
                        <td>
                            @if ($propiedad->llave > 0)
                            SI
                            @else
                            NO
                            @endif
                        </td>
                        @endif
                        @if (in_array('cartel', $campos))
                        <td>{{ $propiedad->cartel ?? '' }}</td>
                        @endif

                        @if (in_array('autorizacion', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->autorizacion_venta ?? '' }}</td>
                        @endif
                        @if (in_array('compartida', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->comparte_venta ?? '' }}</td>
                        @endif
                        @if (in_array('foto', $campos))
                        <td>
                            @if ($propiedad->fotos && $propiedad->fotos->isNotEmpty())
                            SI
                            @else
                            NO
                            @endif
                        </td>
                        @endif

                        @if (in_array('video', $campos))
                        <td>
                            @if ($propiedad->video && $propiedad->video->isNotEmpty())
                            SI
                            @else
                            NO
                            @endif
                        </td>
                        @endif

                        @if (in_array('documentacion', $campos))
                        <td>
                            @if ($propiedad->documentacion && $propiedad->documentacion->isNotEmpty())
                            SI
                            @else
                            NO
                            @endif
                        </td>
                        @endif
                        @if (in_array('reel', $campos) && $sector === 'Venta')
                        @if ($propiedad->reel)
                        <td>SI</td>
                        @else
                        <td>NO</td>
                        @endif
                        @endif
                        @if (in_array('flyer', $campos) && $sector === 'Venta')
                        @if ($propiedad->flyer)
                        <td>SI</td>
                        @else
                        <td>NO</td>
                        @endif
                        @endif
                        @if (in_array('captador', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->captador_int ?? '' }}</td>
                        @endif
                        @if (in_array('zonaprop', $campos) && $sector === 'Venta')
                        @if ($propiedad->zona_prop)
                        <td>SI</td>
                        @else
                        <td>NO</td>
                        @endif
                        @endif
                        @if (in_array('web', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->web ?? '' }}</td>
                        @endif
                        @if (in_array('vendedor', $campos) && $sector === 'Venta')
                        <td>{{ $propiedad->asesor ?? '' }}</td>
                        @endif



                        @if (in_array('usuario', $campos))
                        <td>{{ $propiedad->username ?? '-' }}</td>
                        @endif

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($pertenece === 'estadoPropietario')
        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        <th>Codigo Alquiler</th>
                        <th>Folio</th>
                        <th>Propietario</th>
                        <th>Direccion</th>
                        <th>Piso / Depto</th>
                        <th>Zona</th>
                        <th>Dormitorios</th>
                        <th>Cochera</th>
                        <th>Inmueble</th>
                        <th>Precio</th>
                        <th>Cartel</th>
                        <th>Foto</th>
                        <th>Documentacion</th>
                        <th>Videos</th>
                    </tr>
                </thead>
                <tbody class="listado_tabla">
                    <!--  {{ $propiedades }} -->
                    @foreach ($propiedades as $propiedad)
                    <!--  {{ $propiedad->propietarios }} -->
                    <tr>
                        <td>{{ $propiedad->cod_alquiler }}</td>
                        <td>
                            @foreach ($propiedad->folios as $folio)
                            {{ $folio->folio }}
                            @if ($folio->empresa->nombre === 'Atilio')
                            CENT /
                            @elseif($folio->empresa->nombre === 'Dolly')
                            CAN /
                            @elseif($folio->empresa->nombre === 'Flor')
                            TRIB
                            @else
                            @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach ($propiedad->propietarios as $propietario)
                            @if($propietario->pivot->baja === 'no')
                            {{ $propietario->apellido ?? '' }}, {{ $propietario->nombre ?? '' }}
                            @endif
                            @endforeach
                        </td>
                        <td>
                            {{ $propiedad->calle->name ?? '' }} {{ $propiedad->numero_calle ?? '' }}
                        </td>
                        <td>
                            @if ($propiedad->piso != null)
                            Piso: {{ $propiedad->piso ?? '' }}
                            @endif
                            @if ($propiedad->departamento != null)
                            Depto: {{ $propiedad->departamento ?? '' }}
                            @endif
                        </td>
                        <td>
                            {{ $propiedad->zona->name ?? '' }}
                        </td>
                        <td>
                            {{ $propiedad->cantidad_dormitorios ?? '' }}
                        </td>
                        <td>
                            {{ $propiedad->cochera ?? '' }}
                        </td>
                        <td>
                            {{ $propiedad->tipoInmueble->inmueble ?? '-' }}
                        </td>
                        @if ($sector === 'Alquiler')
                        <td>
                            @if ($propiedad->precio)
                            @if ($propiedad->precio->moneda_alquiler_pesos !== null)
                            $ {{ $propiedad->precio->moneda_alquiler_pesos }}
                            @elseif($propiedad->precio->moneda_alquiler_dolar !== null)
                            u$d {{ $propiedad->precio->moneda_alquiler_dolar }}
                            @endif
                            @endif
                        </td>
                        @elseif($sector === 'Venta')

                        <td>
                            @if ($propiedad->precio)
                            @if ($propiedad->precio->moneda_venta_dolar !== null)
                            u$d {{ $propiedad->precio->moneda_venta_dolar }}
                            @elseif($propiedad->precio->moneda_venta_pesos !== null)
                            $ {{ $propiedad->precio->moneda_venta_pesos }}
                            @endif
                            @endif
                        </td>
                        @endif
                        <td>
                            {{ $propiedad->cartel }}
                        </td>
                        <td>
                            @if (!empty($propiedad->foto))
                            SI
                            @else
                            NO
                            @endif
                        </td>

                        <td>
                            @if (!empty($propiedad->documentacion))
                            SI
                            @else
                            NO
                            @endif
                        </td>
                        <td>

                            @if (!empty($propiedad->video))
                            SI
                            @else
                            NO
                            @endif
                        </td>


                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($pertenece === 'ofrecimientoVenta')
        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        <th>
                            Codigo
                        </th>
                        <th>
                            Direccion
                        </th>
                        <th>
                            Piso / Depto
                        </th>
                        <th>
                            Cant Consultas
                        </th>
                        <th>
                            Cant Ofrecimientos
                        </th>
                        <th>
                            Cant Muestras
                        </th>
                    </tr>
                </thead>
                <tbody class="listado_tabla">
                    @foreach($query as $q)
                    <tr>
                        <td>
                            {{ $q->cod_venta }}
                        </td>
                        <td>
                            {{ $q->calle }} {{ $q->numero_calle }}
                        </td>
                        <td>
                            @if($q->piso != null && $q->departamento != null)
                            Piso: {{ $q->piso ?? '' }} / Depto: {{ $q->departamento ?? '' }}
                            @elseif($q->piso == null && $q->departamento != null)
                            Depto: {{ $q->departamento ?? '' }}
                            @elseif($q->piso != null && $q->departamento == null)
                            Piso: {{ $q->piso ?? '' }}
                            @endif
                        </td>
                        <td>
                            {{ $q->total_consultas }}
                        </td>
                        <td>
                            {{ $q->total_ofrecimientos }}
                        </td>
                        <td>
                            {{ $q->total_muestras }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        @elseif($pertenece === 'devoluciones')
        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        <th>
                            Cliente
                        </th>
                        <th>
                            Telefono
                        </th>
                        <th>
                            Fecha Consulta
                        </th>
                        <th>
                            Mensaje
                        </th>
                        <th>
                            Fecha Devolucion
                        </th>
                        <th>
                            Mensaje
                        </th>
                        <th>
                            Usuario
                        </th>
                    </tr>
                </thead>
                <tbody class="listado_tabla">
                    @foreach($datosTotales as $q)
                    <tr>
                        <td>
                            {{ $q->cliente->nombre ?? ''}}
                        </td>
                        <td>
                            {{ $q->telefono ?? ''}}
                        </td>
                        <td>
                            {{$q->fecha_hora ?? ''}}
                        </td>
                        <td>
                            {{$q->referencia ?? ''}}
                        </td>
                        <td>
                            {{$q->fecha_devolucion ?? ''}}
                        </td>
                        <td>
                            {{$q->devolucion ?? ''}}
                        </td>
                        <td>
                            {{$q->nombre_usuario ?? ''}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        @elseif($pertenece === 'criteriosActivos')
        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        <th>Cliente</th>
                        <th>telefono</th>
                        <th>Tipo Inmueble</th>
                        <th>Categoria</th>
                        <th>Zona</th>
                        <th>Cant. Dormitorios</th>
                        <th>Cochera</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody class="listado_tabla">
                    @foreach ($criterios_vendedor as $criterio)

                    <tr>
                        <td>{{ $criterio->cliente->nombre ?? '-' }}</td>
                        <td>{{ $criterio->cliente->telefono ?? '-' }}</td>
                        <td>{{ $criterio->tipoInmueble->inmueble ?? '' }}</td>
                        <td>{{ $criterio->id_categoria ?? '-' }}</td>
                        <td>{{ $criterio->zona->name ?? '' }}</td>
                        <td>{{ $criterio->cant_dormitorios ?? '-' }}</td>
                        <td>{{ $criterio->cochera ?? '-' }}</td>
                        <td>{{ $criterio->precio_hasta ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        @endif
    </div>

</body>

</html>
