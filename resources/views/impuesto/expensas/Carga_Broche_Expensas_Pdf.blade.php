<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Expensas</title>
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
            padding-right: 1px;
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
        }

        tbody tr:nth-child(even) {
            background-color: rgba(95, 95, 95, 0.3);
            font-size: 12px;
        }
    </style>
</head>

<body>
    @php
        // Agrupar resultados por administrador (nombre)
        $agrupadoPorAdmin = collect($resultado)->groupBy('nombre');
    @endphp

    @foreach ($agrupadoPorAdmin as $nombreAdmin => $items)
        @php
            // Obtener información del primer item para datos del administrador
            $primerItem = $items->first();
            $fecha = \Carbon\Carbon::parse($primerItem->vencimiento);
            $anio = $fecha->format('Y');
            $mes = $fecha->format('m');

            // Calcular total del administrador
            $totalAdmin = $items->sum('total');
        @endphp

        <div class="admin-section">
            {{-- Header con logo (solo en la primera página) --}}
            @if ($loop->first)
                <table class="header-table">
                    <tr>
                        <td>
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                alt="logo">
                        </td>
                    </tr>
                </table>
            @endif

            {{-- Información del Administrador --}}
            <h2>{{ $nombreAdmin }}</h2>
            <p>
                <strong>CUIT:</strong> {{ $primerItem->cuit }} &nbsp;&nbsp; &nbsp;&nbsp;
                <strong>Dirección:</strong> {{ $primerItem->direccion_administra }} {{ $primerItem->altura_administra }}
            </p>
            <p>
                <strong>Contacto:</strong> {{ $primerItem->contacto }} &nbsp;&nbsp; &nbsp;&nbsp;
                @if ($primerItem->pagina_web)
                    <strong>Web:</strong> {{ $primerItem->pagina_web }}
                @endif
            </p>
            <p>
                <strong>Período:</strong> {{ $mes }} / {{ $anio }} &nbsp;&nbsp;
                <strong>Total:</strong> ${{ number_format($totalAdmin, 2, ',', '.') }}
            </p>

            <hr>

            @php
                $id_edificio = null;
                $itemsAgrupados = $items->groupBy('id_edificio');
            @endphp

            @foreach ($itemsAgrupados as $edificioId => $itemsEdificio)
            {{-- @dd($itemsEdificio);  --}}
            @php
                $primerItem = $itemsEdificio->first();
            @endphp
            <p>
                <strong>Edificio:</strong> {{ $primerItem->nombre_consorcio }}&nbsp;&nbsp;
                <strong>Dirección:</strong> {{ $primerItem->direccion_edificio }} {{ $primerItem->altura_edificio }}
            </p>
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Piso</th>
                            <th>Departamento</th>
                            <th>Período</th>
                            <th>Ordinaria</th>
                            <th>Extraordinaria</th>
                            <th>Importe</th>
                            <th>Vencimiento</th>
                           {{--  <th>Observaciones</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemsEdificio as $item)
                            <tr>
                                <td style="font-size: 15px;">{{ $item->folio }}</td>
                                <td style="font-size: 15px;">{{ $item->piso }}</td>
                                <td style="font-size: 15px;">{{ $item->depto }}</td>
                                <td style="font-size: 15px;">{{ $item->periodo }}/{{ $item->anio }}</td>
                                <td style="font-size: 15px;">$ {{ number_format($item->ordinaria, 2, ',', '.') }}</td>
                                <td style="font-size: 15px;">$ {{ number_format($item->extraordinaria, 2, ',', '.') }}</td>
                                <td style="font-size: 15px;">$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                <td style="font-size: 15px;">
                                    {{ \Carbon\Carbon::parse($item->vencimiento)->format('d/m/Y') }}</td>
                               {{--  <td style="font-size: 15px;">{{ $item->observaciones }}</td> --}}
                            </tr>
                        @endforeach

                        {{-- Fila de total del edificio --}}
                        <tr class="total-row">
                            <td colspan="6" style="text-align: right;">TOTAL {{ $nombreAdmin }}</td>
                            <td>${{ number_format($itemsEdificio->sum('total'), 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>

        {{-- Salto de página entre administradores (excepto en el último) --}}
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>