<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
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
                Listado
                <br>
            </div>

            <hr>
        </div>

        <div class="col-md-12">
            <table class="table table-striped w-100">
                <thead class="listado_tabla_titulo">
                    <tr>
                        @if(in_array('cod_alquiler', $campos)) <th>Código Alquiler</th> @endif
                        @if(in_array('folio', $campos)) <th>Folio / Empresa</th> @endif
                        @if(in_array('direccion', $campos)) <th>Dirección</th> @endif
                        @if(in_array('zona', $campos)) <th>Zona</th> @endif
                        @if(in_array('p_d', $campos)) <th>Piso / Depto</th> @endif
                        @if(in_array('dormitorio', $campos)) <th>Dormitorios</th> @endif
                        @if(in_array('cochera', $campos)) <th>Cochera</th> @endif
                        @if(in_array('inmueble', $campos)) <th>Inmueble</th> @endif
                        @if(in_array('estado', $campos)) <th>Estado</th> @endif
                        @if(in_array('precio', $campos)) <th>Precio</th> @endif
                        @if(in_array('cartel', $campos)) <th>Cartel</th> @endif
                        @if(in_array('foto', $campos)) <th>Foto</th> @endif
                        @if(in_array('video', $campos)) <th>Videos</th> @endif
                        @if(in_array('documentacion', $campos)) <th>Documentación</th> @endif
                        @if(in_array('usuario', $campos)) <th>Usuario</th> @endif
                    </tr>
                </thead>

                <tbody class="listado_tabla">
                    @foreach ($propiedades as $propiedad)
                        <tr>

                            @if(in_array('cod_alquiler', $campos))
                                <td>{{ $propiedad->cod_alquiler ?? '' }}</td>
                            @endif

                            @if(in_array('folio', $campos))
                                <td>
                                    @forelse ($propiedad->folios ?? [] as $ep)
                                        {{ $ep->folio ?? '' }}

                                        @if($ep->empresa->nombre == 'Dolly')
                                            CAN
                                        @elseif($ep->empresa->nombre == 'Flor')
                                            TRIB
                                        @endif

                                        @if (!$loop->last)<br>@endif
                                    @empty
                                        114
                                    @endforelse
                                </td>
                            @endif

                            @if(in_array('direccion', $campos))
                                <td>
                                    {{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                                </td>
                            @endif

                            @if(in_array('zona', $campos))
                                <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                            @endif

                            @if(in_array('p_d', $campos))
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

                            @if(in_array('dormitorio', $campos))
                                <td>{{ $propiedad->cantidad_dormitorios ?? '' }}</td>
                            @endif

                            @if(in_array('cochera', $campos))
                                <td>{{ $propiedad->cochera ?? '' }}</td>
                            @endif

                            @if(in_array('inmueble', $campos))
                                <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                            @endif

                            @if(in_array('estado', $campos))
                                <td>{{ $propiedad->estadoAlquiler->name ?? '' }}</td>
                            @endif

                            @if(in_array('precio', $campos))
                                <td style="white-space: nowrap;">
                                    @if ($propiedad->precio && $propiedad->precio->moneda_alquiler_pesos && $propiedad->precio->moneda_alquiler_pesos != 0)
                                        $ {{ $propiedad->precio->moneda_alquiler_pesos }}
                                    @elseif($propiedad->precio && $propiedad->precio->moneda_alquiler_dolar != 0)
                                        u$s {{ $propiedad->precio->moneda_alquiler_dolar }}
                                    @endif
                                </td>
                            @endif

                            @if(in_array('cartel', $campos))
                                <td>{{ $propiedad->cartel ?? '' }}</td>
                            @endif

                            @if(in_array('foto', $campos))
                                <td>
                                    @if ($propiedad->fotos && $propiedad->fotos->isNotEmpty())
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
                            @endif

                            @if(in_array('video', $campos))
                                <td>
                                    @if (!empty($propiedad->video))
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
                            @endif

                            @if(in_array('documentacion', $campos))
                                <td>
                                    @if ($propiedad->documentacion && $propiedad->documentacion->isNotEmpty())
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
                            @endif

                            @if(in_array('usuario', $campos))
                                <td>{{ $propiedad->username ?? '-' }}</td>
                            @endif

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
