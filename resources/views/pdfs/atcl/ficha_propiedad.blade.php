<!DOCTYPE html>
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
    <div class="row ">
        <div class="header col-12 row">
            {{-- mi imagen esta dentro de public/image --}}
            <div class="col-3">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>
            <div class="col-9 text-end">
                Ficha de Propiedad en {{-- -{{  $ubicacion}}- --}}

                {{-- 1. Condicional corregido para el título --}}
                @if ($ubicacion == "'A'")
                    Alquiler - {{ $propiedad['cod_alquiler'] ?? '' }}
                @elseif($ubicacion == 'V')
                    Venta - {{ $propiedad['cod_venta'] ?? '' }}
                @elseif($ubicacion == "'AR'")
                    Reserva


                    {{-- 2. El bloque de la ficha ahora está afuera para que se renderice siempre --}}
                    <div class="col-12 row ficha-propiedad_contenido text-start">
                        <div class="col-12 text-end">
                            Codigo: {{ $propiedad['cod_alquiler'] ?? '' }}
                        </div>

                        <div class="col-12 text-end">
                            @foreach ($propiedad['folios'] as $folio)
                                <span>Folio: </span>
                                @if ($folio['empresa_id'] == '1')
                                    {{ $folio['folio'] ?? '' }}
                                @elseif($folio['empresa_id'] == '2')
                                    Can {{ $folio['folio'] ?? '' }}
                                @elseif($folio['empresa_id'] == '3')
                                    Trib {{ $folio['folio'] ?? '' }}
                                @endif
                                <br> {{-- Un salto de línea por cada folio --}}
                            @endforeach
                        </div>
                    </div>
                @endif
                <br>
            </div>
            <hr>
        </div>
        <div class="col-4 row p-0 m-0">
            <div class=" card col-12 row p-0 m-0">
                <div class=" card-header col-12 text-center ficha-propiedad_header pt-2 pb-2">
                    🗒️ Datos de la Propiedad
                </div>
                <div class="card-body col-12 ">
                    <div class="row ">
                        <div class="col-12 ficha-propiedad_titulos">
                            📍 DIRECCION
                        </div>
                        <div class="col-12 ficha-propiedad_contenido">
                            {{ $propiedad['calle']['name'] ?? '' }} {{ $propiedad['numero_calle'] ?? '' }}

                        </div>
                        <div class="col-12 ficha-propiedad_contenido">
                            @if (isset($propiedad['piso']) && $propiedad['piso'])
                                Piso {{ $propiedad['piso'] }}
                            @endif
                            @if (isset($propiedad['departamento']) && $propiedad['departamento'])
                                Depto {{ $propiedad['departamento'] }}
                            @endif
                        </div>
                        <div class="col-12 ficha-propiedad_contenido">
                            {{ $propiedad['zona']['name'] ?? '' }}
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-12 ficha-propiedad_titulos">
                            🏠 TIPO INMUEBLE
                        </div>
                        <div class="col-12 ficha-propiedad_contenido text-center">
                            {{ $propiedad['tipo_inmueble']['inmueble'] }}
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-12 ficha-propiedad_titulos">
                            🔧 SERVICIOS
                        </div>
                        <div class="col-12 ficha-propiedad_contenido text-center">
                            @if (isset($propiedad['asfalto']) && $propiedad['asfalto'] == 'SI')
                                Asfalto
                            @endif
                            @if (isset($propiedad['gas']) && $propiedad['gas'] == 'SI')
                                • Gas
                            @endif
                            @if (isset($propiedad['cloaca']) && $propiedad['cloaca'] == 'SI')
                                • Cloaca
                            @endif
                            @if (isset($propiedad['agua']) && $propiedad['agua'] == 'SI')
                                • Agua
                            @endif
                            {{-- @if (isset($propiedad['ph']) && $propiedad['ph'] == 'SI')
                                • PH
                            @endif --}}
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-12 ficha-propiedad_titulos">
                            🚗 COCHERA
                        </div>
                        <div class="col-12 ficha-propiedad_contenido text-center">
                            @if (isset($propiedad['cochera']) && $propiedad['cochera'] == 'SI')
                                Sí - N° {{ $propiedad['numero_cochera'] ?? '' }}
                            @else
                                No
                            @endif
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-6 ficha-propiedad_titulos">
                            🛏️ DORMITORIOS
                        </div>
                        <div class="col-6 ficha-propiedad_titulos">
                            🛁 BAÑOS
                        </div>
                        <div class="col-6 ficha-propiedad_contenido text-center">
                            {{ $propiedad['cantidad_dormitorios'] ?? '' }}
                        </div>
                        <div class="col-6 ficha-propiedad_contenido text-center">
                            {{ $propiedad['banios'] ?? '' }}
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-6 ficha-propiedad_titulos">
                            📐 M² LOTE
                        </div>
                        <div class="col-6 ficha-propiedad_titulos">
                            📏 M² CUBIERTOS
                        </div>
                        <div class="col-6 ficha-propiedad_contenido text-center">
                            {{ $propiedad['mLote'] ?? '' }} m²
                        </div>

                        <div class="col-6 ficha-propiedad_contenido text-center">
                            {{ $propiedad['mCubiertos'] ?? '' }} m²
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-12 ficha-propiedad_titulos">
                            💲 VALOR
                        </div>
                        <div class="col-12 ficha-propiedad_contenido_valor text-center">
                            @if ($ubicacion == "'A'" || $ubicacion == "'AR'")
                                $ {{ $propiedad['precio_actual']['moneda_alquiler_pesos'] ?? '' }}
                            @else
                                U$S {{ $propiedad['precio_actual']['moneda_venta_dolar'] ?? '' }}
                            @endif
                        </div>
                    </div>
                    <hr class="mt-1 mb-1">
                    <div class="row">
                        <div class="col-12 ficha-propiedad_titulos">
                            📰 DESCRIPCIÓN
                        </div>
                        <div class="col-12 ficha-propiedad_contenido">
                            {{ $propiedad['descipcion_propiedad'] ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            @if ($ubicacion != "'AR'")
                <div class="col-12 pt-3 pb-2 m-0 text-center">
                    <div class="ficha-propiedad_comentario">
                        <label for="comentario">
                            ¿Qué te pareció tu
                            visita? ¡Contanos!
                        </label>
                    </div>
                    <div class="m-3 d-flex justify-content-center align-items-center">
                        <img class="img-fluid" src="{{ public_path('image/qrFichaPropiedad.jpeg') }}">
                    </div>

                </div>
            @elseif($ubicacion == "'AR'")
                <div class=" card col-12 row p-0 m-0 mt-2">
                    <div class=" card-header col-12 text-center ficha-propiedad_header pt-2 pb-2">
                        🗒️ Datos Reserva
                    </div>
                    <div class="card-body col-12 ">
                        <div class="row ">
                            <div class="col-12 ficha-propiedad_titulos">
                                CONDICION
                            </div>
                            <div class="col-12 ficha-propiedad_contenido" style="font-size: 7px;">
                                {{ $propiedad['condicion'] ?? '' }}
                            </div>
                        </div>
                        <hr class="mt-1 mb-1">
                        <div class="row">
                            <div class="col-12 ficha-propiedad_titulos">
                                PROPIETARIOS
                            </div>
                            <div class="col-12 row ficha-propiedad_contenido" style="font-size: 7px;">
                                @foreach ($propiedad['propietarios'] as $propietario)
                                    <br>
                                    <div class="col-12">{{ $propietario['nombre'] }} {{ $propietario['apellido'] }}
                                    </div>
                                    <div class="col-12">Dir: {{ $propietario['calle'] }}
                                        {{ $propietario['numero_calle'] }}</div>
                                    @foreach ($propietario['telefonos'] as $telefono)
                                        <div class="col-12">Tel: {{ $telefono['phone_number'] }}</div>
                                    @endforeach
                                    <hr>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-8">
            {{-- fotos --}}
            @foreach ($fotosOrdenadas as $foto)
                <div class="ficha-propiedad_foto p-2">
                    <img src="{{ 'http://10.10.10.191' . $foto }}" class="img-fluid">
                </div>
            @endforeach

        </div>

    </div>

</body>

</html>
