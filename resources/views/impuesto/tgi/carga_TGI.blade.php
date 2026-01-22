@extends('layout.nav')

@section('title', 'Cargar TGI')

@section('content')
<div class="container-fluid" style="max-height: 80vh;">
    <div class="row">
        <form action="{{ route('carga_tgi') }}" method="GET" class="row gy-1 gx-2 align-items-center mb-2" autocomplete="off">
            <!-- Mes (2 dígitos) -->
            <div class="col-auto">
                <input type="number" name="mes" class="form-control form-control-sm" placeholder="Mes"
                    value="{{ request('mes') }}" min="1" max="12" id="mes"
                    oninput="this.value = this.value.slice(0, 2)"
                    style="width: 70px;">
            </div>

            <!-- Año (4 dígitos) -->
            <div class="col-auto">
                <input type="number" name="anio" class="form-control form-control-sm" placeholder="Año"
                    value="{{ request('anio') }}" id="anio"
                    oninput="this.value = this.value.slice(0, 4)"
                    style="width: 90px;">
            </div>

            <!-- Folio (hasta 8 números) -->
            <div class="col-auto">
                <input type="number" name="folio" class="form-control form-control-sm" placeholder="Folio"
                    value="{{ request('folio') }}" maxlength="8"
                    pattern="\d{1,8}" title="Máximo 8 números"
                    style="width: 120px;">
            </div>

            <!-- Partida / Clave (hasta 15 números) -->
            <div class="col-auto">
                <input type="number" name="busqueda" class="form-control form-control-sm" placeholder="Partida / Clave"
                    value="{{ request('busqueda') }}" maxlength="15"
                    pattern="\d{1,15}" title="Máximo 15 números"
                    style="width: 150px;">
            </div>

            <!-- Estado (select) -->
            <div class="col-auto">
                <select name="estado" id="estado" class="form-select form-select-sm" style="width: 110px;">
                    <option value="">Todos</option>
                    <option value="INACTIVO" {{ request('estado') == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                    <option value="ACTIVO" {{ request('estado') == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                </select>
            </div>

            <!-- Bajado (select) -->
            <div class="col-auto">
                <select name="bajado" id="bajado" class="form-select form-select-sm" style="width: 110px;">
                    <option value="S" {{ request('bajado') == 'S' ? 'selected' : '' }}>Todos</option>
                    <option value="N" {{ request('bajado') == 'N' ? 'selected' : '' }}>N</option>
                </select>
            </div>

            <!-- Botón -->
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>






    <div class="d-flex justify-content-between align-items-end mb-3">
        {{-- Formulario original --}}
        <form method="POST" action="{{ route('cargarNuevoTGI') }}" class="d-flex align-items-end gap-2">
            @csrf
            <div class="flex-grow-1">
                <label for="codigo_barras" class="form-label mb-1">Código de Barras</label>
                <input type="text" name="codigo_barras" id="codigo_barras"
                    class="form-control form-control-sm" placeholder="Código de Barras">
            </div>
        </form>

        {{-- IMPORTANTE: el modal va fuera del form padre --}}
        @include('impuesto.tgi.modales.modal_cargaManualTGI')
        @if(request('anio') && request('mes'))
        <div class="d-flex gap-2  justify-content-end">
            <div class="dropdown">
                <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="dropdownMenuOpciones" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear-fill"></i> Más opciones
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuOpciones">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-success" href="{{ route('exportar_tgi_faltantes', ['anio' => request('anio'), 'mes' => request('mes')]) }}">
                            <i class="bi bi-file-earmark-text"></i> Exportar faltantes
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-success" href="#" data-bs-toggle="modal" data-bs-target="#modalArmarBroches">
                            <i class="bi bi-diagram-3-fill"></i> Armar broches
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-success"
                            href="{{ route('exportar_broches', ['anio' => request('anio'), 'mes' => request('mes')]) }}"
                            target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar broches PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-success"
                            href="{{ route('exportar_broches_salas', ['anio' => request('anio'), 'mes' => request('mes')]) }}"
                            target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar broches PDF SALAS
                        </a>
                    </li>

                    <!-- Ejecuta la ruta modificar_bajado para modificar el bajado de los registros de tgi_carga que tengan num_broche por el mes y año indicados -->
                    @if($resultadoPermisoBoton)
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 text-success" href="{{ route('modificarBajado', ['anio' => request('anio'), 'mes' => request('mes')]) }}">
                            <i class="bi bi-clipboard2-check-fill"></i> Modificar bajado
                        </a>
                    </li>
                    @endif


                </ul>
            </div>




            <!-- Modal para armar broches -->
            <!-- Modal para armar broches -->
            <div class="modal fade" id="modalArmarBroches" tabindex="-1" aria-labelledby="modalArmarBrochesLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content shadow-sm">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalArmarBrochesLabel">
                                <i class="bi bi-diagram-3-fill me-2"></i> Armar Broches
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-4">
                                    <!-- Columna TGI -->
                                    <div class="col-md-6">
                                        <h6 class="text-primary">TGI - Administra L</h6>
                                        <p class="fw-bold text-muted" id="monto_total">Monto Total: —</p>

                                        <label for="cant_broches" class="form-label">Cantidad de broches</label>
                                        <div class="input-group mb-2">
                                            <input type="number" name="num_broches" id="cant_broches" class="form-control form-control-sm" placeholder="Ej: 3">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn_calculaBroches">
                                                <i class="bi bi-calculator"></i> Calcular
                                            </button>
                                        </div>

                                        <label class="form-label mt-3">Resultado</label>
                                        <ul class="list-group" id="contenedor_resultado_broche">
                                            <!-- JS genera aquí los broches -->
                                        </ul>
                                    </div>

                                    <!-- Columna Salas -->
                                    <div class="col-md-6">
                                        <h6 class="text-success">TGI - Salas</h6>
                                        <p class="fw-bold text-muted" id="monto_total_salas">Monto Total Salas: —</p>

                                        <label for="cant_broches_salas" class="form-label">Cantidad de broches</label>
                                        <input type="text" id="cant_broches_salas" class="form-control form-control-sm mb-3" value="1" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer con botones alineados -->
                            <div class="modal-footer justify-content-between">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_guardar_broches_tgi">
                                    <i class="bi bi-save"></i> Guardar Broches TGI
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm" id="btn_guardar_broches_salas">
                                        <i class="bi bi-save"></i> Guardar Broches Salas
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>
        @endif

    </div>





    <div class="table-responsive" style="height: 75%; overflow-y: auto;">

        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Código de Barras</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Folio</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Partida</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Clave</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Adm</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Monto</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Vencimiento</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Bajado</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $registro)
                @php
                $folioActual = $registro->padron->folio ?? null;
                $compartidos = json_decode($registro->compartidos ?? '[]', true);
                $esCompartido = in_array($folioActual, $compartidos);
                $estaVencido = \Carbon\Carbon::parse($registro->rescicion)->lt(now());
                @endphp

                <tr>
                    <td class="codigo_barras">{{ $registro->codigo_barra }}</td>

                    <td class="texto_tabla">
                        @foreach($compartidos as $compartido)
                        <label class="{{ $compartido['estado'] !== 'ACTIVO' ? 'text-danger' : 'text-success' }}">
                            {{ $compartido['folio'] }}
                        </label>
                        @if (!$loop->last), @endif
                        @endforeach
                    </td>

                    <td class="texto_tabla">{{ $registro->padron->partida ?? '' }}</td>
                    <td class="texto_tabla">{{ $registro->padron->clave ?? '' }}</td>
                    <td class="texto_tabla">{{ $registro->padron->administra ?? '' }}</td>
                    <td class="texto_tabla">{{ $registro->importe }}</td>
                    <td class="texto_tabla">{{ \Carbon\Carbon::parse($registro->fecha_vencimiento)->format('d/m/Y') }}</td>

                    <td class="texto_tabla">{{ $registro->bajado ?? '' }}</td>
                    {{-- @dd($registro) --}}

                    <!-- Acciones -->
                    <td class="texto_tabla" style="position: relative;">
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm p-0" type="button" id="dropdownMenuButton{{ $registro->id }}" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $registro->id }}">
                                <li>
                                    <button type="button" class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#modalEstado{{ $registro->id }}">
                                        Modificar estado
                                    </button>
                                </li>
                                <li>
                                    <form action="{{ route('eliminarRegistro', $registro->id) }}" method="POST" class="form-eliminar" autocomplete="off">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="anio" value="{{ request('anio') }}">
                                        <input type="hidden" name="mes" value="{{ request('mes') }}">
                                        <input type="hidden" name="busqueda" value="{{ request('busqueda') }}">
                                        <button type="submit" class="dropdown-item text-danger" title="Eliminar">
                                            Eliminar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                        <!-- Modal único por registro -->
                        <div class="modal fade" id="modalEstado{{ $registro->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $registro->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form action="{{ route('modificarEstadoTGI', $registro->id) }}" method="POST" autocomplete="off">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel{{ $registro->id }}">Modificar estado</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <!-- cartel pequeño de aviso (si cambias un estado debes volver a generar el broche) -->
                                        <!-- <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <strong>Atención!</strong> Si el broche fue generado, debes volver a generarlo.
                                        </div> -->

                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="estado{{ $registro->id }}">Estado</label>
                                                <select name="estado" id="estado{{ $registro->id }}" class="form-select">
                                                    <option value="ACTIVO">Activo</option>
                                                    <option value="INACTIVO">Inactivo</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>



</div>
@endsection

@section('scripts')
<script type="module" src="{{ asset('js/impuesto/Tgi/cargaTGI.js') }}"></script>


@endsection