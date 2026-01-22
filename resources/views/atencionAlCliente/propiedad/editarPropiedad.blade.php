@extends('layout.nav')

@section('title', 'Edita Propiedad')

@section('content')

    <form class = 'text-center mx-3' action="{{ route('propiedad.updatedatos', $propiedad->id) }}" method="POST"
        enctype="multipart/form-data" autocomplete="off" novalidate>
        @csrf
        @method('PUT')
        {{-- datos de la propiedad y fotos --}}
        <div class="row datosPropiedad">
            <!-- Primer bloque de columna -->
            <div class="col-md-6 d-flex justify-content-center  px-3">
                <div class="row g-1">

                    <div class="col-md-4 ">
                        <label for="search-calle">Calle</label>
                        <input type="text" id="search-calle" class="form-control @error('calle') is-invalid @enderror"
                            placeholder="Buscar calle..." value="{{ $propiedad->calle->name ?? '' }}"
                            @error('calle') title="{{ $message }}" @enderror>
                        <input type="hidden" id="calle_id" name="calle" value="{{ $propiedad->id_calle ?? '' }}">
                        <div id="search-results" class="list-group mt-2" style="position: absolute; z-index: 1000;">
                        </div>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center  " id="basic-addon1">Numero</label>
                        <input name="numero_calle" type="number"
                            class="form-control @error('numero_calle') is-invalid @enderror"
                            value="{{ session('numero_calle', $propiedad->numero_calle) }}" id="numero_calle" min="0"
                            max="100000">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">PH</label>
                        <select class=" form-select @error('ph') is-invalid @enderror" aria-label="Default select example"
                            name="ph" id="ph">
                            <option value="">-</option>
                            <option value="SI" {{ session('ph', $propiedad->ph) == 'SI' ? 'selected' : '' }}>
                                SI
                            </option>
                            <option value="NO" {{ session('ph', $propiedad->ph) == 'NO' ? 'selected' : '' }}>
                                NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Piso</label>
                        <input name= "piso" type="number" class="form-control @error('piso') is-invalid @enderror"
                            value="{{ session('piso', $propiedad->piso) }}" id="" min="0" max="163">
                    </div>

                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Depto</label>
                        <input name="depto" type="text" class="form-control @error('depto') is-invalid @enderror"
                            value="{{ session('depto', $propiedad->departamento) }}" id="" min= "0"
                            max="600">
                    </div>
                    <div class="col-md-4 ">
                        <label class="text-center" id="basic-addon1">Inmueble</label>
                        <select class="form-select @error('tipo_inmueble') is-invalid @enderror"
                            aria-label="Default select example" id="tipo_inmueble" name="tipo_inmueble">
                            <option value="">Selec. un inmueble</option>
                            @foreach ($tipo_inmueble as $tipo)
                                <option value="{{ $tipo->id }}"
                                    {{ session('tipo_inmueble', $propiedad->id_inmueble) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->inmueble }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-center" id="basic-addon1">Zona</label>
                        <select class="form-select @error('zona') is-invalid @enderror" aria-label="Default select example"
                            name="zona" id="zona">

                            <option value="">Seleccione una zona</option>

                            @foreach ($zona as $zonas)
                                <option value="{{ $zonas->id }}"
                                    {{ session('zona', $propiedad->id_zona) == $zonas->id ? 'selected' : '' }}>
                                    {{ $zonas->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-center" id="basic-addon1">Provincia</label>
                        <select class="form-select @error('provincia') is-invalid @enderror"
                            aria-label="Default select example" name="provincia" id="provincia">

                            <option value="">Seleccione una provincia</option>

                            @foreach ($provincia as $provincias)
                                <option value="{{ $provincias->id }}"
                                    {{ session('provincia', $propiedad->id_provincia) == $provincias->id ? 'selected' : '' }}>
                                    {{ $provincias->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Llave</label>
                        <input name="llave" id="llave" type="number"
                            class="form-control @error('llave') is-invalid @enderror"
                            value="{{ session('llave', $propiedad->llave) }}" id="" min="0" max="100">
                    </div>
                    <div class="col-md-10">
                        <label class="text-center" id="basic-addon1">Observacion Llave</label>
                        <textarea class="form-control w-100 @error('observacion_llave') is-invalid @enderror" name="observacion_llave"
                            rows="2" placeholder="Escribir comentario de la llave" id="observacion_llave">{{ session('observacion_llave', $propiedad->comentario_llave) }}</textarea>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Cartel</label>
                        <select class="form-select @error('cartel') is-invalid @enderror"
                            aria-label="Default select example" name="cartel" id= "cartel">

                            <option value="">Seleccione un estado</option>

                            <option value="SI" {{ session('cartel', $propiedad->cartel) == 'SI' ? 'selected' : '' }}>
                                SI
                            </option>
                            <option value="NO" {{ session('cartel', $propiedad->cartel) == 'NO' ? 'selected' : '' }}>
                                NO
                            </option>
                        </select>
                    </div>
                    <div class = "col-md-10">
                        <label class="text-center" id="basic-addon1">Observacion</label>
                        <textarea class="form-control w-100 @error('observacion_cartel') is-invalid @enderror" name="observacion_cartel"
                            rows="2" placeholder="Escribir comentario del cartel" id="observacion_cartel">{{ session('observacion_cartel', $propiedad->comentario_cartel) }}</textarea>
                    </div>
                </div>
            </div>
            <!-- Segundo bloque de columna -->
            <div class="col-md-6 d-flex justify-content-center  px-3">
                <div class="row g-1">
                    <div class="col-md-3">
                        <label for="">Estado general</label>
                        <select class="form-select @error('estado_general') is-invalid @enderror"
                            aria-label="Default select example" name="estado_general" id="estado_general">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_general as $estado_gen)
                                <option value="{{ $estado_gen->id }}"
                                    {{ session('estado_general', $propiedad->id_estado_general) == $estado_gen->id ? 'selected' : '' }}>
                                    {{ $estado_gen->estado_general }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Dorm.</label>
                        <input name="dormitorios" id="dormitorios"type="number"
                            class="form-control @error('dormitorios') is-invalid @enderror"
                            value="{{ session('dormitorios', $propiedad->cantidad_dormitorios) }}" min="0"
                            max="100">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Baños</label>
                        <input name="banios" id="banios"type="number"
                            class="form-control @error('banios') is-invalid @enderror"
                            value="{{ session('banios', $propiedad->banios) }}" min="0" max="100">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Cochera</label>
                        <select class=" form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="cochera" id="cochera">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('cochera', $propiedad->cochera) == 'SI' ? 'selected' : '' }}>SI
                            </option>
                            <option value="NO"
                                {{ session('cochera', $propiedad->cochera) == 'NO' ? 'selected' : '' }}>NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">N° Cochera</label>
                        <input name="numero_cochera"type="number" id="numero_cochera"
                            class="form-control  @error('numero_cochera') is-invalid @enderror"
                            value="{{ session('numero_cochera', $propiedad->numero_cochera) }}" id="">
                    </div>

                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">m² Lote</label>
                        <input name="m_Lote"type="number" class="form-control @error('m_Lote') is-invalid @enderror"
                            value="{{ session('m_Lote', $propiedad->mLote) }}" id="m_Lote" min="0">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">m² Cubiertos</label>
                        <input name="m_Cubiertos"type="number"
                            class="form-control @error('m_Cubiertos') is-invalid @enderror"
                            value="{{ session('m_Cubiertos', $propiedad->mCubiertos) }}" id="m_Cubiertos">
                    </div>


                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Asfalto</label>
                        <select class=" form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="asfalto" id="asfalto">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('asfalto', $propiedad->asfalto) == 'SI' ? 'selected' : '' }}>SI
                            </option>
                            <option value="NO"
                                {{ session('asfalto', $propiedad->asfalto) == 'NO' ? 'selected' : '' }}>NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Gas</label>
                        <select class=" form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="gas" id= "gas">
                            <option value="">-</option>
                            <option value="SI" {{ session('gas', $propiedad->gas) == 'SI' ? 'selected' : '' }}>SI
                            </option>
                            <option value="NO" {{ session('gas', $propiedad->gas) == 'NO' ? 'selected' : '' }}>NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Cloaca</label>
                        <select class=" form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="cloaca" id="cloaca">
                            <option value="">-</option>
                            <option value="SI" {{ session('cloaca', $propiedad->cloaca) == 'SI' ? 'selected' : '' }}>
                                SI
                            </option>
                            <option value="NO" {{ session('cloaca', $propiedad->cloaca) == 'NO' ? 'selected' : '' }}>
                                NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Agua</label>
                        <select class=" form-select @error('agua') is-invalid @enderror"
                            aria-label="Default select example" name="agua" id="agua">
                            <option value="">-</option>
                            <option value="SI" {{ session('agua', $propiedad->agua) == 'SI' ? 'selected' : '' }}>SI
                            </option>
                            <option value="NO" {{ session('agua', $propiedad->agua) == 'NO' ? 'selected' : '' }}>NO
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 ">
                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                            data-bs-target="#descripcionPropiedad">Descripcion</button>
                    </div>
                    <div class="col-md-6 ">
                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                            data-bs-target="#listaPropietario">
                            Propietario
                        </button>
                    </div>

                    <div class="col-md-4 ">
                        <a href="{{ route('fotos.show', $propiedad->id) }}" class="btn btn-primary w-100">Mod. Fotos</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('documentacion.show', $propiedad->id) }}" class="btn btn-primary w-100">Mod.
                            Documentacion</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('video.show', $propiedad->id) }}" class="btn btn-primary w-100">Mod. Videos</a>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        {{-- VENTAS Y ALQUILERES --}}
        <div class="row g-2 datosPropiedad">
            <!-- Primer bloque de columna ventas-->
            <div class="col-md-6 d-flex justify-content-center align-items-center">
                <div class="col-md-2">
                    <strong>
                        <label class="text-center" id="basic-addon1">Codigo Venta</label></strong>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control text-center @error('cod_venta') is-invalid @enderror"
                        value="{{ session('cod_venta', $propiedad->cod_venta) }}" name="cod_venta" id="cod_venta"
                        min="0">
                </div>
                <div class="col-md-8 px-2">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        Informacion Ventas
                    </button>
                </div>
            </div>
            <!-- Segundo bloque de columna alquiler-->
            <div class="col-md-6 d-flex justify-content-center align-items-center">


                <div class="col-md-2">
                    <strong>
                        <label class="text-center" id="basic-addon1">Codigo Alquiler</label>
                    </strong>

                </div>

                <div class="col-md-2">
                    <input type="number" class="form-control text-center @error('cod_alquiler') is-invalid @enderror"
                        value="{{ session('cod_alquiler', $propiedad->cod_alquiler) }}" id="cod_alquiler"
                        name="cod_alquiler" min="0">
                </div>
                <div class="col-md-8 px-2">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#exampleModalA">
                        Informacion Alquiler
                    </button>

                </div>

            </div>
        </div>
        <hr>
        <div class="row d-flex justify-content-center">
            <div class="col-md-2 pt-3">
                <button type="submit" class="btn btn-primary w-100" id="btnGuardarVenta">Grabar</button>
            </div>
        </div>
        <br>





        <!-- Modal Descripcion-->
        @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-descripcion')
        <!-- Modal Informacion Ventas-->
        @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-informacion-ventas')
        <!-- Modal Informacion Alquileres-->
        @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-informacion-alquileres')
        <!-- Modal Propietario -->
        @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-propietario')
        {{-- Modal de Condicion --}}
        @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-condicion')
    </form>

    {{-- ------------------------------------------------------------------------------------------------------------ --}}



    <!-- Modal para Dar de Alta -->
    @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-dar-alta')
    <!-- Modal para Buscar Persona -->
    @include('atencionAlCliente.propiedad.modal-editar-propiedad.modal-buscar-persona')

    


    {{-- ------------------------------------------------------------------------------------------------------------ --}}

    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCalleSearch(@json($calle));
            initPersonaSearch(@json($buscarPropietarios));

            function redirigirAEditarFotos() {
                // Cambia la URL por la ruta de tu vista de edición de fotos
                window.location.href = '/fotos.edit';
            }
        });
    </script>
    {{-- Scrpt para guardar cambios --}}
    <script>
        $(document).ready(function() {
            $('input, select, textarea').on('change', function() {
                var campo = $(this).attr('name'); // Obtener el nombre del campo
                var valor = $(this).val(); // Obtener el valor

                $.ajax({
                    url: "{{ route('propiedad.guardarCambio') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        campo: campo, // Nombre del input/select
                        valor: valor // Valor seleccionado o ingresado
                    },
                    success: function(response) {
                        console.log(campo + ' guardado en la sesión');
                    }
                });
            });
        });
    </script>
    {{-- SCRIPT PARA DAR DE BAJA --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dar-baja').forEach(button => {
                button.addEventListener('click', function() {
                    const propiedadId = this.getAttribute('data-propiedad-id');
                    const padronId = this.getAttribute('data-padron-id');
                    const observacionesInput = document.querySelector(
                        `input[name="observaciones"][data-padron-id="${padronId}"]`);
                    event.preventDefault();

                    // Validar si el campo de observaciones está vacío
                    if (!observacionesInput || observacionesInput.value.trim() === '') {
                        alert(
                            'Por favor, ingresa una observación antes de dar de baja al propietario.'
                        );
                        observacionesInput.focus(); // Enfocar el campo de observaciones
                        return;
                    }

                    if (confirm('¿Estás seguro de que deseas dar de baja a este propietario?')) {
                        fetch("{{ route('propiedad.darDeBaja') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    propiedad_id: propiedadId,
                                    padron_id: padronId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Propietario dado de baja exitosamente.');
                                    // Recargar la página actual
                                    location.reload();
                                } else {
                                    alert('Hubo un error al dar de baja al propietario.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Hubo un error al procesar la solicitud.');
                            });
                    }
                });
            });
        });
    </script>
    {{-- SCRIPT PARA DAR DE ALTA --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dar-alta').forEach(button => {
                button.addEventListener('click', function() {
                    const propiedadId = this.getAttribute('data-propiedad-id');
                    const padronId = this.getAttribute('data-padron-id');

                    if (confirm('¿Estás seguro de que deseas dar de alta a este propietario?')) {
                        fetch("{{ route('propiedad.darDeAlta') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    propiedad_id: propiedadId,
                                    padron_id: padronId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Propietario dado de alta exitosamente.');
                                    location.reload(); // Recargar la página actual
                                } else {
                                    alert('Hubo un error al dar de alta al propietario.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Hubo un error al procesar la solicitud.');
                            });
                    }
                });
            });
        });
    </script>
    {{-- SCRIPT PARA BUSCAR PERSONA --}}
    <script>
        function initPersonaSearch(buscarPropietarios) {

            const searchInput = document.getElementById("search-persona");
            const searchResults = document.getElementById("search-results-persona");
            const personaIdInput = document.getElementById("persona_id");
            const agregarPersonaButton = document.getElementById("agregarPersona");

            searchInput.addEventListener("input", function() {
                const searchTerm = this.value.toLowerCase();
                searchResults.innerHTML = "";

                if (searchTerm.length < 2) {
                    searchResults.style.display = "none";
                    agregarPersonaButton.disabled = true; // Deshabilitar el botón
                    return;
                }

                // Filtrar por nombre y apellido combinados
                const filteredPersonas = buscarPropietarios
                    .filter((persona) => persona.name.toLowerCase().includes(searchTerm))
                    .slice(0, 15); // Limitar a 10 resultados

                if (filteredPersonas.length > 0) {
                    searchResults.style.display = "block";

                    filteredPersonas.forEach((persona) => {
                        const div = document.createElement("div");
                        div.className = "list-group-item list-group-item-action";
                        div.textContent = persona.name;
                        div.addEventListener("click", () => {
                            searchInput.value = persona.name;
                            personaIdInput.value = persona.id;
                            searchResults.style.display = "none";
                            agregarPersonaButton.disabled = false; // Habilitar el botón
                        });
                        searchResults.appendChild(div);
                    });
                } else {
                    searchResults.style.display = "none";
                    agregarPersonaButton.disabled = true; // Deshabilitar el botón
                }
            });

            // Cerrar resultados cuando se hace clic fuera
            document.addEventListener("click", function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = "none";
                }
            });

            // Manejar el clic en el botón "Agregar"
            agregarPersonaButton.addEventListener("click", function() {
                const personaId = personaIdInput.value;
                if (!personaId) {
                    alert("Por favor, selecciona una persona.");
                    return;
                }

                asignarPersona(personaId);
            });
        }

        // Función para asignar una persona a la propiedad
        function asignarPersona(personaId) {
            const propiedadId = "{{ $propiedad->id }}";

            fetch("{{ route('propiedad.asignarPersona') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({
                        propiedad_id: propiedadId,
                        persona_id: personaId,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Persona asignada exitosamente.");
                        location.reload(); // Recargar la página para reflejar los cambios
                    } else {
                        alert("Esta persona ya se encuentra asignada.");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("Hubo un error al procesar la solicitud.");
                });
        }
    </script>
@endsection
