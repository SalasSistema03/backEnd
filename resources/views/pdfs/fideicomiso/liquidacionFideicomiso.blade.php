<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        {!! file_get_contents(public_path('css/pdfStyles.css')) !!}
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
        }
        
        /* Ajustes extremos para comprimir verticalmente y que entren 35 filas */
        .tabla-masiva {
            margin-bottom: 0 !important;
        }
        .tabla-masiva th, .tabla-masiva td {
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
        
        /* Ajustes del lado derecho */
        .tabla-individual th, .tabla-individual td {
            font-size: 0.8rem;
            padding: 0.2rem !important;
        }
        .cupon-container {
            border: 2px dashed #ccc;
            padding: 8px 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    @php
        $unidadClick = $data['unidad'] ?? [];
        $registroClick = $data['registro'] ?? [];
        $registroGeneral = $data['registro_general'] ?? [];
        $todos = $data['todos_los_registros'] ?? [];
        
        $periodo = $registroClick['periodo'] ?? '';
        [$anio, $mes] = array_pad(explode('-', $periodo), 2, '');
        
        $conceptos = ['tgi', 'agua', 'api', 'luz', 'seguro', 'limpieza', 'ascensor', 'honorario'];
        
        // Suma total del 100% del edificio
        $totalGeneralEdificio = 0;
        foreach($conceptos as $c) {
            $totalGeneralEdificio += floatval($registroGeneral[$c] ?? 0);
        }

        // Lógica de cálculo de períodos bimestrales
        $mesInt = intval($mes);
        $mesBimestre = ceil($mesInt / 2);
        $mesBimestreStr = str_pad($mesBimestre, 2, '0', STR_PAD_LEFT);
        
        // Lógica de cuotas para el Agua según la paridad del mes
        $cuotaAgua = ($mesInt % 2 == 0) ? '(1/2)' : '(2/2)';
    @endphp

    <div class="row m-0 w-100">
        
        <!-- ========================================================= -->
        <!-- LADO IZQUIERDO: PADRÓN COMPLETO (COL-8)                   -->
        <!-- ========================================================= -->
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
                        <th>Piso/Unidad</th>
                        <!-- <th class="text-start">Propietario</th> -->
                        <th>%</th>
                        @foreach($conceptos as $c)
                            <th class="text-uppercase">{{ $c }}</th>
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
                            <!-- <td class="text-start text-truncate" style="max-width: 75px; overflow: hidden;">{{ $row['propietario'] ?? 'S/N' }}</td> -->
                            
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
                        <td colspan="2" class="text-end">TOTALES GENERALES:</td>
                        <!-- <td colspan="3" class="text-end">TOTALES GENERALES:</td> -->
                        @foreach($conceptos as $c)
                            <td>$ {{ number_format(floatval($registroGeneral[$c] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-danger">$ {{ number_format($totalGeneralEdificio, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ========================================================= -->
        <!-- LADO DERECHO: CUPÓN INDIVIDUAL (COL-4)                    -->
        <!-- ========================================================= -->
        <div class="col-4 ps-3">
            <div class="cupon-container h-100 bg-light">
                
                <div class="text-center mb-2 border-bottom pb-2">
                    <img src="{{ public_path('image/Cardinal.png') }}" style="max-height: 35px; margin-bottom: 5px;">
                    <!-- <h6 class="fw-bold mb-0">Cupón de Pago Individual</h6> -->
                     <h6 class="fw-bold mb-0">Resumen de Liquidación</h6>
                    <small>Período: {{ $mes }} / {{ $anio }}</small>
                </div>

                <div class="mb-2" style="font-size: 0.8rem;">
                    <strong>Propietario:</strong> {{ $unidadClick['propietario'] ?? 'S/N' }}<br>
                    <!-- <strong>Unidad:</strong> Piso {{ $unidadClick['piso'] ?? '' }} - Dto {{ $unidadClick['unidad'] ?? '' }}<br> -->
                    <strong>Unidad:</strong> Piso {{ $unidadClick['piso'] ?? '' }} - {{ $unidadClick['unidad'] ?? '' }}<br>
                    <strong>Porcentual:</strong> {{ $unidadClick['porcentual'] ?? 0 }}%
                </div>

                <table class="table table-bordered table-sm tabla-individual text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start">Concepto</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalCupon = 0; @endphp
                        @foreach($conceptos as $c)
                            @if(isset($registroClick[$c]) && floatval($registroClick[$c]) > 0)
                                @php 
                                    $totalCupon += floatval($registroClick[$c]); 
                                    $textoPeriodo = '';
                                    
                                    if ($c === 'tgi') {
                                        $textoPeriodo = "$mes";
                                    } elseif ($c === 'api') {
                                        $textoPeriodo = "$mesBimestreStr";
                                    } elseif ($c === 'agua') {
                                        $textoPeriodo = "$mesBimestreStr $cuotaAgua";
                                    }
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
                    </tbody>
                    <tfoot>
                        <tr class="table-dark text-white fw-bold">
                            <td class="text-end">TOTAL A PAGAR:</td>
                            <td>$ {{ number_format($totalCupon, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-4 text-center">
                    <!-- <p class="mb-4 small text-muted" style="font-size: 0.7rem;">Válido como comprobante de pago con firma y sello.</p>
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <small>Firma / Sello Inmobiliaria</small>
                    </div> -->
                </div>

            </div>
        </div>

    </div>
</body>
</html>