<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pdfStyles.css') }}">

</head>

<body>
    @foreach ($broches as $index => $broche)
        <div class ="row ">
        <div class="header col-12 row">
            {{-- mi imagen esta dentro de public/image --}}
            <div class= "col-6">
                <img src="{{ public_path('image/logo.png') }}" class="logo">
            </div>
            <div class="col-6">
                <div>TGI - Broche {{ $broche['num_broche'] }}</div>
            </div>
        </div>

        <div class="col-12 row mt-1 mb-1 impuestos_datos_broche">
            <div class="col-6 d-flex justify-content-end align-items-center">
                Periodo:
                {{ str_pad($broche['items'][0]['periodo_mes'], 2, '0', STR_PAD_LEFT) }}/{{ $broche['items'][0]['periodo_anio'] }}
            </div>
            <div class="col-6 d-flex justify-content-start align-items-center">
                Total:
                ${{ number_format($broche['total'], 2, ',', '.') }}
            </div>
        </div>

        <table class="table-responsive table table-striped">
            <thead class= " impuestos_tabla_titulo">
                <tr>
                    <th>Folio</th>
                    <th>Importe</th>
                    <th>Vencimiento</th>
                    <th>Comienza</th>
                    <th>Finaliza</th>
                </tr>
            </thead>
            <tbody class="impuestos_tabla">
                @foreach ($broche['items'] as $item)
                    <tr>
                        <td>{{ $item['folio'] }}</td>
                        <td>${{ number_format($item['importe'], 2, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['fecha_vencimiento'])->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['comienza'])->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['rescicion'])->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @if (!$loop->last)

        <div class="page-break"></div>
        @endif

    @endforeach
</body>

</html>
