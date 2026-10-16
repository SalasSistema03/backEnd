<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        {
            ! ! file_get_contents(public_path('css/pdfStyles.css')) ! !
        }

        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
        }

        .tabla-masiva {
            margin-bottom: 0 !important;
        }

        .tabla-masiva th,
        .tabla-masiva td {
            font-size: 0.55rem;
            padding: 0.1rem 0.15rem !important;
            vertical-align: middle;
            text-align: center;
            line-height: 1;
            white-space: nowrap;
        }

        .tabla-masiva th {
            font-size: 0.5rem;
        }

        .tabla-individual th,
        .tabla-individual td {
            font-size: 0.8rem;
            padding: 0.2rem !important;
        }

        .cupon-container {
            border: 2px dashed #ccc;
            padding: 8px 10px;
            border-radius: 8px;
        }

        /* Añadido para forzar el salto de línea en las observaciones */
        .obs-text {
            white-space: normal !important;
            text-align: justify;
        }
    </style>
</head>

<body>
    @php
    $unidadClick = $data['unidad'] ?? [];
    $registroClick = $data['registro'] ?? [];
    $registroGeneral = $data['registro_general'] ?? [];
    $todos = $data['todos_los_registros'] ?? [];
    $detallado = $data['detallado'] ?? true;

    $periodo = $registroClick['periodo'] ?? '';
    [$anio, $mes] = array_pad(explode('-', $periodo), 2, '');

    // 1. CONCEPTOS TOTALES: Ahora incluye fondo_reserva
    $conceptos = ['tgi', 'agua', 'api', 'luz', 'seguro', 'limpieza', 'ascensor','internet', 'honorario', 'fondo_reserva', 'extraordinaria', 'otro'];

    // 2. CONCEPTOS REGULARES: Solo los comunes
    $conceptosRegulares = ['tgi', 'agua', 'api', 'luz', 'seguro', 'limpieza', 'ascensor','internet', 'honorario'];

    $totalGeneralEdificio = 0;
    foreach($conceptos as $c) {
    $totalGeneralEdificio += floatval($registroGeneral[$c] ?? 0);
    }

    $mesInt = intval($mes);
    $mesBimestre = ceil($mesInt / 2);
    $mesBimestreStr = str_pad($mesBimestre, 2, '0', STR_PAD_LEFT);
    $cuotaAgua = ($mesInt % 2 == 0) ? '(1/2)' : '(2/2)';

    // Sumatoria para el PDF Simple
    $totalOrdinarias = 0;
    foreach($conceptosRegulares as $c) {
    $totalOrdinarias += floatval($registroClick[$c] ?? 0);
    }

    // El total a pagar suma TODO (usando el array $conceptos)
    $totalCupon = 0;
    foreach($conceptos as $c) {
    if(isset($registroClick[$c]) && floatval($registroClick[$c]) > 0) {
    $totalCupon += floatval($registroClick[$c]);
    }
    }
    @endphp

    <div class="row m-0 w-100 justify-content-center">

        @if($detallado)
        <!-- ================= LADO IZQUIERDO (Tabla General) ================= -->
        <div class="col-8 pe-3 border-end">
            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                <div>
                    <img src="{{ public_path('image/Cardinal.png') }}" style="max-height: 25px;">
                </div>
                <div class="text-end">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.8rem;">Planilla General de Liquidación</h6>
                    <small class="text-muted" style="font-size: 0.65rem;">Período: {{ $mes }} / {{ $anio }}</small>
                </div>
            </div>

            <table class="table table-bordered table-striped tabla-masiva">
                <thead class="table-dark">
                    <tr>
                        <th>P/U</th>
                        <th>%</th>
                        @foreach($conceptos as $c)
                        <th class="text-uppercase">{{ $c == 'fondo_reserva' ? 'F.RES' : $c }}</th>
                        @endforeach
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todos as $row)
                    @php
                    $sumaFila = 0;
                    foreach($conceptos as $c) {
                    $sumaFila += floatval($row[$c] ?? 0);
                    }
                    @endphp
                    <tr class="{{ ($row['id_unidad'] == ($unidadClick['id'] ?? 0)) ? 'table-primary fw-bold' : '' }}">
                        <td>{{ $row['piso'] }} - {{ $row['unidad'] }}</td>
                        <td>{{ $row['porcentual'] ?? 0 }}%</td>
                        @foreach($conceptos as $c)
                        <td>$ {{ number_format(floatval($row[$c] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-success fw-bold">$ {{ number_format($sumaFila, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td colspan="2" class="text-end">TOTALES:</td>
                        @foreach($conceptos as $c)
                        <td>$ {{ number_format(floatval($registroGeneral[$c] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-danger">$ {{ number_format($totalGeneralEdificio, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- ================= LADO DERECHO (Cupón Individual) ================= -->
        <div class="{{ $detallado ? 'col-4 ps-3' : 'col-6 mx-auto mt-5' }}">
            <div class="cupon-container h-100 bg-light">

                <div class="text-center mb-2 border-bottom pb-2">
                    <img src="{{ public_path('image/Cardinal.png') }}" style="max-height: 35px; margin-bottom: 5px;">
                    <h6 class="fw-bold mb-0">Resumen de Liquidación</h6>
                    <small>Período: {{ $mes }} / {{ $anio }}</small> <br>
                    <small>IMPUESTO Y EXPENSAS</small>
                </div>

                <div class="mb-2" style="font-size: 0.8rem;">
                    <strong>Propietario:</strong> {{ $unidadClick['propietario'] ?? 'S/N' }}<br>
                    <strong>Unidad:</strong> Piso {{ $unidadClick['piso'] ?? '' }} - {{ $unidadClick['unidad'] ?? '' }}<br>
                    <strong>Porcentual:</strong> {{ $unidadClick['porcentual'] ?? 0 }}%
                </div>

                <!-- 1. TABLITA DE CONCEPTOS REGULARES -->
                <table class="table table-bordered table-sm tabla-individual text-center mb-1">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start">CONCEPTO</th>
                            <th style="width: 35%;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($detallado)
                        <!-- MODO DETALLADO: Muestra todos los conceptos comunes -->
                        @foreach($conceptosRegulares as $c)
                        @if(isset($registroClick[$c]) && floatval($registroClick[$c]) > 0)
                        @php
                        $textoPeriodo = '';
                        if ($c === 'tgi') $textoPeriodo = "$mes";
                        elseif ($c === 'api') $textoPeriodo = "$mesBimestreStr";
                        elseif ($c === 'agua') $textoPeriodo = "$mesBimestreStr $cuotaAgua";
                        @endphp
                        <tr>
                            <td class="text-start text-uppercase">
                                {{ $c }}
                                @if($textoPeriodo !== '')
                                <small class="text-muted fw-normal" style="font-size: 0.65rem; margin-left: 4px;">{{ $textoPeriodo }}</small>
                                @endif
                            </td>
                            <td>$ {{ number_format(floatval($registroClick[$c]), 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        @endforeach
                        @else
                        <!-- MODO SIMPLE: Muestra solo una fila agrupadora -->
                        <tr>
                            <td class="text-start fw-bold text-uppercase">Total Ordinarias</td>
                            <td class="fw-bold">$ {{ number_format($totalOrdinarias, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                    @if($detallado)
                    <!-- SUB-TOTAL ORDINARIAS EN MODO DETALLADO -->
                    <tfoot>
                        <tr class="table-light fw-bold border-top">
                            <td>TOTAL ORDINARIAS</td>
                            <td>$ {{ number_format($totalOrdinarias, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                <!-- 2. TABLITA DE FONDO DE RESERVA (Aparece en ambos modos si hay monto) -->
                @if(isset($registroClick['fondo_reserva']) && floatval($registroClick['fondo_reserva']) > 0)
                <table class="table table-bordered table-sm tabla-individual text-center mb-1">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start text-uppercase">Fondo de Reserva</th>
                            <th style="width: 35%;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start text-muted" style="font-size: 0.65rem; line-height: 1.3;">
                                <div class="obs-text">
                                    <strong>Obs:</strong> {{ $registroClick['fondo_reser_observaciones'] ?? 'Sin detalle' }}
                                </div>
                            </td>
                            <td class="align-middle fw-bold">$ {{ number_format(floatval($registroClick['fondo_reserva']), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <!-- 3. TABLITA DE EXTRAORDINARIA (Aparece en ambos modos si hay monto) -->
                @if(isset($registroClick['extraordinaria']) && floatval($registroClick['extraordinaria']) > 0)
                <table class="table table-bordered table-sm tabla-individual text-center mb-1">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start text-uppercase">Extraordinarias</th>
                            <th style="width: 35%;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start text-muted" style="font-size: 0.65rem; line-height: 1.3;">
                                <div class="obs-text">
                                    <strong>Obs:</strong> {{ $registroClick['extraordinaria_observaciones'] ?? 'Sin detalle' }}
                                </div>
                            </td>
                            <td class="align-middle fw-bold">$ {{ number_format(floatval($registroClick['extraordinaria']), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <!-- 4. TABLITA DE OTRO (Aparece en ambos modos si hay monto) -->
                @if(isset($registroClick['otro']) && floatval($registroClick['otro']) > 0)
                <table class="table table-bordered table-sm tabla-individual text-center mb-1">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start text-uppercase">Otros Gastos</th>
                            <th style="width: 35%;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start text-muted" style="font-size: 0.65rem; line-height: 1.3;">
                                <div class="obs-text">
                                    <strong>Obs:</strong> {{ $registroClick['otro_observaciones'] ?? 'Sin detalle' }}
                                </div>
                            </td>
                            <td class="align-middle fw-bold">$ {{ number_format(floatval($registroClick['otro']), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <!-- 5. TABLA FIJA PARA EL TOTAL -->
                <table class="table table-bordered table-sm tabla-individual text-center mt-2 mb-0">
                    <tr class="table-dark text-white fw-bold">
                        <td class="text-end">TOTAL A PAGAR:</td>
                        <td style="width: 35%; font-size: 0.9rem;">$ {{ number_format($totalCupon, 2, ',', '.') }}</td>
                    </tr>
                </table>

            </div>
        </div>

    </div>
</body>

</html>