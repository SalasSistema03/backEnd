@extends('layout.nav')

@section('title', 'Cargar Propiedad')

@section('content')
    {{-- @dd($usuariosTotales) --}}
    <div class="px-2 ">
        <form action="{{ route('propiedad.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off"
            id="miFormulario" novalidate class="text-center mx-3">
            @csrf <!-- Token CSRF para seguridad -->
            <!-- Campo oculto para el ID del usuario -->
            <input type="text" name="usuario_id" value="{{ $usuario->id }}" hidden>

            {{-- datos de la propiedad y fotos --}}
            <div class="row datosPropiedad">
                <div class="col-md-12">
                    <div class="row">
                        <!-- Primer bloque de columna -->
                        <div class="col-md-6 d-flex justify-content-center">
                            <div class="row g-1">
                                <div class="col-md-4 ">
                                    <label for="search-calle">Calle</label>
                                    <input type="text" id="search-calle"
                                        class="form-control @error('calle') is-invalid @enderror"
                                        placeholder="Buscar calle..."
                                        value="{{ optional(App\Models\At_cl\Calle::find(old('calle')))->name }}">
                                    <input type="hidden" id="calle_id" name="calle" value="{{ old('calle') }}">
                                    <div id="search-results" class="list-group mt-2"
                                        style="position: absolute; z-index: 1000;">
                                    </div>
                                </div>
                                <div class="col-md-2 ">
                                    <label class="text-center  " id="basic-addon1">Numero</label>
                                    <input name="numero_calle" type="number"
                                        class="form-control @error('numero_calle') is-invalid @enderror"
                                        value="{{ old('numero_calle', request('numero_calle')) }}" id=""
                                        min="0" max="100000">
                                </div>
                                <div class="col-md-2 ">
                                    <label class="text-center" id="basic-addon1">PH</label>
                                    <select class="form-select @error('ph') is-invalid @enderror"
                                        aria-label="Default select example" name="ph">
                                        <option value="">-</option>
                                        <option value="SI" {{ old('ph', request('ph')) == 'SI' ? 'selected' : '' }}>SI
                                        </option>
                                        <option value="NO" {{ old('ph', request('ph')) == 'NO' ? 'selected' : '' }}>NO
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 ">
                                    <label class="text-center" id="basic-addon1">Piso</label>
                                    <input name= "piso" type="number"
                                        class="form-control @error('piso') is-invalid @enderror"
                                        value="{{ old('piso', request('piso')) }}" id="" min="0"
                                        max="163">
                                </div>
                                <div class="col-md-2 ">
                                    <label class="text-center" id="basic-addon1">Depto</label>
                                    <input name="depto" type="text"
                                        class="form-control @error('depto') is-invalid @enderror"
                                        value="{{ old('depto', request('depto')) }}" id="" min= "0"
                                        max="600">
                                </div>
                                <div class="col-md-4">
                                    <label class="text-center" id="basic-addon1">Inmueble</label>
                                    <select class="form-select @error('tipo_inmueble') is-invalid @enderror"
                                        aria-label="Default select example" name="tipo_inmueble">
                                        <option value="">Selec. un inmueble</option>
                                        @foreach ($tipo_inmueble as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ old('tipo_inmueble') == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->inmueble }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 ">
                                    <label class="text-center" id="basic-addon1">Zona</label>
                                    <select class="form-select @error('zona') is-invalid @enderror"
                                        aria-label="Default select example" name="zona">
                                        <option value="">Seleccione una zona</option>
                                        @foreach ($zona as $zona)
                                            <option value="{{ $zona->id }}"
                                                {{ old('zona') == $zona->id ? 'selected' : '' }}>
                                                {{ strtoupper($zona->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-center" id="basic-addon1">Provincia</label>
                                    <select class="form-select @error('provincia') is-invalid @enderror"
                                        aria-label="Default select example" name="provincia">
                                        <option value="">Seleccione una provincia</option>
                                        @foreach ($provincia as $provincias)
                                            <option value="{{ $provincias->id }}"
                                                {{ old('provincia', 20) == $provincias->id ? 'selected' : '' }}>
                                                {{ strtoupper($provincias->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-1">

                                    <div class="col-md-3 ">
                                        <label class="text-center" id="basic-addon1">Llave</label>
                                        <input name="llave" type="number"
                                            class="form-control @error('llave') is-invalid @enderror"
                                            value="{{ old('llave', request('llave')) }}" id="" min="0"
                                            max="100">
                                    </div>
                                    <div class="col-md-9 ">
                                        <label class="text-center" id="basic-addon1">Observacion</label>
                                        <textarea class="form-control w-100 @error('observacion_llave') is-invalid @enderror" name="observacion_llave"
                                            rows="2" placeholder="Escribir comentario de la llave">{{ old('observacion_llave', request('observacion_llave')) }}</textarea>
                                    </div>

                                    <div class="col-md-3 ">
                                        <label class="text-center" id="basic-addon1">Cartel</label>
                                        <select class="form-select @error('cartel') is-invalid @enderror"
                                            aria-label="Default select example" name="cartel">
                                            <option value=""></option>
                                            <option
                                                value="SI"{{ old('cartel', request('cartel')) == 'SI' ? 'selected' : '' }}>
                                                SI</option>
                                            <option
                                                value="NO"{{ old('cartel', request('cartel')) == 'NO' ? 'selected' : '' }}>
                                                NO</option>
                                        </select>
                                    </div>

                                    <div class="col-md-9 ">
                                        <label class="text-center" id="basic-addon1">Observacion</label>
                                        <textarea class="form-control w-100 @error('observacion_cartel') is-invalid @enderror" name="observacion_cartel"
                                            rows="2" placeholder="Escribir comentario del cartel">{{ old('observacion_cartel', request('observacion_cartel')) }}
                                    </textarea>
                                    </div>
                                </div>

                                {{-- BOTON MODAL COMODIDADES --}}
                                <div class="col-md-6 pt-3">
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                        data-bs-target="#comodidadesPropiedadCarga">Comodidades</button>
                                </div>

                                {{-- Modal de Comodidades --}}
                                @include('atencionAlCliente.propiedad.modal-cargar-propiedad.modal-comodidades')

                                {{-- Boton Descripcion --}}
                                <div class="col-md-6 pt-3">
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                        data-bs-target="#descripcionPropiedad">Descripcion</button>
                                </div>
                                {{-- Modal de Descripcion --}}
                                @include('atencionAlCliente.propiedad.modal-cargar-propiedad.modal-descripcion')

                                {{-- Boton modal ventas --}}
                                <div class="col-md-6 pt-3">
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                        data-bs-target="#VentasPropiedadCarga">Ventas</button>
                                </div>
                                {{-- Boton modal alquiler --}}
                                <div class="col-md-6 pt-3">
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                        data-bs-target="#AlquilerPropiedadCarga">Alquiler</button>
                                </div>

                                {{-- Modal de Ventas --}}
                                @include('atencionAlCliente.propiedad.modal-cargar-propiedad.modal-ventas')


                                {{-- Modal de Alquiler --}}
                                @include('atencionAlCliente.propiedad.modal-cargar-propiedad.modal-alquiler')

                                {{-- Modal Condicion Alquiler --}}
                                @include('atencionAlCliente.propiedad.modal-cargar-propiedad.modal-condicion')

                                {{-- Boton Grabar --}}
                                <div class="row d-flex justify-content-center p-1">
                                    <div class="col-md-3 ">
                                        <button type="submit" class="btn btn-primary w-100 ">Grabar</button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Segundo bloque de columna -->
                        <div class="col-md-3 d-flex justify-content-center  ">
                            <div class="row g-1">
                                <!-- Campo para seleccionar múltiples fotos -->
                                <div class="col-md-12">
                                    <label for="fotos" class="form-label ">Subir Fotos y Documentos</label>
                                    <input type="file" class="form-control form-select " id="fotos"
                                        name="fotos[]" accept="image/*,application/pdf,video/*" multiple>
                                </div>
                            </div>
                        </div>
                        {{-- formulario de carga de fotos --}}
                        <div class="col-md-3">
                            <!-- Formulario de carga de varias fotos con detalles y previsualización dentro de un carrusel -->
                            <div class="container col-md-12">

                                <!-- Contenedor del Carrusel -->
                                <div id="fotosCarrusel" class="carousel slide h-50" data-bs-ride="carousel">
                                    <div class="carousel-inner " id="fotosDetalles">
                                        <!-- Aquí van las diapositivas -->
                                    </div>
                                    <!-- Controles del Carrusel -->
                                    <button class="carousel-control-prev" type="button" data-bs-target="#fotosCarrusel"
                                        data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#fotosCarrusel"
                                        data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>

                            </div>


                        </div>

































                    </div>






                </div>
            </div>



        </form>
    </div>


    {{-- ------------------------------------------------------------------------------------------------------------ --}}
    {{-- Incluir el archivo JavaScript --}}

    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>
    {{--  <script src="{{ asset('js/atencionAlCliente/propiedad/cargar-fotos.js') }}"></script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCalleSearch(@json($calle));
        });
    </script>
    <script>
        document.getElementById('fotos').addEventListener('change', function(event) {
            let fotosDetallesDiv = document.getElementById('fotosDetalles');
            fotosDetallesDiv.innerHTML = ''; // Limpiar las diapositivas anteriores

            const archivos = event.target.files;

            for (let i = 0; i < archivos.length; i++) {
                const file = archivos[i];
                const reader = new FileReader();

                // Crear contenedor para la diapositiva
                const slideContainer = document.createElement('div');
                slideContainer.classList.add('carousel-item');

                reader.onload = function(e) {
                    let contenido = '';

                    if (file.type.startsWith('image/')) {
                        contenido = `<img src="${e.target.result}" class="d-block w-100 img-thumbnail" 
                        style="height: 220px; object-fit: cover;">`;
                    } else if (file.type === "application/pdf") {
                        contenido = `<iframe src="${e.target.result}" width="100%" height="220px" 
                        style="border:none; object-fit: cover;"></iframe>`;
                    } else if (file.type.startsWith('video/')) {
                        contenido = `<video class="d-block w-100 img-thumbnail" style="height: 220px; object-fit: cover;" controls>
                        <source src="${e.target.result}" type="${file.type}">
                        Tu navegador no soporta la etiqueta de video.
                    </video>`;
                    }

                    slideContainer.innerHTML = contenido;

                    const detallesHtml = `
                    <div class="carousel-caption d-none d-md-block">
                        <textarea class="form-control" name="notes[${i}][descripcion]" rows="2" placeholder="Condición"></textarea>
                    </div>
                `;
                    slideContainer.innerHTML += detallesHtml;
                };

                if (i === 0) {
                    slideContainer.classList.add('active');
                }

                fotosDetallesDiv.appendChild(slideContainer);

                reader.readAsDataURL(file);
            }
        });

        // INTERCEPTAR EL ENVÍO DEL FORMULARIO Y USAR AJAX
        document.getElementById('formPropiedad').addEventListener('submit', function(event) {
            event.preventDefault(); // Evita la recarga de la página

            let formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href =
                            "{{ route('propiedad_padron.index') }}"; // Redirige si todo salió bien
                    } else {
                        alert('Error al guardar la propiedad');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>


@endsection
