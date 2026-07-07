<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    {{-- 1. Inyectamos Bootstrap directamente desde el servidor (si lo tenés descargado) o usamos tus propios estilos --}}
    <style>
        /* Inyectamos tu CSS personalizado */
        {!! file_get_contents(public_path('css/pdfStyles.css')) !!}
        
        /* Opcional: Si necesitas el grid de Bootstrap, es mejor definir lo básico acá a mano para PDFs */
        .row { width: 100%; display: table; }
        .col-3 { width: 25%; display: table-cell; vertical-align: middle; }
        .col-7 { width: 58%; display: table-cell; vertical-align: middle; }
        .col-9 { width: 75%; display: table-cell; vertical-align: middle; }
        .col-12 { width: 100%; }
        .text-center { text-align: center; }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-center { justify-content: center; }
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
            <div class="col-7 d-flex align-items-center justify-content-center">
                <div class="listado_agenda_titulo_general">Agenda {{ $sectorNombre }} Desde
                    {{ \Carbon\Carbon::parse($rangoFechas[0])->format('d/m/Y') }} Hasta
                    {{ \Carbon\Carbon::parse($rangoFechas[1])->format('d/m/Y') }}</div>
            </div>
            <div class="col-2 d-flex align-items-center justify-content-center">
                <table class="listado_agenda_asesores">
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
                        @foreach ($conteoUsuarios as $conteo)
                            <tr>
                                <td>{{ $conteo['username'] }}</td>
                                <td>{{ $conteo['cantidad'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>


            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 text-center">
                <table>
                    <thead>
                        <tr class="listado_agenda_titulo text-center"> {{-- ya la tenés --}}
                            <th>Usuario</th>
                            <th>Codigo</th>
                            <th>Direccion</th>
                            <th>Cliente</th>
                            <th>Telefono</th>
                            <th>Fecha</th>
                            <th>Quien Agendo</th>
                        </tr>
                    </thead>
                    <tbody class="listado_agenda_body"> {{-- agregar esta clase --}}
                        @foreach ($datos as $item)
                            <tr>
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['usuario_id'] ?? '' }}</td>
                                @if ($sectorNombre == 'Alquiler')
                                    <td class="listado_agenda_texto_una_linea p-1 text-center">
                                        {{ $item['propiedad']['cod_alquiler'] ?? '' }}</td>
                                @elseif($sectorNombre == 'Venta')
                                    <td class="listado_agenda_texto_una_linea p-1 text-center">
                                        {{ $item['propiedad']['cod_venta'] ?? '' }}</td>
                                @endif
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['propiedad']['calle']['name'] ?? '' }}
                                    {{ $item['propiedad']['numero_calle'] ?? '' }}
                                </td>
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['cliente']['nombre'] ?? '' }}</td>
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['cliente']['telefono'] ?? '' }}</td>
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</td>
                                <td class="listado_agenda_texto_una_linea p-1 text-center">
                                    {{ $item['creado_por'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endif


</body>

</html>
