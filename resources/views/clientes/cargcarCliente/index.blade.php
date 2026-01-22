@extends('layout.nav')

@section('content')

<div class="container-fluid">
    <div class="row row-half">
        <div class="col-md-6 ">
            <div class="table-container">
                <form id="form_cliente">
                    <div class="contenedor_listados contenedor_datos_cliente">
                        <div class="d-flex justify-content-center contenedor_titulo">
                            <div class="d-flex align-items-center justify-content-center itulos_contenedores">
                                <span class="fw-semibold">Datos del cliente</span>
                            </div>
                        </div>
                        <input type="hidden" name="usuario_id" value="{{ session('usuario_id') }}">
                        <div class="row form-group">
                            <div class="col-md-4">
                                <label for="telefono">Telefono</label>
                                <input type="number" class="form-control" name="telefono" id="telefono" require>
                            </div>
                            <div class="col-md-4">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" require>
                            </div>

                            <div class="col-md-4">
                                @if($resultadoPermisoBoton)
                                <label>Asignar asesor</label>
                                <select name="id_asesor" id="id_asesor_cliente" class="form-control custom-select-style" required>
                                    <option value="" selected>Seleccionar</option>
                                    @foreach($usuarioSectors as $usuarioSector)
                                    @if($usuarioSector->venta === 'S')
                                    <option value="{{ $usuarioSector->id_usuario }}"
                                        data-venta="{{ $usuarioSector->venta }}"
                                        data-alquiler="{{ $usuarioSector->alquiler }}">
                                        {{ $usuarioSector->usuario_username }}
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                                @endif

                                @if(!$resultadoPermisoBoton)
                                <label>Asignar asesor</label>
                                <select name="id_asesor" id="id_asesor_cliente" class="form-control custom-select-style" required>
                                    {{-- Aquí, el valor es el ID y el texto visible es el nombre de usuario --}}
                                    <option value="{{ $usuario->id }}">
                                        {{ $usuario->username }}
                                    </option>
                                </select>
                                @endif

                            </div>


                        </div>

                        <div class="row form-group">

                            <div class="col-md-4">
                                <label>Ingreso por</label>
                                <select name="ingreso" id="ingreso" class="form-control">
                                    <option value="" selected disabled>Seleccionar</option>
                                    <option value="Correo">Correo</option>
                                    <option value="Difusion">Difusión</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Presencial Candioti">Presencial Candioti</option>
                                    <option value="Presencial Tribunales">Presencial Tribunales</option>
                                    <option value="Recomendación">Recomendación</option>
                                    <option value="Sitio web">Sitio web</option>
                                    <option value="Telefonicamente">Telefónicamente</option>
                                    <option value="Telefonicamente Candioti">Telefónicamente Candioti</option>
                                    <option value="Telefónicamente Tribunales">Telefónicamente Tribunales</option>
                                    <option value="Zona Prop">Zona Prop</option>
                                    <option value="whatsapp">whatsapp</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>¿Pertenece a un inmobiliaria?</label>
                                <select id="pertenece_a_inmobiliaria" name="pertenece_a_inmobiliaria" class="form-control">
                                    <option value="N" selected>No</option>
                                    <option value="S">Sí</option>
                                </select>
                            </div>

                            <div class="col-md-4" id="nombre_pertenece_a_inmobiliaria" style="display: none;">
                                <label>Nombre de la inmobiliaria</label>
                                <input type="text" class="form-control" name="nombre_de_inmobiliaria" id="nombre_de_inmobiliaria">
                            </div>

                        </div>


                        <div class="form-group">
                            <div class="row">

                                <div id="col-observaciones" class="col-md-12">
                                    <label>Observaciones del cliente</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                @include('clientes.cargcarCliente.criterio_busqueda_cliente')

                <!-- Botones de acciones -->
                <div id="contenedor_acciones">
                    <button type="button" id="btn_guardar_todo" class="btn btn-success">
                        Guardar
                    </button>
                </div>


            </div>



        </div>



        <div class="col-md-6 ">
            <div class="table-container">
                <div class="contenedor_listados">
                    <!-- Lista propiedades asignadas -->
                    @include('clientes.cargcarCliente.lista_propiedades_asignadas')

                    <!-- Lista criterio de búsqueda -->
                    @include('clientes.cargcarCliente.lista_criterio_busqueda')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








@section('scripts')
<script type="module" src="{{ asset('js/cliente/asignarPropiedades.js') }}"></script>
<script type="module" src="{{ asset('js/cliente/modal_listaPropiedades.js') }}"></script>
<script type="module" src="{{ asset('js/cliente/main.js') }}?v={{ time() }}"></script>
<script type="module" src="{{ asset('js/cliente/validaciones.js') }}"></script>
@endsection