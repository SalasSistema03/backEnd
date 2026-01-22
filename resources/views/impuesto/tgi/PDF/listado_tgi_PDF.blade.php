<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de TGI</title>
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
            background-color: rgba(95, 95, 95, 0.3);
            font-size: 12px;
            /* Color para los renglones pares */
        }
    </style>
</head>

<body>
    @foreach ($data['broches'] as $index => $broche)
    @php
    $primerItem = $broche['items'][0];
    $anio = $primerItem->periodo_anio;
    $mes = str_pad($primerItem->periodo_mes, 2, '0', STR_PAD_LEFT);
    $total = number_format($broche['total'], 2, ',', '.');
    @endphp
    <table class="header-table">
                        <tr>
                            <td>
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('image/logo.png'))) }}"
                                    alt="logo">
                            </td>
                            <td>
                                <h1> TGI - Broche {{ $broche['num_broche'] }}</h1>
                            </td>
                        </tr>
                    </table>

    <p><strong>Perido:</strong> {{ $mes }}/{{ $anio }} &nbsp;&nbsp; <strong>Total del Broche:</strong> ${{ $total }} &nbsp;&nbsp;</p>

    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>Folio</th>
                <th>Importe</th>
                <th>Vencimiento</th>
                <th>Comienza</th>
                <th>Finaliza</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($broche['items'] as $item)
            <tr>
                <td style="font-size: 15px;" >{{ $item->folio }}</td>
                <td style="font-size: 15px;">{{ number_format($item->importe, 2, ',', '.') }}</td>
                <td style="font-size: 15px;">{{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}</td>

                <td>{{ \Carbon\Carbon::parse($item->comienza)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->rescicion)->format('d/m/Y') }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Salto de página excepto en el último broche --}}
    @if (!$loop->last)
    <div style="page-break-after: always;"></div>
    @endif
    @endforeach
</body>


</html>