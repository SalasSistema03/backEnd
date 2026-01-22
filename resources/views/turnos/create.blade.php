@extends('layout.nav')
@section('title', 'Toma Turnos')
@section('content')
    <div class=" px-3">
        <div class="row justify-content-center text-center">
            <div class="col-md-7 p-1">
                <div class="card turnosCardToma">
                    <div class="card-header turnosTitulosToma">
                        Registrar Turno
                    </div>
                    <div class="card-body px-3 pt-1">
                        <form action="{{ route('turnos.store') }}" method="POST" class="row" autocomplete="off">
                            @csrf
                            <input type="hidden" name="fecha_carga" value="{{ $fecha_carga }}">
                            <div class="form-group col-md-6">
                                <label for="tipo_identificador" class="form-label p-0">Identificador</label>
                                <select class="form-control @error('tipo_identificador') is-invalid @enderror"
                                    id="tipo_identificador" name="tipo_identificador" required>
                                    <option value="">Tipo Identificador...</option>
                                    <option value="DNI">DNI</option>
                                    <option value="Folio">Folio</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="numero_identificador" class="form-label">Número</label>
                                <input type="number"
                                    class="form-control" 
                                    id="numero_identificador" name="numero_identificador" required min="1">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="sector" class="form-label">Sector</label>
                                <select class="form-control" id="sector" name="sector" required>
                                    <option value="">Seleccione un sector...</option>
                                    @foreach ($sectores as $sector)
                                        <option value="{{ $sector->id }}">{{ $sector->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                             
                            <div class="form-group col-md-12 pt-3">
                                <button type="submit" class="btn btn-primary w-100">Registrar Turno</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-5 p-1">
                <div class="card turnosCard" id="turnos-card-pendientes">
                    <div class="card-header turnosTitulos">Turnos Pendientes</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaDatos">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Sector</th>
                                        <th>Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($turnos as $turno)
                                        <tr>
                                            <td>{{ $turno->numero_identificador }}</td>
                                            <td>{{ $turno->tipo_identificador }}</td>
                                            <td>{{ $turno->sector()->first()->nombre ?? 'Sin sector' }}</td>
                                            <td>{{ $turno->fecha_carga ? \Carbon\Carbon::parse($turno->fecha_carga)->format('H:i') : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 p-1">
                <div class="card turnosCard" id="turnos-card-llamados">
                    <div class="card-header turnosTitulos">Turnos Llamados</div>
                    <div class="card-body">
                        @if (isset($turnosLlamados) && $turnosLlamados)
                            <table class="table table-striped table-hover" id="tablaDatos">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Sector</th>
                                        <th>Ingreso</th>
                                        <th>Llamado</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($turnosLlamados as $turnos)
                                        <tr>
                                            <td>{{ $turnos->numero_identificador ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->tipo_identificador ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->sector()->first()->nombre ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->fecha_carga ? \Carbon\Carbon::parse($turnos->fecha_carga)->format('H:i') : '' }}
                                            </td>
                                            <td>{{ $turnos->fecha_llamado ? \Carbon\Carbon::parse($turnos->fecha_llamado)->format('H:i') : '' }}
                                            </td>
                                            <td>{{ $turnos->usuario_id ?? 'Sin Usuario' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 p-1">
                <div class="card turnosCard" id="turnos-card-inactivos">
                    <div class="card-header turnosTitulos">Turnos Terminados</div>
                    <div class="card-body">
                        @if (isset($turnosInactivos) && $turnosInactivos)
                            <table class="table table-striped table-hover" id="tablaDatos">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Sector</th>
                                        <th>Ingreso</th>
                                        <th>Llamado</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($turnosInactivos as $turnos)
                                        <tr>
                                            <td>{{ $turnos->numero_identificador ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->tipo_identificador ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->sector()->first()->nombre ?? 'Sin sector' }}</td>
                                            <td>{{ $turnos->fecha_carga ? \Carbon\Carbon::parse($turnos->fecha_carga)->format('H:i') : '' }}
                                            </td>
                                            <td>{{ $turnos->fecha_llamado ? \Carbon\Carbon::parse($turnos->fecha_llamado)->format('H:i') : '' }}
                                            </td>
                                            <td>{{ $turnos->usuario_id ?? 'Sin Usuario' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function refrescarTodasLasCards() {
            fetch("{{ route('turnos.create') }}")
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    // Refresca Turnos Pendientes
                    let nuevaPendientes = doc.querySelector('#turnos-card-pendientes');
                    if (nuevaPendientes) {
                        document.querySelector('#turnos-card-pendientes').innerHTML = nuevaPendientes.innerHTML;
                    }
                    // Refresca Turnos Llamados
                    let nuevaLlamados = doc.querySelector('#turnos-card-llamados');
                    if (nuevaLlamados) {
                        document.querySelector('#turnos-card-llamados').innerHTML = nuevaLlamados.innerHTML;
                    }
                    // Refresca Turnos Inactivos
                    let nuevaInactivos = doc.querySelector('#turnos-card-inactivos');
                    if (nuevaInactivos) {
                        document.querySelector('#turnos-card-inactivos').innerHTML = nuevaInactivos.innerHTML;
                    }
                });
        }
        setInterval(refrescarTodasLasCards, 5000); // Refresca cada 5 segundos
    </script>

@endsection
