<!-- filepath: c:\xampp\htdocs\salas\salas\resources\views\impuesto\expensas\Carga_Administrador.blade.php -->
@extends('layout.nav')

@section('title', 'Unidades Expensas')

@section('content')
    <div class="px-3">
        <h1>Padron Unidades</h1>
        <div class="row">

            <div class="col-md-10">
                <form action="{{ route('exp_unidades.filtro.completo') }}" method="GET" class="row mb-3" autocomplete="off">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Buscar por nombre, cuit, telefono..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false">
                                Filtrar opciones
                            </button>
                            <div class="dropdown-menu p-3" style="min-width: 250px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filtros[]" value="ACTIVO"
                                        id="activos" {{ in_array('ACTIVO', request('filtros', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activos">Activos</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filtros[]" value="INACTIVO"
                                        id="inactivos" {{ in_array('INACTIVO', request('filtros', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inactivos">Inactivos</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filtros[]" value="L"
                                        id="admInmobiliario" {{ in_array('L', request('filtros', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="admInmobiliario">Adm inmobiliario</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filtros[]" value="P"
                                        id="admPropietario" {{ in_array('P', request('filtros', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="admPropietario">Adm propietario</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filtros[]" value="I"
                                        id="admInquilino" {{ in_array('I', request('filtros', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="admInquilino">Adm inquilino</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>

            </div>

            <div class="col-md-2" style="margin-bottom: 5px;">
                <a href="{{ route('actualizar_padron_unidades') }}" class="btn  btnSalas" id="btnActualizarPadron">
                    Actualizar Padrón
                </a>
            </div>
        </div>
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr class="text-center" style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                        <th>Folio</th>
                        <th>Ubicacion</th>
                        <th>Com</th>
                        <th>Adm</th>
                        <th>Estado</th>
                        <th>Consorcio</th>
                        <th>Acciones</th>
                        <th></th>
                        <th>Casa</th>
                    </tr>
                </thead>
                <tbody class="table_expensas">
                    @foreach ($unidades as $unidad)
                        @php
                            // Obtener todas las unidades de esta id_casa (ahora es un array/colección)
                            $padrones = isset($unidadesPadronByCasa)
                                ? $unidadesPadronByCasa->get($unidad->casa) ?? collect()
                                : collect();
                            //dd($padrones);
                            $primerPadron = $padrones->first(); // Tomar la primera para mostrar en tabla
                        @endphp
                        {{-- @dd($padrones); --}}
                        <tr @class([
                            'table',
                            'table-hover',
                            'table-danger' => ($unidad->estado ?? '') === 'Inactivo',
                        ])>
                            <td class="text-center align-middle table_folio">{{ $unidad->folio }}</td>
                            <td class="text-start align-middle table_ubicacion">{{ $unidad->ubicacion }}</td>
                            <td class="text-center align-middle">{{ $unidad->comision }}</td>
                            <td class="text-center align-middle">{{ $unidad->administra }}</td>
                            <td class="text-center align-middle">{{ $unidad->estado }}</td>
                            <td class="text-center align-middle">
                                {{ optional($edificios->firstWhere('id', $primerPadron->id_edificio ?? null))->nombre_consorcio ?? 'No Asignado' }}
                            </td>
                            {{--  @dd($padrones) --}}
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditarUnidad" data-id="{{ $unidad->folio ?? '' }}"
                                    data-casa="{{ $unidad->casa ?? '' }}"
                                    data-ubicacion="{{ $unidad->ubicacion ?? '' }}"
                                    data-estado="{{ $unidad->estado ?? '' }}"
                                    data-administra="{{ $unidad->administra ?? '' }}"
                                    data-comision="{{ $unidad->comision ?? '' }}"
                                    data-padrones='@json($padrones)'>
                                    Modificar
                                </button>
                            </td>
                            <td class="text-center align-middle">
                                @if ($padrones)
                                    @php
                                        $observacionesCount = $padrones->whereNotNull('observaciones')->count();
                                    @endphp
                                    @if ($observacionesCount > 0)
                                        <i class="fa-regular fa-comment"
                                            title="{{ $observacionesCount }} observación(es)"></i>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center align-middle">{{ $unidad->casa }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Cargar datos completos de una unidad --}}
    @include('impuesto.expensas.modales-expensas.modal-cargar-unidad')

    {{-- Modal Mensaje --}}
    @include('impuesto.expensas.modales-expensas.modal-cargar-unidad-mensaje')

    <script>
        // Cargar comentario cuando se abre el modal de comentario
        document.getElementById('modalComentario').addEventListener('show.bs.modal', function(event) {
            // Obtener el valor actual del campo oculto (lo que ya se guardó antes)
            const comentarioGuardado = document.getElementById('comentarioHidden').value;
            document.getElementById('comentarioTextarea').value = comentarioGuardado || '';
        });
        // Limpiar comentarios cuando se abre el modal principal desde la tabla
        document.getElementById('modalEditarUnidad').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (button && button.hasAttribute('data-id')) {
                var folio = button.getAttribute('data-id') || '';
                var comision = button.getAttribute('data-comision') || '';
                var administra = button.getAttribute('data-administra') || '';
                var ubicacion = button.getAttribute('data-ubicacion') || '';
                var id_casa = button.getAttribute('data-casa') || '';
                var padronesAttr = button.getAttribute('data-padrones');
                var estadoContrato = button.getAttribute('data-estado') || '';


                //console.log('Estado recibido:', estadoContrato);

                let padrones = [];

                if (padronesAttr) {
                    try {
                        padrones = JSON.parse(padronesAttr);
                        // Verificar si hay padrones y si el primer elemento tiene id_edificio
                        if (padrones && padrones.length > 0 && padrones[0].hasOwnProperty('id_edificio')) {
                            const edificioSelect = document.getElementById('edificioSelect');
                            if (edificioSelect) {
                                edificioSelect.value = padrones[0].id_edificio;
                            }
                        }
                        console.log('Padrones recibidos:', padrones);
                    } catch (e) {
                        console.error('Error al parsear padrones:', e);
                        padrones = [];
                    }
                }

                document.getElementById('folioInput').value = folio;
                document.getElementById('comisionInput').value = comision;
                document.getElementById('administraInput').value = administra;
                document.getElementById('ubicacionInput').value = ubicacion;
                document.getElementById('idInput').value = id_casa;

                if (estadoContrato === 'Activo') {
                    document.getElementById('estadoSelect').value = 'Activo';
                } else if (estadoContrato === 'Inactivo') {
                    document.getElementById('estadoSelect').value = 'Inactivo';
                }



                // Limpiar el contenedor de unidades
                const contenedorUnidades = document.getElementById('unidades');
                contenedorUnidades.innerHTML = '';

                // Si hay padrones, cargar cada uno
                if (padrones.length > 0) {
                    padrones.forEach((padron, index) => {
                        crearUnidadConDatos(index, padron);
                    });

                    // Actualizar el contador para los nuevos que se agreguen
                    contador = padrones.length;
                } else {
                    // Si no hay padrones, crear uno vacío
                    crearUnidadVacia(0);
                    contador = 1;
                }
            }
        });
    </script>
@endsection

@section('scripts')
    <script src="{{ asset('js/impuesto/Exp/Unidad/alertaPadronUnidades.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/agregarUnidad.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/eliminarUnidad.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/abrirModalComentario.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/cerrarModalComentario.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/guardarComentarioYVolver.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/crearUnidadConDatos.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Unidad/crearUnidadVacia.js') }}"></script>
@endsection
