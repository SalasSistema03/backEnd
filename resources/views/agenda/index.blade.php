@extends('layout.nav')
@section('title', 'Agenda ' . session('usuario')->username)
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="d-flex">

                <div class="nav flex-column nav nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <!-- Fecha -->
                    <div class="col-md-12 pb-1">
                        <input type="date" class="form-control" id="fechaPrincipal" value="{{ $fechaSeleccionada ?? '' }}">
                    </div>
                    <!-- Listado de sectores -->
                    @foreach ($sectores as $sector)
                        <button class="nav-link sector-btn" id="{{ $sector->nombre }}-tab" data-bs-toggle="tab"
                            data-bs-target="#{{ $sector->nombre }}" type="button" role="tab"
                            aria-controls="{{ $sector->nombre }}"
                            aria-selected="{{ $sector->nombre == '' ? 'true' : 'false' }}"
                            data-sector="{{ $sector->nombre }}">
                            {{ $sector->nombre }}
                        </button>
                    @endforeach
                </div>
                <!-- Agenda -->
                <div class="tab-content flex-grow-1" id="v-pills-tabContent">
                    @foreach ($sectores as $sector)
                        <div class="tab-pane fade show" id="{{ $sector->nombre }}" role="tabpanel"
                            aria-labelledby="{{ $sector->nombre }}-tab">
                            <!-- Nombre del sector -->
                            <div class="col-md-12 px-2">
                                <h4>{{ $sector->nombre }} </h4>
                            </div>
                            <!-- Grilla horaria por sector -->
                            <div class="col-md-12 p-4">
                                <div class="table-responsive calendar-scroll rounded-start agenda-table">
                                    <table class="table calendar-table table-hover table-striped">
                                        <thead class="sticky-header caption-top">
                                            <tr>
                                                <!-- Columna de horas -->
                                                <th class="agenda-th agenda-header agenda-col-hora sticky-col">HORAS</th>
                                                <!-- Columnas de usuarios -->
                                                @foreach ($usuariosAgenda->where('sector_id', $sector->id) as $usuario)
                                                    <th class="agenda-th agenda-header agenda-col-usuario text-center">
                                                        {{ $usuario->username }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($h = 7; $h <= 20; $h++)
                                                @for ($m = 0; $m < 60; $m += 15)
                                                    <tr>
                                                        <td
                                                            class="agenda-td agenda-col-hora sticky-col text-center align-middle">
                                                            {{ sprintf('%02d:%02d', $h, $m) }}
                                                        </td>
                                                        @foreach ($usuariosAgenda->where('sector_id', $sector->id) as $usuario)
                                                            @php
                                                                $sectorId = $sector->id;

                                                                $horaActual = sprintf('%02d:%02d', $h, $m);
                                                                $horaActualMinutos = $h * 60 + $m;
                                                                /* @dump($evento); */
                                                                $eventoActivo = null;
                                                                //@dd($notasPorSectorUsuario);
                                                                // Buscar si hay algún evento activo para esta celda
                                                                if (
                                                                    isset(
                                                                        $notasPorSectorUsuario[$sector->id][
                                                                            $usuario->usuario_id
                                                                        ],
                                                                    )
                                                                ) {
                                                                    foreach (
                                                                        $notasPorSectorUsuario[$sector->id][
                                                                            $usuario->usuario_id
                                                                        ]
                                                                        as $horaInicio => $evento
                                                                    ) {
                                                                        // Convertir horas a minutos para comparación
                                                                        [$hIni, $mIni] = explode(':', $horaInicio);
                                                                        $inicioMinutos = (int) $hIni * 60 + (int) $mIni;

                                                                        [$hFin, $mFin] = explode(
                                                                            ':',
                                                                            $evento['hora_fin'],
                                                                        );
                                                                        $finMinutos = (int) $hFin * 60 + (int) $mFin;

                                                                        // Si la hora actual está entre inicio y fin
                                                                        if ($sector->id == 2) {
                                                                            if (
                                                                                $horaActualMinutos >= $inicioMinutos &&
                                                                                $horaActualMinutos <= $finMinutos
                                                                            ) {
                                                                                $eventoActivo = $evento;
                                                                                break;
                                                                            }
                                                                        } else {
                                                                            if (
                                                                                $horaActualMinutos >= $inicioMinutos &&
                                                                                $horaActualMinutos < $finMinutos
                                                                            ) {
                                                                                $eventoActivo = $evento;
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                            @endphp

                                                            <td class="agenda-td agenda-col-usuario calendar-slot @if ($eventoActivo) ocupado @endif"
                                                                data-usuario="{{ $usuario->usuario_id }}"
                                                                data-sector-nombre="{{ $sector->nombre }}"
                                                                data-username="{{ $usuario->username }}"
                                                                data-sector="{{ $sector->id }}"
                                                                data-hora="{{ $horaActual }}"
                                                                data-cliente="{{ $eventoActivo['cliente'] ?? '' }}"
                                                                data-cliente-id="{{ $eventoActivo['cliente_id'] ?? '' }}"
                                                                data-evento="{{ $eventoActivo
                                                                    ? json_encode([
                                                                        'nota' => [
                                                                            'id' => $eventoActivo['nota']->id,
                                                                            'descripcion' => $eventoActivo['nota']->descripcion,
                                                                            'hora_inicio' => $eventoActivo['hora_inicio'],
                                                                            'hora_fin' => $eventoActivo['hora_fin'],
                                                                            'agenda_id' => $eventoActivo['nota']->agenda_id,
                                                                            'usuario_id' => $eventoActivo['nota']->usuario_id,
                                                                            'sector_id' => $eventoActivo['nota']->agenda->sector_id,
                                                                            'creado_por_username' => $eventoActivo['nota']->creado_por_username,
                                                                            'calle' => $eventoActivo['nota']->calle,
                                                                            'devoluciones' => $eventoActivo['nota']->devoluciones,
                                                                            'cliente_id' => $eventoActivo['nota']->cliente_id,
                                                                        ],
                                                                        'cliente' => $eventoActivo['cliente'] ?? '',
                                                                        'cliente_id' => $eventoActivo['cliente_id'] ?? '',
                                                                        'propiedad' => $eventoActivo['propiedad'] ?? '',
                                                                        'propiedad_id' => $eventoActivo['propiedad_id'] ?? '',
                                                                    ])
                                                                    : '' }}">


                                                                @if ($eventoActivo)
                                                                    @if ($sector->id == $eventoActivo['nota']->agenda->sector_id)
                                                                        @if ($sector->id == 3)
                                                                            @php
                                                                                $propiedadPartes = explode(
                                                                                    ' - ',
                                                                                    $eventoActivo['propiedad'],
                                                                                    2,
                                                                                );
                                                                                $direccion = isset($propiedadPartes[1])
                                                                                    ? $propiedadPartes[1]
                                                                                    : $eventoActivo['propiedad'];
                                                                            @endphp
                                                                            <div class="ocupado-content"
                                                                                title="{{ $eventoActivo['nota']->descripcion }}">
                                                                                <div
                                                                                    class="btn btn-primary w-100 ocupado_calle">
                                                                                    {{ $direccion }}
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <div class=" ocupado-content "
                                                                                title="{{ $eventoActivo['nota']->descripcion }}">
                                                                                <i
                                                                                    class="fa-regular fa-calendar-days btn btn-primary w-100"></i>
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <div class="ocupado-content"
                                                                            title="{{ $eventoActivo['nota']->descripcion }}">
                                                                            <i
                                                                                class="fa-regular fa-calendar btn btn-dark w-100"></i>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endfor
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Modal para agregar/editar evento -->
                    <div class="modal fade" data-bs-backdrop="static" id="calendarEventModal" tabindex="-1"
                        aria-labelledby="calendarEventModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="calendarEventModalLabel">Agenda</h5>
                                    <span id="sector-modal-id"></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="calendarEventForm" class="row" action="{{ route('agenda.store') }}"
                                        method="POST" autocomplete="off">
                                        @csrf
                                        <input type="hidden" id="sector_real" name="sector">
                                        <div class="col-md-3">
                                            <label for="modal-username" class="form-label">Usuario</label>
                                            <input type="text" class="form-control" id="modal-username"
                                                name="usuario_nombre" readonly>
                                            <input type="hidden" id="modal-userid" name="usuario_id">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="fechaNota" class="form-label">Fecha</label>
                                            <input type="date" class="form-control" id="fechaNota" name="fecha"
                                                value="{{ $fechaSeleccionada ?? '' }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="modal-hora" class="form-label">Hora de inicio</label>
                                            <input type="text" class="form-control" id="modal-hora" name="hora_inicio"
                                                readonly>
                                        </div>
                                        <!-- AGREGAR EN EL HEAD de tu layout -->

                                        <div class="col-md-3">
                                            <label for="modal-hora-fin" class="form-label">Hora de finalización</label>
                                            <!-- EL INPUT ORIGINAL con el ID y NAME que necesitas -->
                                            <input type="text" class="form-control" id="modal-hora-fin"
                                                name="hora_fin" placeholder="HH:MM" readonly style="height: 32px;">
                                            <!-- Mismo tamaño que Bootstrap -->
                                        </div>
                                        <div class="col-md-12">
                                            <label for="modal-descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="modal-descripcion" name="descripcion" placeholder="Descripción del evento"
                                                rows="4"></textarea>
                                        </div>
                                        <!-- Devolución (solo sector Ventas id=2) -->
                                        {{-- <div class="col-md-6" style="display:none;" id="devolucionContainer" >
                                            <label for="devolucion" class="form-label">Devolución</label>
                                            <input type="text" class="form-control" id="devolucion" name="devolucion"
                                                placeholder="Ingrese devolución" value="{{ isset($eventoActivo) ? $eventoActivo['nota']->devoluciones : '' }}">
                                        </div> --}}
                                        <div class="col-md-6" id="clienteContainer">
                                            <label for="cliente_info" class="form-label">Cliente</label>
                                            <input type="text" class="form-control" id="cliente_info"
                                                name="cliente_info"
                                                value="{{ isset($eventoActivo['nota']->cliente) ? $eventoActivo['nota']->cliente->telefono . ' - ' . $eventoActivo['nota']->cliente->nombre : 'Sin cliente' }}"
                                                readonly>
                                            <input type="hidden" id="cliente_id" name="cliente_id"
                                                value="{{ $eventoActivo['nota']->cliente_id ?? '' }}">
                                        </div>

                                        <div class="col-md-6" id="creadoPorContainer">
                                            <label for="creadoPor" class="form-label">Creado por</label>
                                            <input type="text" class="form-control" id="creadoPor"
                                                name="creado_por_username"
                                                value="{{ isset($eventoActivo) ? $eventoActivo['nota']->creado_por_username : '' }}"
                                                readonly>
                                        </div>



                                        <div class="col-md-6" style="display:none;" id="div-buscar-propiedad">
                                            <label for="tipo_busqueda" class="form-label">Buscar propiedad por</label>
                                            <select class="form-control" id="tipo_busqueda">
                                                <option value="codigo">Código</option>
                                                <option value="calle">Calle</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6" style="display:none;" id="div-codigo-propiedad">
                                            <label for="propiedad" class="form-label">Codigo Propiedad</label>
                                            <input type="text" class="form-control" id="propiedad"
                                                autocomplete="off">
                                            <input type="hidden" id="propiedad-codigo-real" name="propiedad_id">
                                            <div id="sugerencias-propiedades" class="list-group position-absolute"
                                                style="z-index: 1000;"></div>
                                        </div>

                                        <div class="col-md-6" style="display:none;" id="div-calle-propiedad">
                                            <label for="calle-autocomplete" class="form-label">Calle Propiedad</label>
                                            <input type="text" id="calle-autocomplete" class="form-control"
                                                placeholder="Buscar calle..."
                                                value="{{ isset($eventoActivo) ? $eventoActivo['nota']->calle : '' }}">
                                            <input type="hidden" id="calle_id" name="calle"
                                                value="{{ isset($eventoActivo) ? $eventoActivo['nota']->calle : '' }}">
                                            <div id="calle-suggestions" class="list-group mt-2"
                                                style="position: absolute; z-index: 1000;"></div>
                                        </div>
                                        <div class="col-md-6" style="display:none;" id="cliente-nombre">
                                            <label for="cliente" class="form-label">Nombre Cliente</label>
                                            <input type="text" class="form-control" id="cliente-nombre"
                                                autocomplete="off" name="cliente_nombre">
                                            {{--     <input type="hidden" id="cliente-nombre-real" name="cliente-nombre"> --}}
                                            {{-- <div id="sugerencias-clientes" class="list-group position-absolute"
                                                style="z-index: 1000;"></div>  --}}
                                        </div>
                                        <div class="col-md-6" style="display:none;" id="clientelefono">
                                            <label for="cliente" class="form-label">Numero Telefono Cliente</label>
                                            <input type="text" class="form-control" id="cliente" autocomplete="off"
                                                name="cliente_telefono">
                                            <input type="hidden" id="cliente-telefono-real" name="cliente_id">
                                            <div id="sugerencias-clientes" class="list-group position-absolute"
                                                style="z-index: 1000;"></div>
                                        </div>

                                        <input type="hidden" id="nota-id" name="nota_id">
                                        {{-- -------------------------------------- DATOS DEL ALQUILER A COMPLETAR-------------------------------------- --}}

                                        <div class="col-md-12 pt-3" id="input-alquiler-container" style="display: none;">
                                            <div class="table-responsive calendar-table-scroll rounded-start">
                                                <table class="table calendar-table table-hover table-striped">
                                                    <thead class="sticky-table-header caption-top">
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Inmueble</th>
                                                            <th>Asesor</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="calendar-table-body">
                                                        <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted">
                                                                Seleccione un cliente para ver su historial de muestras
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>


                                    </form>

                                    <div class="d-flex justify-content-between mt-3">
                                        <div>
                                            <form id="bajaForm" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" id="nota-id-baja" name="id">
                                                {{--   <input type="hidden" id="fecha-baja" name="usuario" value="{{ session('usuario_id') }}"> --}}
                                                <button type="submit" class="btn btn-danger">Baja</button>
                                            </form>
                                        </div>
                                        <div class="ms-auto">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-secondary me-2"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" id="btnGuardar" class="btn btn-primary"
                                                        onclick="document.getElementById('calendarEventForm').submit();">Guardar</button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.RUTA_CLIENTE = "{{ url('/clientes/buscar-telefono') }}";
        window.RUTA_PROPIEDAD = "{{ url('/propiedades/buscar-codigo') }}";
        window.RUTA_CALLE = "{{ url('/buscar-calle') }}";
        window.RUTA_HISTORIAL = "{{ url('/clientes/historial') }}";
        /* window.CALLES = @json($calles); */
    </script>
    {{--  <script>
        // Pasar los datos de Laravel a JS
        window.CALLES = @json($calles);

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('calle-autocomplete');
            const suggestions = document.getElementById('calle-suggestions');
            const hiddenId = document.getElementById('calle_id');

            input.addEventListener('input', function() {
                const valor = input.value.toLowerCase();
                suggestions.innerHTML = '';
                hiddenId.value = '';
                if (valor.length < 2) {
                    suggestions.style.display = 'none';
                    return;
                }
                // Filtrar por nombre de calle o número
                const filtrados = window.CALLES.filter(item => {
                    const nombre = (item.nombre_calle || '').toLowerCase();
                    const numero = (item.numero_calle || '').toString();
                    const id_propiedad = item.id_inmueble;

                    return nombre.includes(valor) || numero.includes(valor) || (nombre + ' ' +
                        numero).includes(valor);
                }).slice(0, 10);

                if (filtrados.length > 0) {
                    suggestions.style.display = 'block';
                    filtrados.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'list-group-item list-group-item-action';
                        div.textContent = item.cod_alquiler != null ?
                            `A: ${item.cod_alquiler} | ${item.nombre_calle || ''} ${item.numero_calle || ''}` :
                            `V: ${item.cod_venta} | ${item.nombre_calle || ''} ${item.numero_calle || ''}`
                            .trim();
                        div.addEventListener('click', function() {
                            input.value =
                                `${item.cod_alquiler != null ? 'A: ' : 'V: '}${item.cod_alquiler || item.cod_venta} | ${item.nombre_calle || ''} ${item.numero_calle || ''}`
                                .trim();
                            hiddenId.value =
                                `${item.id_inmueble}`
                                .trim();
                            suggestions.style.display = 'none';
                        });

                        suggestions.appendChild(div);
                    });
                } else {
                    suggestions.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.style.display = 'none';
                }
            });
        });
    </script> --}}




    {{--  <script>
        @if (isset($eventoActivo))
            console.log("eventoActivo", @json($eventoActivo));
        @endif
    </script> --}}


    <script>
        // **DETECTAR CLICK EN LAS CELDAS DE LA AGENDA**
        document.addEventListener('click', function(e) {
            // Buscar si se hizo click en una celda de la agenda o dentro de ella
            const celda = e.target.closest('.calendar-slot');

            if (celda) {
                const clienteId = celda.getAttribute('data-cliente-id');
                const eventoData = celda.getAttribute('data-evento');
                const sectorId = celda.getAttribute('data-sector');
                if (sectorId == 3) {


                    // Si hay un evento activo (celda ocupada)
                    if (eventoData && eventoData !== '') {
                        try {
                            const evento = JSON.parse(eventoData);

                            // Verificar si hay cliente_id
                            const idCliente = evento.cliente_id || clienteId;

                            if (idCliente) {
                                // **CARGAR HISTORIAL AUTOMÁTICAMENTE**
                                cargarHistorialCliente(idCliente);
                            } else {
                                // Si no hay cliente, limpiar la tabla
                                limpiarTablaHistorial();
                            }
                        } catch (error) {
                            console.error('Error al parsear evento:', error);
                        }
                    } else {
                        // Si es una celda vacía, limpiar la tabla
                        limpiarTablaHistorial();
                    }
                }
            }
        });









        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#modal-hora-fin", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 15,
                locale: "es",
                onOpen: function(selectedDates, dateStr, instance) {
                    // Fuerza a Flatpickr a usar el valor actual del input
                    const currentValue = document.getElementById('modal-hora-fin').value;
                    if (currentValue && currentValue.trim() !== '') {
                        // Parsea la hora actual y la establece
                        const [hours, minutes] = currentValue.split(':');
                        const today = new Date();
                        today.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                        instance.setDate(today, false); // false = no dispara onChange
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    console.log("Hora fin:", dateStr);
                }
            });
        });
    </script>


@endsection

@section('scripts')
    <script src="{{ asset('js/agenda/actualizar-pagina.js') }}"></script>
    <script src="{{ asset('js/agenda/mostrar-calendario.js') }}"></script>
    <script src="{{ asset('js/agenda/selector-busqueda.js') }}"></script>
    <script src="{{ asset('js/agenda/sincronizar-fecha.js') }}"></script>
    <script src="{{ asset('js/agenda/mostrar_ocultar-datos.js') }}"></script>
    <script src="{{ asset('js/agenda/autocompletado-datos.js') }}"></script>
    {{-- <script src="{{ asset('js/agenda/preseleccionado-de-sector.js') }}"></script> --}}
@endsection
