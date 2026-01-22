@extends('layout.nav')

@section('title', 'Cargar Propietario en Propiedad')

@section('content')

    <!-- Token CSRF para las peticiones AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="conteiner px-3">
        <form method="GET" autocomplete="off">

            <div class="row mx-3">
                <div class="col-md-6 mb-3">
                    <input type="text" id="search-propietario" name="propietario" class="form-control mt-3"
                        placeholder="Buscar propietarios....">
                    <input type="hidden" id="propietario_id" name="propietarioo" value="">
                    <input type="hidden" id="propiedad_id" name="propiedad_id" value="{{ session('propiedad_id') }}">
                    <div id="search-results-prop" class="list-group mt-2" style="position: absolute; z-index: 1000;"></div>
                </div>
                <div class="col-md-3 d-flex justify-content-start align-items-center ">
                    <button type="button" class="btn btn-primary btn-block" id="btn-buscar">Agregar</button>
                </div>
                <div class="col-md-2 d-flex justify-content-end align-items-center me-2 ">
                    <button type="button" class="btn btnSalas btn-block" id="btn-finalizar">Finalizar</button>
                </div>
            </div>
    
            <div class="mx-3">
                <table class="table table-striped table-hover text-center tabla">
                    <thead>
                        <tr>
                            <th>Propietarios Agregados</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody id="propietarios-list">
                        {{-- Aquí se agregarán las filas dinámicamente --}}
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    

      <script>
        const propiedadPadron = @json($propiedadPadron);

        document.addEventListener('DOMContentLoaded', function() {
            initPropietarioSearch(@json($padron));

            document.getElementById('btn-finalizar').addEventListener('click', function() {
            const propiedadId = document.getElementById('propiedad_id').value;

            // Redirigir a la URL de la función show del controlador PropiedadController con el ID de la propiedad
            window.location.href = 'propiedad/' + propiedadId;
        });
        });

        function verificarVinculoExistente(propiedadId, propietarioId) {
            return propiedadPadron.some(vinculo => vinculo.propiedad_id == propiedadId && vinculo.padron_id ==
                propietarioId);
        }

        function initPropietarioSearch(propietarios) {
            const searchInput = document.getElementById("search-propietario");
            const searchResults = document.getElementById("search-results-prop");
            const propietarioIdInput = document.getElementById("propietario_id");
            const propietariosList = document.getElementById("propietarios-list");
            const btnAgregar = document.getElementById("btn-buscar");

            searchInput.addEventListener("input", function() {
                const searchTerm = this.value.toLowerCase();
                searchResults.innerHTML = "";

                if (searchTerm.length < 2) {
                    searchResults.style.display = "none";
                    return;
                }

                const filteredPropietarios = propietarios
                    .filter((propietario) => propietario && propietario.nombre && propietario.apellido &&
                        (`${propietario.nombre} ${propietario.apellido} ${propietario.documento}`.toLowerCase()
                            .includes(searchTerm)))
                    .slice(0, 10);

                if (filteredPropietarios.length > 0) {
                    searchResults.style.display = "block";

                    filteredPropietarios.forEach((propietario) => {
                        const div = document.createElement("div");
                        div.className = "list-group-item list-group-item-action";
                        div.textContent =
                            `${propietario.documento} ${propietario.nombre} ${propietario.apellido}`;
                        div.addEventListener("click", () => {
                            searchInput.value =
                                `${propietario.documento} ${propietario.nombre} ${propietario.apellido} `;
                            propietarioIdInput.value = propietario.id;
                            searchResults.style.display = "none";
                        });
                        searchResults.appendChild(div);
                    });
                } else {
                    searchResults.style.display = "none";
                }
            });

            document.addEventListener("click", function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = "none";
                }
            });

            btnAgregar.addEventListener("click", function() {
                const propietarioId = propietarioIdInput.value;
                const propiedadId = "{{ session('propiedad_id') }}"; // ID de la propiedad en sesión

                if (!propietarioId) {
                    alert("Seleccione un propietario antes de agregar.");
                    return;
                }

                // Verificar si ya existe el vínculo entre la propiedad y el propietario
                if (verificarVinculoExistente(propiedadId, propietarioId)) {
                    alert("Este propietario ya está vinculado a esta propiedad.");
                    return; // No continuar si ya está vinculado
                }

                // Enviar datos al backend para vincular propiedad y propietario
                fetch('{{ url('/vincular-propietario') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            propiedad_id: propiedadId,
                            padron_id: propietarioId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log(searchInput.value);
                            agregarPropietarioALista(searchInput.value, propietarioId);

                            // Después de vincular, actualizar la lista de propiedadPadron con el nuevo vínculo
                            propiedadPadron.push({
                                propiedad_id: propiedadId,
                                padron_id: propietarioId
                            });

                            propietarioIdInput.value = ""; // Limpiar campo oculto
                            searchInput.value = "";
                        } else {
                            alert('Error al vincular propietario con la propiedad.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            function agregarPropietarioALista(nombre, id) {
                console.log(nombre, id);
                const row = document.createElement("tr");
                row.setAttribute("data-id", id);

                const tdNombre = document.createElement("td");
                tdNombre.textContent = nombre;

                const tdAcciones = document.createElement("td");
                tdAcciones.className = "text-center d-flex justify-content-center align-items-center";
                const deleteButton = document.createElement("button");
                deleteButton.type = "button";
                deleteButton.className = "btn btn-secondary ";
                deleteButton.textContent = "Borrar";
                deleteButton.addEventListener("click", function() {

                    const propiedadId = "{{ session('propiedad_id') }}";
                    // Confirmar antes de eliminar
                    if (confirm('¿Está seguro de que desea eliminar este propietario?')) {
                        fetch('{{ url('/desvincular-propietario') }}', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                body: JSON.stringify({
                                    propiedad_id: propiedadId,
                                    padron_id: id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    row.remove(); // Eliminar visualmente de la tabla
                                } else {
                                    alert('Error al desvincular el propietario.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al procesar la solicitud de eliminación');
                            });
                    }
                });

                tdAcciones.appendChild(deleteButton);
                row.appendChild(tdNombre);
                row.appendChild(tdAcciones);
                propietariosList.appendChild(row);
            }
        }
    </script> 
@endsection
