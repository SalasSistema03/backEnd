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

@if ($pertenece === 'listadoAgenda')

    <body>
        <div class="row">
            <div class="col-3">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>
            <div class="col-9 d-flex align-items-center justify-content-center">
                <div class="listado_agenda_titulo_general">Agenda {{ $sectorNombre }} - {{ $usuarioNombre }} Desde
                    {{ \Carbon\Carbon::parse($rangoFechas[0])->format('d/m/Y') }} Hasta
                    {{ \Carbon\Carbon::parse($rangoFechas[1])->format('d/m/Y') }} - {{ $estado }}</div>

            </div>
        </div>

        @php
            $fechaAnterior = null;
        @endphp


        <table>
            <thead class="">
                <tr class="listado_agenda_titulo text-center">
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Descripción</th>
                    @if ($sectorNombre === 'Ventas' || $sectorNombre === 'Alquiler')
                        <th>Cliente</th>
                        <th>Telefono</th>
                        <th>Cod Propiedad</th>
                        <th>Direccion Prop</th>
                    @endif
                    @if ($estado === 'Inactivo')
                        <th>Eliminado por</th>
                        <th>Motivo</th>
                    @endif
                    <th>-</th>


                </tr>
            </thead>
            <tbody class = "listado_agenda_body">
                @foreach ($datos as $item)
                    <tr>
                        <td class="listado_agenda_texto_una_linea p-1 text-center">
                            {{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</td>
                        <td class="listado_agenda_texto_una_linea p-1 text-center">
                            {{ \Carbon\Carbon::parse($item['hora_inicio'])->format('H:i') }} Hs.</td>
                        <td class=" p-1">{{ $item['descripcion'] ?? '-' }}</td>
                        @if ($sectorNombre === 'Ventas' || $sectorNombre === 'Alquiler')
                            <td class = "listado_agenda_texto_una_linea p-1 text-center">
                                {{ $item['datos_cliente']['nombre'] ?? '-' }}</td>
                            <td class="listado_agenda_texto_una_linea p-1 text-center">
                                {{ $item['datos_cliente']['telefono'] ?? '-' }}</td>
                            @if ($sectorNombre === 'Ventas')
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['datos_propiedad']['cod_venta'] ?? '-' }}</td>
                            @endif
                            @if ($sectorNombre === 'Alquiler')
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['datos_propiedad']['cod_alquiler'] ?? '-' }}</td>
                            @endif
                            <td class="listado_agenda_texto_una_linea p-1 text-center">
                                {{ $item['datos_propiedad']['calle']['name'] ?? '-' }}
                                {{ $item['datos_propiedad']['numero_calle'] ?? '' }}</td>
                        @endif
                        @if ($estado === 'Inactivo')
                            <td class="listado_agenda_texto_una_linea p-1 text-center">
                                {{ $item['quien_borro'] ?? '-' }}</td>
                            <td class=" p-1">{{ $item['motivo'] ?? '-' }}</td>
                        @endif
                        <td class="listado_agenda_texto_una_linea p-1 text-center">{{ $item['creado_por'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    @elseif($pertenece === 'AgendaMuestra')
        <div class="row">
            <div class="col-3">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>
            <div class="col-6 d-flex align-items-center justify-content-center">
                <div class="listado_agenda_titulo_general">Agenda</div>
            </div>
            <div class="col-3 d-flex align-items-center justify-content-center">
                <table>
                    <thead>
                        <tr>
                            <th>
                                Asesor
                            </th>
                            <th>
                                Visitas
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td> nombre

                            </td>
                            <td> cantidad

                            </td>
                        </tr>

                    </tbody>

                </table>


            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 text-center">
                <table>
                    <thead>
                        <tr class="listado_agenda_titulo text-center">
                            <th>Codigo</th>
                            <th>Direccion</th>
                            <th>Cliente</th>
                            <th>Telefono</th>
                            <th>Fecha</th>
                            <th>Quien Agendo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datos as $item)
                            <tr>
                                {{ $sectorNombre }}
                                <td>{{ $item['propiedad']['cod_alquiler'] ?? '' }}

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endif


</body>

</html>
