<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha de Propiedad</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo-cell {
            width: 80px;
        }

        .logo {
            height: 60px;
        }

        .title {
            margin: 0;
            color: rgba(0, 175, 154, 0.96);
            font-size: 24px;
        }

        /* Layout principal */
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-cell {
            width: 40%;
            vertical-align: top;
            padding: 10px;
        }

        .photos-cell {
            width: 60%;
            vertical-align: top;
            padding: 10px;
        }

        /* Tabla de datos */
        .data-table {

            width: 100%;
            /* border-collapse: collapse; */
            font-size: 12px;
            box-shadow: 0px 0px 10px #0056b3;
            /* border: 3px solid #0056b3; */
            border-radius: 10px;
        }

        .data-table th {
            background: #0056b3;
            /* box-shadow: 0px 0px 2px #0056b3; */
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            /* border: 3px solid #0056b3; */
            border-radius: 10px;
        }

        .data-table td {
            padding: 5px;
        }

        .text-propiedad {
            text-align: center;
            font-size: 8;
            
        }

        /* Fotos */
        .photo-container {
            margin-bottom: 5px;
            text-align: center;
        }

        .property-photo {

            max-height: 350px;
            min-height: 350px;
            max-width: 550px;

            border: 3px solid #ffffffff;
            border-radius: 10px;
        }

        /* Descripción */
        .descripcion-fullwidth {
            width: 100%;
            padding: 5px;
            padding-top: 1px;
            box-shadow: 0px 0px 10px #000000ff;

        }

        .descripcion-texto {
            white-space: pre-line;
            /* Respeta saltos de línea */
            word-wrap: break-word;
            /* Rompe palabras largas */
            overflow-wrap: break-word;
            /* Alternativa moderna */
            text-align: justify;
            /* Texto justificado */
        }

        .title_table {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img class="logo" src="{{ public_path('image/logo.png') }} " alt="Logo">
            </td>
            <td>
                @if ($tipoBTN == 'alquiler')
                    <h1 class="title"> Ficha de propiedad en Alquiler - {{ $propiedad->cod_alquiler }}</h1>
                @else
                    <h1 class="title"> Ficha de propiedad en Venta - {{ $propiedad->cod_venta }}</h1>
                @endif
            </td>
        </tr>

    </table>

    <table class="main-table">
        <tr>
            <td class="data-cell">
                <table class="data-table">
                    <tr>
                        <th colspan="2">Datos de la Propiedad</th>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Dirección</td>
                    </tr>
                    <tr>
                        @php 
                        if ($propiedad->piso) {
                            $piso = " - Piso " . $propiedad->piso;
                        } else {
                            $piso = '';
                        }
                        if ($propiedad->departamento) {
                            $departamento = " - Depto " . $propiedad->departamento;
                        } else {
                            $departamento = '';
                        }
                        @endphp
                        <td>
                            {{ $propiedad->calle->name ?? '' }} {{ $propiedad->numero_calle ?? '' }} {{ $piso }}  {{ $departamento }}
                             <!-- / Piso
                            {{ $propiedad->piso ?? '' }} / Depto {{ $propiedad->departamento ?? '' }} --></td>

                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Zona</td>

                    </tr>
                    <tr>
                        <td>{{ strtoupper($propiedad->zona->name ?? '') }}</td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Tipo Inmueble</td>
                    </tr>
                    <tr>
                        <td>{{ $propiedad->tipoInmueble->inmueble ?? '' }}</td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Servicios</td>
                    </tr>
                    <tr>
                        <td>
                            @php
                                $servicios = [];
                                if ($propiedad->gas == 'SI') {
                                    $servicios[] = 'GAS ';
                                }
                                if ($propiedad->agua == 'SI') {
                                    $servicios[] = ' AGUA ';
                                }
                                if ($propiedad->cloaca == 'SI') {
                                    $servicios[] = ' CLOACA ';
                                }
                                if ($propiedad->asfalto == 'SI') {
                                    $servicios[] = 'ASFALTO';
                                }
                            @endphp
                            {{ implode(', ', $servicios) }}
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Metros</td>
                    </tr>
                    <tr>

                        <td>
                            @if (!empty($propiedad->mLote) && $propiedad->mLote != 0)
                                {{ $propiedad->mLote . ' m²' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Metros Cubiertos</td>
                    </tr>
                    <td>
                        @if (!empty($propiedad->mCubiertos) && $propiedad->mCubiertos != 0)
                            {{ $propiedad->mCubiertos . ' m²' }}
                        @else
                            -
                        @endif
                    </td>
                    <tr>
                        <td width="100%" class = "title_table">Codigo Web</td>
                    </tr>
                    <td>
                        @if ($tipoBTN == 'alquiler')
                            {{ $propiedad->cod_alquiler }}
                        @else
                            {{ $propiedad->cod_venta }}
                        @endif
                    </td>
                    <tr>
                        <td width="100%" class = "title_table">Alquilado</td>
                    </tr>               
                    <tr>
                        <td>
                         
                            @if ($vencimiento_contratos != null && $vencimiento_contratos > now())
                                SI
                            @else
                                NO
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Valor</td>
                    </tr>
                    <tr>
                        <td>
                         
                            @if ($tipoBTN == 'alquiler')
                                @if ($propiedad->precio->moneda_alquiler_pesos !== null)
                                    $ {{ $propiedad->precio->moneda_alquiler_pesos }}
                                @elseif($propiedad->precio->moneda_alquiler_dolar !== null)
                                    USD {{ $propiedad->precio->moneda_alquiler_dolar }}
                                @else
                                    -
                                @endif
                            @else
                                @if ($propiedad->precio->moneda_venta_pesos !== null)
                                    $ {{$propiedad->precio->moneda_venta_pesos }}
                                @elseif($propiedad->precio->moneda_venta_dolar !== null)
                                    USD {{ $propiedad->precio->moneda_venta_dolar }}
                                @else
                                    -
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" class = "title_table">Descripción</td>
                    </tr>
                    <tr>
                        <td>{{ $propiedad->descipcion_propiedad ?? '-' }}</td>
                    </tr>
                </table>
                <br>
                 <table>
                    <tr>
                        <th colspan="2">¿Que te parecio tu visita? ¡Contanos!</th>
                    </tr>
                    <tr>
                     
                     <td>
                        {{-- <img src="{{ asset('image/googleQR.png') }}" alt="QR" class="header-img" id="QR" style="width: 250px; height: 250px; display: block; margin-left: auto; margin-right: auto;"> --}}
                        <img src="{{ public_path('image/googleQR.png') }}" alt="QR" class="header-img" id="QR" style="width: 250px; height: 250px; display: block; margin-left: auto; margin-right: auto;">
                        <!-- <p style="text-align: center; font-size: 12px; color: black;">{{ $propiedad->descripcion_completa ?? '-' }}</p> -->
                     </td>
                    </tr>
                </table> 
            <td class="photos-cell">
                @if (count($fotos) > 0)
                    @foreach (array_slice($fotos->toArray(), 0, 3) as $foto)
                        <div class="photo-container">
                            @php
                                $rutaImagen = str_replace('/imagenes', $htmlRemplace, $foto['url']);
                            @endphp
                            <img class="property-photo" src="{{ $rutaImagen }}" alt="Foto propiedad">
                        </div>
                    @endforeach
                @endif
            </td>
          
        </tr>
    </table>
</body>

</html>
