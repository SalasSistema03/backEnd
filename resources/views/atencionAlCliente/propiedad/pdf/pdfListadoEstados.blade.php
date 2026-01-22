<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        thead th {
            white-space: nowrap;
        }

        thead td {
            white-space: nowrap;
        }

        h1 {

            margin: 0;
            font-size: 24px;
            padding-left: 0px;
            /* Ajusta el espacio entre la imagen y el título */
            color: rgba(0, 175, 154, 0.96)
        }

        .header-table {
            width: 70%;
            margin-bottom: 20px;
            border: none;
            border-collapse: collapse;
            color: rgb(0, 85, 185)
        }

        .header-table td {
            vertical-align: middle;
            text-align: start;
            border: none;

        }

        .header-table img {
            height: 50px;
            /* padding-left: 1px; */
            /* Ajusta el espacio entre la imagen y el título */
            padding-right: 1px;
            /* Ajusta el espacio entre la imagen y el título */

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }

        thead {
            background-color: rgb(0, 85, 185);
            color: rgba(255, 255, 255, 0.96);
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
        }

        tbody tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.96);
            font-size: 12px;
            /* Color para los renglones impares */
        }

        tbody tr:nth-child(even) {
            background-color: rgba(241, 241, 241, 0.96);
            font-size: 12px;
            /* Color para los renglones pares */
        }

        .contenedor-listado-conversaciones {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }

        .titulo-contenedor-listado-conversaciones {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }

        .titulo-nombre-cliente {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .item-listado-conversaciones {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }

        .criterio-conversacion {
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.5;
        }

        .historial-conversaciones {
            margin: 15px 0 10px 0;
            font-size: 16px;
            color: #333;
        }

        .contenedor-conversaciones {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .listado-contenedor {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
            margin-bottom: 5px;
        }

        .parrafo-listado-conversaciones {
            margin: 0 0 5px 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .fecha-listado-conversaciones {
            color: #777;
            font-size: 12px;
        }

        .devolucion-listado-conversaciones {
            margin: 10px 0 0 0;
            color: #dc3545;
            font-weight: bold;
            font-size: 14px;
        }

        .sin-conversaciones {
            margin: 10px 0;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>

    @if ($pertenece == 'estadosAlquiler')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Propiedades en Alquiler</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Código Alquiler</th>
                                <th>Folio</th>
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
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propiedades as $propiedad)
                                <tr>


                                    <td>{{ $propiedad->cod_alquiler ?? '' }}</td>
                                    <td>{{ $propiedad->folio ?? '' }}</td>
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
                                    <td>{{ $usernames[$propiedad->last_modified_by] ?? '-' }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'estadoPropietarioA')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de propietarios en alquiler</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Código Alquiler</th>
                                <th>Folio</th>
                                <th>Propietario</th>
                                <th>Dirección</th>
                                <th>Zona</th>
                                <th>Piso / Depto</th>
                                <th>Dormitorios</th>
                                <th>Cochera</th>
                                <th>Inmueble</th>
                                <th>Precio</th>
                                <th>Cartel</th>
                                <th>Foto</th>
                                <th>Documentación</th>
                                <th>Videos</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propiedades as $propiedad)
                                <tr>
                                    <td>{{ $propiedad->cod_alquiler ?? '' }}</td>
                                    <td>{{ $propiedad->folio ?? '' }}</td>
                                    <td>
                                        @foreach ($propiedad->propietarios as $propietario)
                                            {{ strtoupper($propietario->apellido) ?? '' }},{{ strtoupper($propietario->nombre) ?? '' }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                                    </td>
                                    <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                                    <td>{{ $propiedad->piso ?? '' }} / {{ $propiedad->departamento ?? '' }}</td>
                                    <td>{{ $propiedad->cantidad_dormitorios ?? '' }}</td>
                                    <td>{{ $propiedad->cochera ?? '' }}</td>
                                    <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                                    <td style="white-space: nowrap;">
                                        @if ($propiedad->precio && $propiedad->precio->moneda_alquiler_pesos && $propiedad->precio->moneda_alquiler_pesos != 0)
                                            $ {{ $propiedad->precio->moneda_alquiler_pesos }}
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
                                    <td>
                                        @if ($propiedad->documentacion && $propiedad->documentacion->isNotEmpty())
                                            SI
                                        @else
                                            NO
                                        @endif
                                    </td>
                                    <td>-</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'estadoPropietarioV')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de propietarios en Venta</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Código Alquiler</th>
                                <th>Folio</th>
                                <th>Propietario</th>
                                <th>Dirección</th>
                                <th>Zona</th>
                                <th>Piso / Depto</th>
                                <th>Dormitorios</th>
                                <th>Cochera</th>
                                <th>Inmueble</th>
                                <th>Autorización</th>
                                <th>Precio</th>
                                <th>Cartel</th>
                                <th>Foto</th>
                                <th>Documentación</th>
                                <th>Videos</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propiedades as $propiedad)
                                <tr>
                                    <td>{{ $propiedad->cod_alquiler ?? '' }}</td>
                                    <td>{{ $propiedad->folio ?? '' }}</td>
                                    <td>
                                        @foreach ($propiedad->propietarios as $propietario)
                                            {{ strtoupper($propietario->apellido) ?? '' }},{{ strtoupper($propietario->nombre) ?? '' }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                                    </td>
                                    <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                                    <td>{{ $propiedad->piso ?? '' }} / {{ $propiedad->departamento ?? '' }}</td>
                                    <td>{{ $propiedad->cantidad_dormitorios ?? '' }}</td>
                                    <td>{{ $propiedad->cochera ?? '' }}</td>
                                    <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                                    <td>{{ $propiedad->autorizacion_venta ?? '' }}</td>
                                    <td style="white-space: nowrap;">
                                        @if ($propiedad->precio && $propiedad->precio->moneda_alquiler_pesos && $propiedad->precio->moneda_alquiler_pesos != 0)
                                            $ {{ $propiedad->precio->moneda_alquiler_pesos }}
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
                                    <td>
                                        @if ($propiedad->documentacion && $propiedad->documentacion->isNotEmpty())
                                            SI
                                        @else
                                            NO
                                        @endif
                                    </td>
                                    <td>-</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'PropiedadesxAsesorV')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Agenda de ventas</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>fecha</th>
                                <th>hora</th>
                                <th>descripcion</th>
                                <th>cliente</th>
                                <th>Codigo Venta</th>
                                <th>estado nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notas as $nota)
                                <tr>
                                    <td>{{ $nota->username }}</td>
                                    <td>{{ $nota->fecha }}</td>
                                    <td>{{ $nota->horaInicioFormatada }} / {{ $nota->horaFinFormatada }}</td>
                                    <td>{{ $nota->descripcion ?? 'Sin descripcion' }}</td>
                                    <td>{{ $nota->cliente }}</td>
                                    <td>{{ $nota->propiedad }}</td>
                                    <td>
                                        @if ($nota->activo == 1)
                                            Activa
                                        @else
                                            Baja
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'PropiedadesxAsesorA')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Agenda de Alquiler</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>fecha</th>
                                <th>hora</th>
                                <th>descripcion</th>
                                <th>cliente</th>
                                <th>Codigo Venta</th>
                                <th>estado nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notas as $nota)
                                <tr>
                                    <td>{{ $nota->username }}</td>
                                    <td>{{ $nota->fecha }}</td>
                                    <td>{{ $nota->horaInicioFormatada }} / {{ $nota->horaFinFormatada }}</td>
                                    <td>{{ $nota->descripcion ?? 'Sin descripcion' }}</td>
                                    <td>{{ $nota->cliente }}</td>
                                    <td>{{ $nota->propiedad }}</td>
                                    <td>
                                        @if ($nota->activo == 1)
                                            Activa
                                        @else
                                            Baja
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'criterios-activos')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Criterios Activos</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
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
                        <tbody>
                            {{-- 
                            @dump($criterios_vendedor->toArray()) --}}
                            @foreach ($criterios_vendedor as $criterio)
                                {{-- @dd($criterio) --}}
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
            </div>
        </div>
    @elseif ($pertenece == 'ofrecimiento')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Devoluciones</h1>
                                Desde: {{ Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} - Hasta:
                                {{ Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Direccion</th>
                                <th>Cant Consulta</th>
                                <th>Cant Ofrecimiento</th>
                                <th>Cant Muestra</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 
                            @dump($criterios_vendedor->toArray()) --}}
                            @foreach ($query as $querys)
                                {{-- @dd($criterio) --}}
                                <tr>
                                    <td>{{ $querys->cod_venta ?? '-' }}</td>
                                    <td>{{ $querys->calle ?? '-' }} {{ $querys->numero_calle ?? '-' }}</td>
                                    <td>{{ $querys->total_consultas ?? '' }}</td>
                                    <td>{{ $querys->total_ofrecimientos ?? '-' }}</td>
                                    <td>{{ $querys->total_muestras ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($pertenece == 'devoluciones')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Devoluciones Codigo: {{ $codigo }}</h1>
                                <p></p>
                                {{--   Desde: {{ Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} - Hasta:
                                {{ Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }} --}}
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Telefono</th>
                                <th>Fecha Consulta</th>
                                <th>Mensaje</th>
                                <th>Fecha Devolucion</th>
                                <th>Mensaje</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 
                            @dump($criterios_vendedor->toArray()) --}}
                            @foreach ($datosTotales as $datos)
                                {{-- @dd($criterio) --}}
                                <tr>
                                    <td>{{ $datos->cliente->nombre ?? '-' }}</td>
                                    <td>{{ $datos->cliente->telefono ?? '-' }}</td>
                                    <td>{{ $datos->fecha_hora ?? '-' }}</td>
                                    <td>{{ $datos->referencia ?? '-' }}</td>
                                    <td>{{ $datos->fecha_devolucion ?? '-' }}</td>
                                    <td>{{ $datos->devolucion ?? '-' }}</td>
                                    <td>{{ $datos->nombre_usuario ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($pertenece == 'conversaciones')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Encabezado con logo y título -->
                    <table class="header-table mb-4" {{--   style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" --}}>
                        <tr>
                            <td {{-- style="vertical-align: middle; padding: 10px; width: 100px;" --}}>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo" style="max-width: 80px; height: auto;">
                            </td>
                            <td {{-- style="vertical-align: middle; padding: 10px;" --}}>
                                <h1 {{-- style="margin: 0; font-size: 24px; font-weight: bold; color: #333;" --}}>Listado de
                                    Conversaciones</h1>
                            </td>
                        </tr>
                    </table>

                    <!-- Bucle por cliente: cada uno en una "tarjeta" simulada con divs para mejor compatibilidad en PDF -->

                    @foreach ($datosTotales as $idCliente => $items)
                        {{-- Obtener el nombre del cliente del primer elemento --}}
                        @php
                            $cliente = $items[0]['cliente'];
                        @endphp

                        <div class="contenedor-listado-conversaciones">
                            <div class="titulo-contenedor-listado-conversaciones">
                                <h2 class="titulo-nombre-cliente">{{ strtoupper($cliente->nombre) }}</h2>
                            </div>
                            <div style="padding: 15px;">
                                <!-- Lista de items (criterios de búsqueda) para este cliente -->
                                @foreach ($items as $item)
                                    <div class="item-listado-conversaciones">
                                        <p class="criterio-conversacion">
                                            <strong style="color: #555;">Fecha:</strong>&nbsp;
                                            {{ $item['cliente']->fecha_criterio_venta ?? '-' }} &nbsp;&nbsp;&nbsp;
                                            <strong style="color: #555;">Tipo Inmueble:</strong>&nbsp;
                                            {{ $item['cliente']->inmueble ?? '-' }} &nbsp;&nbsp;&nbsp;
                                            <strong style="color: #555;">Cant. Dormitorios:</strong>&nbsp;
                                            {{ $item['cliente']->cant_dormitorios ?? '-' }} &nbsp;&nbsp;&nbsp;
                                            <strong style="color: #555;">Precio Hasta:</strong>&nbsp;&nbsp;&nbsp;
                                            {{ $item['cliente']->precio_hasta ?? '-' }}
                                        </p>

                                        <!-- Historial de conversaciones: usando una lista estilizada -->
                                        @if ($item['historial_total']->count() > 0)
                                            <h5 class="historial-conversaciones">Historial
                                                de Conversaciones:</h5>
                                            <ul class="contenedor-conversaciones">
                                                @foreach ($item['historial_total'] as $historial)
                                                    <li class="listado-contenedor">
                                                        <p class="parrafo-listado-conversaciones">
                                                            {{ $historial->mensaje }}</p>
                                                        <small class="fecha-listado-conversaciones">
                                                            {{ $historial->fecha_hora }}</small>
                                                        @if ($historial->devolucion)
                                                            <p class="devolucion-listado-conversaciones">
                                                                {{ $historial->devolucion }} -
                                                                <small>{{ $historial->fecha_devolucion }}</small>
                                                            </p>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="sin-conversaciones">No hay
                                                conversaciones registradas.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif ($pertenece == 'listado-propiedades')
        <div class="row mx-3">
            <div class="col-md-12">
                <div class="container">
                    <!-- Usar una tabla para el encabezado -->
                    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1>Listado de Propiedades en Venta</h1>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <thead>
                            <tr style="font-size: 12px;">
                                @foreach ($propiedades->first()->campos_seleccionados as $campo)
                                    <th>{{ strtoupper($campo) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propiedades as $propiedad)
                                {{-- {{ dump($propiedad) }} --}}
                                <tr>
                                    @if (in_array('c_venta', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 10px; max-width: 100px;">
                                            {{ $propiedad->cod_venta ?? '' }}</td>
                                    @endif

                                    @if (in_array('direccion', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->calle ? $propiedad->calle->name . ' ' . $propiedad->numero_calle : '' }}
                                        </td>
                                    @endif

                                    @if (in_array('zona', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ strtoupper($propiedad->zona->name ?? '') }}
                                        </td>
                                    @endif

                                    @if (in_array('p_d', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            @if ($propiedad->piso == null || $propiedad->piso == 0)
                                                {{ $propiedad->departamento ?? '' }}
                                            @elseif($propiedad->departamento == null)
                                                {{ $propiedad->piso ?? '' }}
                                            @else
                                                {{ $propiedad->piso ?? '' }} / {{ $propiedad->departamento ?? '' }}
                                            @endif
                                        </td>
                                    @endif
                                    @if (in_array('inm', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->tipoInmueble->inmueble ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('ph', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->ph ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('e_venta', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->estado_venta->name ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('autorizacion', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->autorizacion_venta . ' ' . $propiedad->fecha_autorizacion_venta . ' ' . $propiedad->comentario_autorizacion }}
                                        </td>
                                    @endif

                                    @if (in_array('exclusivo', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->exclusividad_venta ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('condicionada', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->condicionado_venta ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('comparte', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->comparte_venta ?? '' }}
                                        </td>
                                    @endif

                                    @if (in_array('llave', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->llave . '  ' . $propiedad->comentario_llave }}
                                        </td>
                                    @endif

                                    @if (in_array('cartel', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->cartel . '  ' . $propiedad->comentario_cartel }}
                                        </td>
                                    @endif

                                    @if (in_array('precio', $propiedad->campos_seleccionados ?? []))
                                        <td style="white-space: nowrap; font-size: 9px;">
                                            @php
                                                $usd = $propiedad->precioActual->moneda_venta_dolar ?? 0;
                                                $ars = $propiedad->precioActual->moneda_venta_pesos ?? 0;
                                            @endphp

                                            @if ($usd && $ars)
                                                u$s {{ number_format($usd, 2, ',', '.') }}<br>
                                                $ {{ number_format($ars, 2, ',', '.') }}
                                            @elseif ($usd)
                                                u$s {{ number_format($usd, 2, ',', '.') }}
                                            @elseif ($ars)
                                                $ {{ number_format($ars, 2, ',', '.') }}
                                            @endif
                                        </td>
                                    @endif

                                    @if (in_array('propietario', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->propiedades_padron->nombre_completo ?? '' }}
                                        </td>
                                    @endif
                                    @if (in_array('folio', $propiedad->campos_seleccionados ?? []))
                                        <td style="font-size: 9px; max-width: 100px;">
                                            @if (!empty($propiedad->folio) && $propiedad->folio->isNotEmpty())
                                                @foreach ($propiedad->folio as $folio)
                                                    @if ($folio->empresa_id == 1)
                                                        {{ $folio->folio }}
                                                    @elseif ($folio->empresa_id == 2)
                                                        C-{{ $folio->folio }} 
                                                    @elseif ($folio->empresa_id == 3)
                                                        T-{{ $folio->folio }}
                                                    @endif
                                                @endforeach
                                            @endif
                                        </td>
                                    @else
                                        {{-- No hay folios --}}
                                    @endif

                                    @if(in_array('dormitorio', $propiedad->campos_seleccionados ?? []))
                                         <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->cantidad_dormitorios ?? '' }} 
                                        </td>
                                    @endif
                                    @if(in_array('cochera', $propiedad->campos_seleccionados ?? []))
                                         <td style="font-size: 9px; max-width: 100px;">
                                            {{ $propiedad->cochera ?? '' }} 
                                            @if($propiedad->numero_cochera)
                                                 - Nº {{ $propiedad->numero_cochera }}
                                            @endif
                                        </td>
                                    @endif
                                    {{-- @dd($propiedad); --}}
                                    @if(in_array('foto', $propiedad->campos_seleccionados ?? []))
                                         <td style="font-size: 9px; max-width: 100px;">
                                         @if($propiedad->fotos->isNotEmpty())
                                            Si
                                         @else
                                            No
                                         @endif
                                  
                                        </td>
                                    @endif
                                    @if(in_array('video', $propiedad->campos_seleccionados ?? []))
                                         <td style="font-size: 9px; max-width: 100px;">
                                            @if($propiedad->video->isNotEmpty())
                                            Si
                                            @else
                                            No
                                            @endif
                                            
                                        </td>
                                    @endif
                                    @if(in_array('documentacion', $propiedad->campos_seleccionados ?? []))
                                         <td style="font-size: 9px; max-width: 100px;">
                                            @if($propiedad->documentacion->isNotEmpty())
                                            Si
                                            @else
                                            No
                                            @endif
                                            
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</body>

</html>
