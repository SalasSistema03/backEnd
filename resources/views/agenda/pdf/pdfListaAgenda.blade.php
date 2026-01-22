@extends('layout.nav')
@section('title', 'Listar Alquiler')
@section('content')
    <div class="d-flex">
        <!-- Contenedor Izquierdo: Listado con Scroll -->
        <div class="left-panel col-3">
            <h2 class="h5 mb-4">Opciones</h2>
            <div>
                <div class="option-button" onclick="loadForm('propiedades_ventas')">
                    Listar agenda de Ventas
                </div>
            </div>
            <div>
                <div class="option-button" onclick="loadForm('propiedades_alquiler')">
                    Listar agenda de Alquiler
                </div>
            </div>
        </div>

        <!-- Contenedor Derecho: Contenido Estático (Formularios Predefinidos) -->
        <div class="right-panel col-9">
            <div id="content">
                <!-- Mensaje de Bienvenida -->
                <div id="welcome" class="welcome">
                    <h1 class="mb-4">Bienvenido</h1>
                    <p>Selecciona una opción del listado a la izquierda para cargar un formulario.</p>
                </div>


                <!-- Formulario de Registro ventas -->
                <div id="propiedades_ventas" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar agenda de Ventas</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstadosPropietario" method="GET"
                                action="{{ route('propiedades.asesorview') }}" target="_blank" autocomplete="off">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="fecha-inicio" class="form-label">Fecha inicio</label>
                                        <input type="date" name="fecha-inicio" class="form-control">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="fecha-fin" class="form-label">Fecha fin</label>
                                        <input type="date" name="fecha-fin" class="form-control">
                                    </div>
                                    {{-- @dd($asesores);  --}}
                                    <div class="form-group col-md-3">
                                        <label for="asesor" class="form-label">Asesor</label>
                                        <select name="asesor" class="form-control">
                                            <option value="">Seleccione un asesor</option>
                                            @foreach ($asesores as $asesor)
                                                <option value="{{ $asesor['id_usuario'] }}">{{ $asesor['username'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="activas" class="form-label">Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="1">Activas</option>
                                            <option value="0">Inactivas</option>
                                            <option value="2">Todas</option>
                                        </select>
                                    </div>


                                </div>
                                <input type="hidden" name="pertenece" value="PropiedadesxAsesorV">
                                @if ($tieneAccesoVenta)
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                    </div>
                                @else
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2" disabled>Listar</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                </div>
                <!-- Formulario de Registro alquiler -->
                <div id="propiedades_alquiler" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar agenda de Alquiler</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstadosPropietario" method="GET"
                                action="{{ route('propiedades.asesorview') }}" target="_blank" autocomplete="off">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="fecha-inicio" class="form-label">Fecha inicio</label>
                                        <input type="date" name="fecha-inicio" class="form-control">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="fecha-fin" class="form-label">Fecha fin</label>
                                        <input type="date" name="fecha-fin" class="form-control">
                                    </div>
                                    {{-- @dd($asesores);  --}}
                                    <div class="form-group col-md-3">
                                        <label for="asesor" class="form-label">Asesor</label>
                                        <select name="asesor" class="form-control">
                                            <option value="">Seleccione un asesor</option>
                                            @foreach ($alquilerAsesor as $alquiler)
                                                <option value="{{ $alquiler['id_usuario'] }}">{{ $alquiler['username'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="activas" class="form-label">Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="1">Activas</option>
                                            <option value="0">Inactivas</option>
                                            <option value="2">Todas</option>
                                        </select>
                                    </div>

                                </div>
                                <input type="hidden" name="pertenece" value="PropiedadesxAsesorA">
@if($tieneAccesoAlquiler)
                                <div class="col-md-12 bg-transparent border-primary mt-2">
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                </div>
@else
                                <div class="col-md-12 bg-transparent border-primary mt-2">
                                    <button type="submit" class="btn btn-primary w-100 mt-2" disabled>Listar</button>
                                </div>
@endif
                            </form>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    @endsection

    @section('scripts')
        <script>
            function loadForm(formType) {
                // Ocultar todas las secciones en el contenedor derecho
                const sections = document.querySelectorAll('#content > div');
                sections.forEach(section => section.style.display = 'none');
                // Mostrar la sección correspondiente
                const target = document.getElementById(formType);
                if (target) {
                    target.style.display = 'block';
                } else {
                    // Si no hay formulario válido, mostrar bienvenida
                    document.getElementById('welcome').style.display = 'block';
                }
            }
        </script>
        <script>
            // Oculta el spinner apenas se carga esta vista y al enviar el formulario
            document.addEventListener('DOMContentLoaded', function() {
                // Mostrar mensaje de bienvenida inicialmente
                document.getElementById('welcome').style.display = 'block';
                var spinner = document.querySelector('.spinner-wrapper');
                if (spinner) spinner.style.display = 'none';

                var form = document.getElementById('formEstadosPropietario');
                if (form) {
                    form.addEventListener('submit', function() {
                        if (spinner) spinner.style.display = 'none';
                    });
                }
            });
        </script>

        <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
    @endsection
