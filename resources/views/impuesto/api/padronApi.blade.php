@extends('layout.nav')

@section('title', 'Asignar Permisos a Usuario')

@section('content')
<div class="container">
    <h1>Padron API</h1>
    <div class="row">
        <div class="col-auto" style="margin-bottom: 5px;">
            <a href="{{ route('actualizar_padron_api') }}" class="btn btn-sm btn-primary" id="btnActualizarPadron">
                Actualizar Padrón API
            </a>
        </div>

        <br>

        <form action="{{ route('padron_api') }}" method="GET" class="row mb-3" autocomplete="off">
            @csrf
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por folio, calle, partida..." value="{{ request('search') }}">
            </div>


            <div class="col-md-4">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        Filtrar opciones
                    </button>
                    <div class="dropdown-menu p-3" style="min-width: 250px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="filtros[]" value="ACTIVO" id="activos"
                                {{ in_array('ACTIVO', request('filtros', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activos">Activos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="filtros[]" value="INACTIVO" id="inactivos"
                                {{ in_array('INACTIVO', request('filtros', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="inactivos">Inactivos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="filtros[]" value="L" id="admInmobiliario"
                                {{ in_array('L', request('filtros', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="admInmobiliario">Adm inmobiliario</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="filtros[]" value="P" id="admPropietario"
                                {{ in_array('P', request('filtros', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="admPropietario">Adm propietario</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="filtros[]" value="I" id="admInquilino"
                                {{ in_array('I', request('filtros', [])) ? 'checked' : '' }}>
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

    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Folio</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Calle</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Partida</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Administra</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Estado</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Comienza</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Rescicion</th>
                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($padron as $padron)
                
                <tr @class([ 'table' , 'table-hover' , 'table-danger'=> strtolower($padron->estado) === 'inactivo',
                    ])>
                    <td>
                        @if($padron->empresa == 3)
                        T-{{ $padron->folio }}
                        @elseif($padron->empresa == 2)
                        CAN-{{ $padron->folio }}
                        @else
                        {{ $padron->folio }}
                        @endif
                    </td>
                    <td>{{ $padron->calle }}</td>
                    <td>{{ $padron->partida }}</td>
                    <td>{{ $padron->administra }}</td>
                    <td>{{ $padron->estado }}</td>
                    <td>{{ \Carbon\Carbon::parse($padron->comienza)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($padron->rescicion)->format('d/m/Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditar"
                            data-id="{{ $padron->id }}"
                            data-folio="{{ $padron->folio }}"
                            data-calle="{{ $padron->calle }}"
                            data-partida="{{ $padron->partida }}"
                            data-estado="{{ $padron->estado }}"
                            data-administra="{{ $padron->administra }}">
                            Modificar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- modal para modificar registro -->

<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('padron_api.actualizar') }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Modificar Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Fila 1 -->
                        <div class="col-md-4 mb-3">
                            <label for="edit-folio" class="form-label">Folio</label>
                            <input type="text" class="form-control" name="folio" id="edit-folio">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit-calle" class="form-label">Calle</label>
                            <input type="text" class="form-control" name="calle" id="edit-calle">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit-partida" class="form-label">Partida</label>
                            <input type="text" class="form-control" name="partida" id="edit-partida">
                        </div>
                    </div>
                    <div class="row">
                        <!-- Fila 2 -->
                        <div class="col-md-4 mb-3">
                            <label for="edit-clave" class="form-label">Clave</label>
                            <input type="text" class="form-control" name="clave" id="edit-clave">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit-estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="edit-estado">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit-administra" class="form-label">Administra</label>
                            <select class="form-select" name="administra" id="edit-administra">
                                <option value="P">Propietario</option>
                                <option value="L">Inmobiliario</option>
                                <option value="I">Inquilino</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection


@section('scripts')
<script type="module" src="{{ asset('js/impuesto/Tgi/padronTGI.js') }}"></script>
@endsection