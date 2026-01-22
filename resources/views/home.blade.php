@extends('layout.nav')

@section('title', 'Home ' . session('usuario')->username)

@section('content')
    {{--  @dd($dolares); --}}
    <style>
        .dolar-scroll-container {
            overflow: hidden;
            position: relative;
            width: 100%;
        }
        
         .dolar-scroll-content {
            display: inline-flex;
            white-space: nowrap;
            animation: scrollLeft 60s linear infinite;
        }
        
        .dolar-item {
            display: inline-block;
            /* padding: 0 2rem; */
        }

        .letra-dolar {
            font-size: 12px;
        }
        
     /*   @keyframes scrollLeft {
            0% {
                transform: translateX(0%);
            }
            100% {
                transform: translateX(-500%);
            }
        }
        
        .dolar-scroll-container:hover .dolar-scroll-content {
            animation-play-state: paused;
        } */

    </style> 

    <div class="px-3">
        <!-- Saludo personalizado -->
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-md-3">
                <div class="alert alert-primary alert-salas d-flex align-items-center" role="alert">
                    <i class="bi bi-person-circle me-2 fs-3"></i>
                    <div class="flex-grow-1">
                        @php
                            $hora = now()->hour;
                        @endphp

                        @if ($hora >= 13)
                            ¡Buenas tardes, <strong>{{ session('usuario')->username ?? 'Invitado' }}</strong>!
                        @else
                            ¡Buen díaaaa, <strong>{{ session('usuario')->username ?? 'Invitado' }}</strong>!
                        @endif
                    </div>
                    <div class="flex-grow-1 small ms-auto text-end">
                        Hoy es <strong>{{ now()->format('d/m/Y') }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="alert alert-primary alert-salas-cumpleaños d-flex align-items-center" role="alert">
                   {{--  <i class="bi bi-currency-dollar me-2"></i> --}}
                    <div class="flex-grow-1 dolar-scroll-container">
                        <div class="dolar-scroll-content">
                            @foreach ($dolares as $dolar)
                           {{--  @dd($dolares) - --}}
                                <div class="dolar-item">
                                    <strong class="letra-dolar">{{ $dolar['nombre'] }}:</strong>
                                    <strong class="me-2 letra-dolar">Compra: ${{ number_format($dolar['compra'], 2, ',', '.') }}</strong>
                                    <strong class="letra-dolar">Venta: ${{ number_format($dolar['venta'], 2, ',', '.') }}</strong>
                                </div>
                            @endforeach

                            {{-- @foreach ($dolares as $dolar)
                                <div class="dolar-item">
                                    <strong>{{ $dolar['nombre'] }}:</strong>
                                    <strong class="me-2">Compra: ${{ number_format($dolar['compra'], 2, ',', '.') }}</strong>
                                    <strong>Venta: ${{ number_format($dolar['venta'], 2, ',', '.') }}</strong>
                                </div>
                            @endforeach --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-md-7">
                <div class="card card-salas">
                    <div class="card-header">
                        <i class="bi bi-calendar2-week"></i> <strong>Agenda del Día</strong>
                    </div>
                    <div class="card-body">
                        <!-- Placeholder eventos -->

                        @if ($notasVinculadas->isEmpty())
                            <li class="alert alert-warning shadow-sm" role="alert"><i
                                    class="bi bi-exclamation-triangle me-2"></i>Sin eventos para hoy.</li>
                        @else
                            <div class="scrollable ">
                                <table>
                                    <thead class="text-start">
                                        <tr>
                                            <th>Sector</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Cliente</th>
                                            <th>Propiedad</th>
                                            <th>Descripción</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach ($notasVinculadas as $nota)
                                        {{--  @dd($nota)   --}}
                                            <tr class="salas-hover text-center ">
                                                <td>{{ $nota['sector'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($nota['hora_inicio'])->format('H:i') }} Hs.</td>
                                                <td>{{ \Carbon\Carbon::parse($nota['hora_fin'])->format('H:i') }} Hs.</td>
                                                <td>{{ $nota['cliente'] }}</td>
                                                <td>{{ $nota['nombre_calle' ] }} {{ $nota['numero_calle'] }}</td>
                                               {{--  @if ($nota['sector'] != 'Ventas')
                                                    <td>{{ $nota['propiedad_venta'] }}</td>
                                                @elseif($nota['sector'] != 'Alquiler')
                                                    <td>{{ $nota['propiedad_alquiler'] }}</td>
                                                @else
                                                    <td> - </td>
                                                @endif --}}
                                                <td class="descripcion">{{ $nota['descripcion'] }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-3">
                <div class="card card-salas">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge"></i> <strong>Recordatorios Pendientes</strong>
                    </div>
                    <div class="card-body">

                        <div class="card-body gap-2">
                            @if ($recordatorio->isEmpty())
                                <div class="alert alert-warning shadow-sm" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No hay recordatorios para hoy
                                </div>
                            @else
                                <div class="scrollable">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Descripción</th>
                                                <th>Sector</th>
                                            </tr>
                                        </thead>
                                        @foreach ($recordatorio as $recordatorio)
                                            <tbody>
                                                <tr class="salas-hover">
                                                    <td>{{ \Carbon\Carbon::parse($recordatorio->fecha_inicio)->format('d/m/Y') }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($recordatorio->hora)->format('H:i') }}
                                                    </td>
                                                    <td>{{ $recordatorio->descripcion }}</td>
                                                    @if ($recordatorio->agenda != null)
                                                        <td>{{ $recordatorio->agenda->sector->nombre }}</td>
                                                    @else
                                                        <td> - </td>
                                                    @endif
                                                </tr>
                                            </tbody>
                                        @endforeach
                                    </table>

                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection