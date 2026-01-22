@extends('layout.nav')
@section('title', 'Listar Alquiler')
@section('content')
<div class="d-flex">
    <!-- Contenedor Izquierdo: Listado con Scroll -->
    <div class="left-panel col-3">
        <h2 class="h5 mb-4">Opciones</h2>
        <div>
            <div class="option-button" onclick="loadForm('propiedades_alquiler')">
                Listar Propiedades en Alquiler
            </div>
            <div class="option-button" onclick="loadForm('propietarios_venta')">
                Listar Propietarios en Alquiler
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


            <!-- Formulario de Registro -->
            <div id="propiedades_alquiler" class="form-section">
                <div class="card border-primary mx-2">
                    <div class="card-header bg-transparent border-primary">
                        <label for="">Listar Propiedades en Alquiler</label>
                    </div>
                    <div class="card-body text-primary">
                        <form class="row" id="formEstado" method="GET"
                            action="{{ route('propiedades.Alquiler.Estados-view') }}" target="_blank" autocomplete="off">
                            <div class="col-md-6">
                                <label class="form-label" for="search-calle">Calle</label>
                                <input type="text" id="search-calle"
                                    class="form-control @error('calle') is-invalid @enderror" placeholder="Buscar calle..."
                                    value="{{ optional(App\Models\At_cl\Calle::find(old('calle')))->name }}">
                                <input type="hidden" id="calle_id" name="calle" value="{{ old('calle') }}">
                                <div id="search-results" class="list-group mt-2" style="position: absolute; z-index: 1000;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Zona</label>
                                <select name="zona_id" id="zona_id" class="form-control">
                                    <option value="">Seleccione una zona</option>
                                    @foreach ($zonas as $zona)
                                    <option value="{{ $zona->id }}">{{ $zona->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="">Seleccione un inmueble</option>
                                    @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->inmueble }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Estado</label>
                                <input type="hidden" name="tipoPdf" value="">
                                <select name="estado_id" id="estado_id" class="form-control">
                                    <option value="">Seleccione un estado</option>
                                    @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Importe desde</label>
                                <input type="number" class="form-control  @error('importe_minimo') is-invalid @enderror"
                                    id="importe_minimo" name="importe_minimo" min="0" placeholder="Importe mínimo"
                                    value="{{ request('importe_minimo') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="" class="form-label">Importe hasta</label>
                                <input type="number" class="form-control @error('importe_maximo') is-invalid @enderror"
                                    id="importe_maximo" name="importe_maximo" min="0" placeholder="Importe máximo"
                                    value="{{ request('importe_maximo') }}">
                            </div>
                            <input type="hidden" name="pertenece" value="estadosAlquiler">
                            @if ($tieneAccesoInformacionAlquiler)
                            <div class="col-md-12 bg-transparent border-primary mt-2">
                                <button type="submit" class="btn btnSalas w-100 mt-2">Listar</button>
                            </div>
                            @else
                            <div class="col-md-12 bg-transparent border-primary mt-2">
                                <button type="submit" class="btn btnSalas w-100 mt-2" disabled>Listar</button>
                            </div>
                            @endif
                        </form>
                    </div>


                </div>
            </div>

            <!-- Formulario de Encuesta -->
            <div id="propietarios_venta" class="form-section">
                <div class="card border-primary mx-2">
                    <div class="card-header bg-transparent border-primary">
                        <label for="">Listar Propietarios en Alquiler</label>
                    </div>
                    <div class="card-body text-primary">
                        <form class="row" id="formEstadosPropietario" method="GET"
                            action="{{ route('propiedades.Alquiler.Estados-view') }}" target="_blank" autocomplete="off">
                            <div class="col-md-12">
                                <label for="" class="form-label">Propietario</label>
                                <input type="text" id="search-propietario" name="propietario" class="form-control"
                                    placeholder="Buscar propietarios por nombre o dni">
                                <input type="hidden" id="propietario_id" name="propietarioo" value="">
                                <input type="hidden" id="propiedad_id" name="propiedad_id"
                                    value="{{ session('propiedad_id') }}">
                                <div id="search-results-prop" class="list-group mt-2"
                                    style="position: absolute; z-index: 1000;"></div>
                            </div>
                            <input type="hidden" name="pertenece" value="estadoPropietarioA">
                            @if ($tieneAccesoPropietario)
                            <div class="col-md-12 bg-transparent border-primary mt-2">
                                <button type="submit" class="btn btnSalas w-100 mt-2">Listar</button>
                            </div>
                            @else
                            <div class="col-md-12 bg-transparent border-primary mt-2">
                                <button type="submit" class="btn btnSalas w-100 mt-2" disabled>Listar</button>
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
        // Pasar los datos de propietarios al archivo JS
        window.propietariosData = @json($propietarios);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCalleSearch(@json($calle));
        });
    </script>
    <script src="{{ asset('js/atencionAlCliente/propiedad/pdf/listar_propietarios_alquiler.js') }}"></script>
    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>
    @endsection