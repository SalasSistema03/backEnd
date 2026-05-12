<html lang="es">


<head>
    <meta charset="UTF-8">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Inline the CSS instead of using file:// --}}
    <style>
        {!! file_get_contents(public_path('css/pdfStyles.css')) !!}
    </style>
</head>

<body>
    <div class="row mx-3">
        <div class="header col-12 row">
            {{-- mi imagen esta dentro de public/image --}}
            <div class= "col-3">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>
            <div class="col-9 text-end">
                Listado
                <br>
            </div>
            <hr>
        </div>

        <div class="col-md-12">
                <table class="table table-striped  w-100">
                    <thead class = "listado_tabla_titulo">
                        <tr>
                            <th>Código Alquiler</th>
                            <th>Folio / Empresa</th>
                            <th>Dirección</th>
                            <th>Zona</th>
                            <th>Piso / Depto</th>
                            <th>Dormitorios</th>
                            <th>Cochera</th>
                            <th>Inmueble</th>
                            <th>Precio</th>
                            <th>Cartel</th>
                            <th>Foto</th>
                            <th>Videos</th>
                            <th>Documentación</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="listado_tabla">

                        @foreach ($propiedades as $propiedad)
                            <tr>


                                <td>{{ $propiedad->cod_alquiler ?? '' }}</td>
                                <td>
                                    @forelse ($propiedad->folios ?? [] as $ep)
                                    {{ $ep->folio ?? '—' }} / {{ $ep->empresa->nombre ?? '—' }}
                                    @if (!$loop->last)<br>@endif
                                    @empty
                                        —
                                    @endforelse
                                </td>
                                
                                <td>{{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                                </td>
                                <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                                <td>
                                    @if ($propiedad->piso == null || $propiedad->piso == 0)
                                        {{ $propiedad->departamento ?? '' }}
                                    @elseif($propiedad->departamento == null)
                                        {{ $propiedad->piso ?? '' }}
                                    @else
                                        {{ $propiedad->piso ?? '' }} / {{ $propiedad->departamento ?? '' }}
                                    @endif
                                </td>
                                <td>{{ $propiedad->cantidad_dormitorios ?? '' }}</td>
                                <td>{{ $propiedad->cochera ?? '' }}</td>
                                <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                                <td style="white-space: nowrap;">
                                    @if ($propiedad->precio && $propiedad->precio->moneda_alquiler_pesos && $propiedad->precio->moneda_alquiler_pesos != 0)
                                        $ {{ $propiedad->precio->moneda_alquiler_pesos }}
                                    @else
                                        u$s {{ $propiedad->precio->moneda_alquiler_dolar }}
                                    @endif
                                </td>
                                <td>{{ $propiedad->cartel ?? '' }}</td>
                                <td>
                                    @if ($propiedad->fotos && $propiedad->fotos->isNotEmpty())
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
                                <td></td>
                                <td>
                                    @if ($propiedad->documentacion && $propiedad->documentacion->isNotEmpty())
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
                                <td>{{ $propiedad->username ?? '-' }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

        </div>
    </div>
</body>

</html>
