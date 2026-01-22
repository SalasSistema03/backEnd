@extends('layout.nav')

@section('title', 'Asesor ' . session('usuario')->username)

@section('content')
    <div class="cont">
        <div class="row">
            <!-- Barra lateral estilo WhatsApp Web -->
            <div class="col-md-2 barra_contactos">
                <div class="row pb-1 px-2">
                    <!-- Filtro por potabilidad con menú desplegable -->
                    <div class="col-6 pb-1 px-2">
                        <div class="dropdown">
                            <button class="form-select text-center" type="button" id="btnPotDropdown" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span id="btnPotDropdownLabel">Todos</span>
                            </button>
                            <ul class="dropdown-menu w-100 letras-potabilidad" aria-labelledby="btnPotDropdown">
                                <li>
                                    <button type="button" class="dropdown-item p-0" data-potabilidad="">
                                        Todos
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item p-0" data-potabilidad="Potable">
                                        <i class="fa-regular fa-face-grin-beam text-success"></i> Potable
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item p-0" data-potabilidad="Medio">
                                        <i class="fa-regular fa-face-grimace text-warning"></i> Medio
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item p-0" data-potabilidad="No Potable">
                                        <i class="fa-regular fa-face-angry text-danger"></i> No Potable
                                    </button>
                                </li>
                            </ul>
                            <input type="hidden" id="filtroPotDropdownValue" value="">
                        </div>
                    </div>
                    <!-- Filtro por devolución con checkbox -->
                    <div class="col-6 pb-1 px-2">
                        <label for="devolucionInput" class="form-label devolucionT">Devolucion</label>
                        <input class="form-check-input mt-1" type="checkbox" value=""
                            aria-label="Checkbox for following text input" id="devolucionescheck">
                    </div>
                    <!-- Buscador -->
                    <div class="col-12 pb-1 px-2">
                        <input type="text" id="buscarInput" class="form-control py-1" placeholder="Buscar ">
                    </div>
                </div>
                <!-- Lista de contactos -->
                <div>
                    <ul class="list-group list-group-flush scroll">
                        <!-- Ordenamos los clientes -->
                        @foreach ($clientes_ordenados as $cliente)
                            <li class=" list-group-item list-group-item-action contacto"
                                onclick="showChat({{ $cliente->id_cliente }}); mostrarNombreCliente('{{ $cliente->nombre }}', '{{ $cliente->telefono }}', '{{ $cliente->id_cliente }}');"
                                data-cliente-id="{{ $cliente->id_cliente }}" data-nombre="{{ $cliente->nombre }}"
                                data-falta-devolucion="{{ $cliente->faltaDevolucion }}">
                                <div class="row">
                                    <!-- Datos del cliente -->
                                    <div class="d-flex justify-content-between align-items-center col-12">
                                        <!-- Nombre del cliente -->
                                        <div class="col-11">
                                            <strong>{{ strtoupper($cliente->nombre) }}</strong>
                                        </div>

                                        <!-- Potabilidad -->
                                        <div class="col-1">
                                            @php
                                                $potabilidad = null;
                                                $criteriosCliente = $criterios_venta->where(
                                                    'id_cliente',
                                                    $cliente->id_cliente,
                                                );
                                            @endphp

                                            {{-- Si no tiene criterios, seteamos potabilidad en SinCriterio --}}
                                            @if ($criteriosCliente->isEmpty())
                                                @php $potabilidad = "SinCriterio"; @endphp
                                            @else
                                                <!-- Ordenamos la potabilidad -->
                                                @foreach ($criterios_venta->where('id_cliente', $cliente->id_cliente) as $criterio)
                                                    @if ($criterio->estado_criterio_venta == 'Activo')
                                                        @if ($criterio->id_categoria == null)
                                                            @php $potabilidad = "Nulo" @endphp
                                                            @if ($potabilidad == 'Nulo')
                                                                @break;
                                                            @endif
                                                        @elseif ($criterio->id_categoria == 'Potable')
                                                            @php $potabilidad = "Potable" @endphp
                                                            @if ($potabilidad == 'Nulo')
                                                                @break;
                                                            @elseif ($criterio->id_categoria == 'Potable')
                                                                @php $potabilidad = "Potable" @endphp
                                                            @endif
                                                        @elseif ($criterio->id_categoria == 'Medio')
                                                            @if ($potabilidad == 'Potable')
                                                                @break;
                                                            @elseif ($criterio->id_categoria == 'Medio')
                                                                @php $potabilidad = "Medio" @endphp
                                                            @endif
                                                        @elseif ($criterio->id_categoria == 'No Potable')
                                                            @if ($potabilidad == 'Potable')
                                                                @break;
                                                            @elseif ($criterio->id_categoria == 'No Potable')
                                                                @php $potabilidad = "No Potable" @endphp
                                                            @endif
                                                        @else
                                                        @endif
                                                    @elseif ($criterio->estado_criterio_venta == 'Finalizado')
                                                        @php $potabilidad = "Finalizado" @endphp
                                                    @endif
                                                @endforeach
                                            @endif
                                            {{-- Mostramos la potabilidad --}}
                                            @if ($potabilidad == 'Potable')
                                                <i class="fa-regular fa-face-grin-beam text-success icono_potabilidad"></i>

                                                {{-- Verde --}}
                                            @elseif ($potabilidad == 'Medio')
                                                <i class="fa-regular fa-face-grimace text-warning icono_potabilidad"></i>
                                                {{-- Amarillo --}}
                                            @elseif ($potabilidad == 'No Potable')
                                                <i class="fa-regular fa-face-angry text-danger icono_potabilidad"></i>
                                                {{-- Rojo --}}
                                            @elseif ($potabilidad == 'Nulo')
                                                <i class="fa-regular fa-pen-to-square text-dark icono_potabilidad"></i>
                                                {{-- Gris --}}
                                            @elseif ($potabilidad == 'SinCriterio')
                                                <i class="fa-regular fa-file text-secondary icono_potabilidad"></i>
                                                {{-- Gris --}}
                                            @elseif ($potabilidad == 'Finalizado')
                                                <i class="fa-regular fa-handshake icono_potabilidad icono-azul"></i>
                                                {{-- Naranja --}}
                                            @else
                                                <i class="fa-regular fa-folder-closed icono_potabilidad naranja"></i>
                                                {{-- Naranja --}}
                                            @endif
                                        </div>

                                    </div>
                                    <div class="d-flex justify-content-between align-items-center col-12">
                                        <!-- Telefono y boton whatsapp -->
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <strong>{{ $cliente->telefono }}
                                                    <i class="fa-brands fa-whatsapp"
                                                        onclick="event.stopPropagation(); window.open('https://wa.me/{{ preg_replace('/[^0-9]/', '', $cliente->telefono) }}', '_blank')">
                                                    </i>
                                                </strong>
                                            </small>
                                        </div>
                                        <!-- Fecha -->
                                        <div class="col-3 ">
                                            @foreach ($criterios_venta->where('id_cliente', $cliente->id_cliente) as $criterio)
                                                @if ($loop->first)
                                                    <small class="text-muted ">
                                                        {{ \Carbon\Carbon::parse($criterio->fecha_criterio_venta)->format('d/m/Y') }}</small>
                                                @endif
                                            @endforeach
                                        </div>
                                        <!-- Boton editar cliente -->
                                        <div class="col-1">
                                            <button type="button" class="btn p-0 border-0 bg-transparent "
                                                data-bs-toggle="modal" data-bs-target="#modalEditarPersona"
                                                data-id-cliente="{{ $cliente->id_cliente }}"
                                                data-nombre="{{ $cliente->nombre }}"
                                                data-telefono="{{ $cliente->telefono }}"
                                                data-observaciones="{{ $cliente->observaciones }}"
                                                data-nombre-inmobiliaria="{{ $cliente->nombre_de_inmobiliaria }}"
                                                onclick="event.stopPropagation();">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Nombre de la inmobiliaria -->
                                    <div class="d-flex justify-content-start align-items-center col-12">
                                        @if ($cliente->nombre_de_inmobiliaria and $cliente->nombre_de_inmobiliaria != null)
                                            <span class="badge pertenece_inmobiliaria"><i
                                                    class="bi bi-house-fill icon_pertenece_inmobiliaria"></i>
                                                {{ $cliente->nombre_de_inmobiliaria }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        <!-- Mensaje opcional cuando no hay resultados -->
                        <div id="noResults" class="alert alert-info mx-3 mt-2 sin_resultados">
                            No se encontraron contactos.
                        </div>
                    </ul>
                </div>
            </div>
            {{-- ---------------------------------------------------------------------------------------------------------- --}}
            <!-- Criterios (estilo contactos) -->
            @php
                $prioridad = [
                    null => 0, // NULL primero
                    'Potable' => 1,
                    'Medio' => 2,
                    'No Potable' => 3,
                    // cualquier otra categoría activa, si existiera, podría ir aquí
                ];

                $clientesCriterios = [];
                foreach ($clientes as $cliente) {
                    $clientesCriterios[$cliente->id_cliente] = $criterios_venta
                        ->where('id_cliente', $cliente->id_cliente)
                        ->sortBy(function ($c) use ($prioridad) {
                            if ($c->estado_criterio_venta !== 'Activo') {
                                return [4, 0];
                            }
                            return [$prioridad[$c->id_categoria] ?? 99, -strtotime($c->fecha_criterio_venta)];
                        });
                }

                $tipoInmueble = $tipo_inmueble[$criterio->id_tipo_inmueble - 1]->inmueble ?? 'Tipo no especificado';
                $zonaS = $zona->find($criterio->id_zona)->name;
            @endphp
            <div class="col-md-2 lista_criterios">
                <!-- Titulo criterios -->
                <div class="pb-1 pt-1 px-1 borde_inferior" id="criterio-default-div">
                    <h6 id="criterio-default" class="t-inmueble"><strong>CRITERIOS</strong></h6>
                     <button id="agregar-nuevo-criterio" class="t-inmueble btn-agregar-nuevo-criterio"
                        style="display:none;" data-telefono=""></button> 
                    <span id="tipo-inmueble-seleccionado" class="badge bg-primary t-inmueble" style="display:none;"></span>

                </div>




                <div id="criterios-container">
                    @foreach ($clientes as $cliente)
                        <div class="criterio-chat hidden-chat" id="chat{{ $cliente->id_cliente }}">
                            <div class="pb-1 pt-1 px-1 borde_inferior">
                                <h6 id="criterio-default-{{ $cliente->id_cliente }}" class="t-inmueble"
                                    style="display:none;"><strong>CRITERIOS</strong></h6>
                                 <button onclick="agregarNuevoCriterio('{{ $cliente->id_cliente }}')"
                                    id="agregar-nuevo-criterio-{{ $cliente->id_cliente }}"
                                    class="t-inmueble btn-agregar-nuevo-criterio" style="display:none;"
                                    data-telefono="{{ $cliente->telefono }}"></button> 
                                <span id="tipo-inmueble-seleccionado-{{ $cliente->id_cliente }}"
                                    class="badge bg-primary t-inmueble" style="display:none;"></span>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach ($clientesCriterios[$cliente->id_cliente] as $criterio)
                                    <li class="list-group-item list-group-item-action criterio"
                                        onclick="mostrarEstado('{{ $criterio->id_criterio_venta ?? 'Estado no especificado' }}')"
                                        id="criterio_seleccionado" data-id-criterio="{{ $criterio->id_criterio_venta }}"
                                        {{-- data-tipo-inmueble="{{ $criterio->id_tipo_inmueble }}" --}}
                                        data-tipo-inmueble="{{ $tipo_inmueble[$criterio->id_tipo_inmueble - 1]->inmueble ?? 'Tipo no especificado' }}"
                                        data-cant-dormitorios="{{ $criterio->cant_dormitorios }}"
                                        data-cochera="{{ $criterio->cochera }}" {{-- data-zona="{{ $criterio->zonaS }}" --}}
                                        data-zona="{{ $zona[$criterio->id_zona - 1]->name ?? 'Zona no especificada' }}">

                                        <!-- Detalles del criterio -->
                                        <div class = "row asesor_criterio_cliente">
                                            <div class="col-3 asesor_criterio_cliente_titulo"><strong>Tipo:</strong>
                                            </div>
                                            <div class="col-9">
                                                {{ $tipo_inmueble[$criterio->id_tipo_inmueble - 1]->inmueble ?? 'Tipo no especificado' }}
                                            </div>

                                            <div class="col-3 asesor_criterio_cliente_titulo"><strong>Dorm:</strong>
                                            </div>
                                            <div class="col-9">{{ $criterio->cant_dormitorios ?? 'No especificado' }}
                                            </div>

                                            <div class="col-3 asesor_criterio_cliente_titulo"><strong>Estado:</strong>
                                            </div>
                                            <div class="col-9">
                                                {{ $criterio->estado_criterio_venta ?? 'No especificado' }}
                                            </div>

                                            <div class="col-3 asesor_criterio_cliente_titulo"><strong>Fecha:</strong>
                                            </div>
                                            <div class="col-9">
                                                {{ \Carbon\Carbon::parse($criterio->fecha_criterio_venta)->format('d/m/Y') ?? 'No especificado' }}
                                            </div>

                                            <div class="col-3 asesor_criterio_cliente_titulo"><strong>Precio</strong>
                                            </div>
                                            <div class="col-9">
                                                {{ $criterio->precio_hasta ?? 'No especificado' }}
                                            </div>

                                            <div class="col-12 row g-0 p-0 asesor_criterio_cliente_botones">
                                                <!-- Boton del criterio -->
                                                <div class="col-6 d-flex align-items-center justify-content-center pb-1">
                                                    <button type="button"
                                                        class="btn p-0 border-0 bg-transparent editar-criterio-btn"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditarCriterio"
                                                        data-id-criterio="{{ $criterio->id_criterio_venta }}"
                                                        data-categoria="{{ $criterio->id_categoria }}"
                                                        data-tipo="{{ $criterio->id_tipo_inmueble }}"
                                                        data-zona="{{ $criterio->id_zona }}"
                                                        data-dorm="{{ $criterio->cant_dormitorios }}"
                                                        data-cochera="{{ $criterio->cochera }}"
                                                        data-observaciones="{{ $criterio->observaciones_criterio_venta }}"
                                                        data-estado="{{ $criterio->estado_criterio_venta }}"
                                                        data-precio-hasta="{{ $criterio->precio_hasta }}"
                                                        onclick="event.stopPropagation();">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                </div>
                                                <!-- Potabilidad del criterio -->
                                                <div class="col-6 d-flex align-items-center justify-content-center">
                                                    @if ($criterio->estado_criterio_venta == 'Activo')
                                                        @if ($criterio->id_categoria == 'Potable')
                                                            <i
                                                                class="fa-regular fa-face-grin-beam text-success icono_potabilidad">
                                                            </i>
                                                        @elseif ($criterio->id_categoria == 'Medio')
                                                            <i
                                                                class="fa-regular fa-face-grimace text-warning icono_potabilidad"></i>
                                                        @elseif ($criterio->id_categoria == 'No Potable')
                                                            <i
                                                                class="fa-regular fa-face-angry text-danger icono_potabilidad"></i>
                                                        @else
                                                            <i
                                                                class="fa-regular fa-pen-to-square text-dark icono_potabilidad"></i>
                                                        @endif
                                                    @else
                                                        <i
                                                            class="fa-regular fa-folder-closed icono_potabilidad naranja"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- ---------------------------------------------------------------------- --}}
            <!-- Conversación y Códigos -->
            <div class="col-md-6">

                <div class="px-2 separador_conversacion">
                    <!-- Header conversación -->
                    <div class="px-1 py-1 borde_inferior">
                        <h6 id="criterio-default-conversacion" class="t-inmueble"><strong>CONVERSIÓN</strong></h6>
                        <span id="tipo-inmueble-seleccionado-conversacion" class="badge bg-primary t-inmueble"
                            style="display:none;"></span>
                    </div>
                    <!-- Mensajes -->
                    <div id="conversacion-container" class="flex-grow-1 overflow-auto p-3 scroll">
                        <ul class="list-group list-group-flush">
                            <!-- Conversación se genera por JS -->
                        </ul>
                    </div>
                    <!-- Input estilo WhatsApp -->

                    <div class="p-2" id="input-conversacion" style="display:none;">
                        <form id="form-mensaje" method="POST" action="{{ route('asesores.enviar-mensaje') }}">
                            @csrf
                            <div class="input-group ">
                                <!-- botones de acciones, agenda,buscar,etc -->
                                <div id="action-menu" class="action-menu-container">
                                    <button type="button" class="btn btn-action-menu boton-mas" title="Buscar Propiedad"
                                        data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-action-menu sector-btn boton-mas"
                                        title="Agendar" data-bs-toggle="modal" data-bs-target="#calendarEventModal"
                                        id="Ventas-tab" data-lugar="asesores"><i class="bi bi-calendar-plus"></i></button>

                                    <button type="button" class="btn btn-action-menu boton-mas" title="Recordar"
                                        id="btn-recordatorio"><i class="bi bi-bell"></i></button>
                                </div>
                                <!-- Botón + para acciones -->
                                <button type="button" id="action-menu-button" class="btn ms-2 px-3 boton-mas">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <input type="text" name="mensaje"
                                    class="form-control rounded-pill px-3 input-texto ms-2"
                                    placeholder="Escribe un mensaje...">

                                {{-- <input type="hidden" name="id_cliente" id="input-id-cliente"> --}}
                                <input type="hidden" name="fecha_hora" value="{{ now() }}">
                                <input type="hidden" name="id_criterio_venta" id="input-id-criterio">
                                <input type="hidden" name="last_modified_by" id="input-last-modified-by"
                                    value="{{ session()->get('usuario_id') }}">

                                <button type="submit" class="btn ms-2 px-3 boton-enviar">
                                    <i class="bi bi-send"></i> <!-- Bootstrap icons -->
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- ---------------------------------Codigo---------------------------- --}}
            </div>
            <div class="col-md-2">
                <div class="p-1 borde_inferior t-inmueble">
                    <h6><strong>CODIGOS</strong></h6>
                </div>
                <div id="codigos-container">
                    <ul class="list-group list-group-flush" id="codigo-list">
                        <!-- Códigos por JS -->
                    </ul>
                </div>
            </div>
        </div>

        @include('clientes.gestionasesores.modal-asesores.modal-editar-persona')

        @include('clientes.gestionasesores.modal-asesores.modal-busqueda-propiedades')

        @include('clientes.gestionasesores.modal-asesores.modal-recordatorio')

        @include('clientes.gestionasesores.modal-asesores.modal-agenda')

        @include('clientes.gestionasesores.modal-asesores.modal-editar-criterio')

        @include('clientes.gestionasesores.modal-asesores.modal-devoluciones')

    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/mostrar-criterios.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/filtrar-telefonos.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/menu-de-opciones.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/busqueda-propiedades.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/recordatorio-manager.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/modal-editar-criterio.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/modal-editar-persona.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/modal-agenda.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/cambiar-criterio-por-nombre.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/mensajes-codigos.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/mostrar-mensajes-agenda.js') }}"></script>
    <script src="{{ asset('js/agenda/selector-busqueda.js') }}"></script>
    <script src="{{ asset('js/agenda/autocompletado-datos.js') }}"></script>
    <script src="{{ asset('js/cliente/asesores/filtrados-clientes.js') }}"></script>

    <script>
        // Configuración de URLs para JavaScript
        // Configuración global para los módulos JS
        window.AsesoresConfig = {
            urls: {
                getConversacion: "{{ route('asesores.get-conversacion', ['criterioId' => '__CRITERIO_ID__']) }}",
                recordatorioStore: "{{ route('recordatorio.store') }}",
                propiedadesBuscar: "{{ url('/propiedades/buscar-codigo') }}"
            }
        };
        window.RUTA_CLIENTE = "{{ url('/clientes/buscar-telefono') }}";
        window.RUTA_PROPIEDAD = "{{ url('/propiedades/buscar-codigo') }}";
        window.RUTA_CALLE = "{{ url('/buscar-calle') }}";
    </script>


   
@endsection
