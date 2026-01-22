<!-- Modal -->
<div class="modal fade" id="modalEditarCriterio" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modificar criterio</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editarCriterioForm"
                    action="{{ route('clientes.modificar-criterio', $criterio->id_criterio_venta) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id_criterio" id="id_criterio"
                        value="{{ $criterio->id_criterio_venta }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria" class="form-label">Potabilidad</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="Potable" {{ $criterio->id_categoria == 'Potable' ? 'selected' : '' }}>
                                    Potable</option>
                                <option value="Medio" {{ $criterio->id_categoria == 'Medio' ? 'selected' : '' }}>Medio
                                </option>
                                <option value="No Potable"
                                    {{ $criterio->id_categoria == 'No Potable' ? 'selected' : '' }}>No Potable</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_inmueble" class="form-label">Tipo de Inmueble</label>
                            <select class="form-select" id="tipo_inmueble" name="tipo_inmueble">
                                @foreach ($tipo_inmueble as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ $criterio->id_tipo_inmueble == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->inmueble }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zona" class="form-label">Zona</label>
                            <select class="form-select" id="zona" name="zona">
                                @foreach ($zona as $z)
                                    <option value="{{ $z->id }}"
                                        {{ $criterio->id_zona == $z->id ? 'selected' : '' }}>{{ $z->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dormitorios" class="form-label">Cantidad de dormitorios</label>
                            <input type="number" class="form-control" id="dormitorios" name="dormitorios"
                                value="{{ $criterio->cant_dormitorios }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cochera" class="form-label">Cochera</label>
                            <select class="form-select" id="cochera" name="cochera">
                                <option value="NO" {{ $criterio->cochera == 'NO' ? 'selected' : '' }}>NO</option>
                                <option value="SI" {{ $criterio->cochera == 'SI' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="observaciones_criterio_venta" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones_criterio_venta" name="observaciones_criterio_venta" rows="2">{{ $criterio->observaciones_criterio_venta }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado_criterio_venta" class="form-label">Estado Criterio</label>
                            <select class="form-select" id="estado_criterio_venta" name="estado_criterio_venta">
                                <option value="Activo"
                                    {{ $criterio->estado_criterio_venta == 'Activo' ? 'selected' : '' }}>Activo
                                </option>
                                <option value="Inactivo"
                                    {{ $criterio->estado_criterio_venta == 'Inactivo' ? 'selected' : '' }}>Inactivo
                                </option>
                                <option value="Finalizado"
                                    {{ $criterio->estado_criterio_venta == 'Finalizado' ? 'selected' : '' }}>Finalizado
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="precio_hasta" class="form-label">Precio Hasta</label>
                            <input type="number" class="form-control" id="precio_hasta" name="precio_hasta"
                                value="{{ $criterio->precio_hasta }}" min="0">
                        </div>
                    </div>

                    <div class="modal-footer py-0 pb-0">
                        <button type="submit" class="btn btnSalas btn-sm" id="guardarCriterioBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="criterioSpinner" role="status"
                                aria-hidden="true"></span>
                            <span id="criterioBtnText">Guardar</span>
                        </button>
                    </div>
                </form>

                <script>
                    // Function to update the criteria in the list and related UI elements
                    function updateCriteriaInList(updatedCriterio) {
                        // Find the criteria item in the list
                        const criteriaItem = document.querySelector(`li[data-id-criterio="${updatedCriterio.id_criterio_venta}"]`);

                        if (criteriaItem) {
                            // Get the property type from the select element in the form
                            const tipoInmuebleSelect = document.getElementById('tipo_inmueble');
                            let tipoInmuebleText = 'No especificado';

                            if (tipoInmuebleSelect) {
                                const selectedOption = tipoInmuebleSelect.options[tipoInmuebleSelect.selectedIndex];
                                tipoInmuebleText = selectedOption ? selectedOption.text : 'No especificado';
                            }

                            // Update the displayed values in the criteria list
                            const detalles = criteriaItem.querySelectorAll('.col-9');
                            if (detalles.length >= 5) {
                                detalles[0].textContent = tipoInmuebleText;
                                detalles[1].textContent = updatedCriterio.cant_dormitorios || 'No especificado';
                                detalles[2].textContent = updatedCriterio.estado_criterio_venta || 'No especificado';
                                detalles[3].textContent = updatedCriterio.fecha_formateada || 'No especificado';
                                detalles[4].textContent = updatedCriterio.precio_hasta ?
                                    `$${parseInt(updatedCriterio.precio_hasta).toLocaleString()}` : 'No especificado';

                                // Update the data attributes if they exist
                                if (criteriaItem.dataset.tipoInmueble) {
                                    criteriaItem.dataset.tipoInmueble = tipoInmuebleText;
                                }
                            }

                            // Actualizar los atributos de datos del elemento del criterio
                            if (criteriaItem) {
                                // Obtener los valores actualizados del formulario
                                const zonaSelect = document.getElementById('zona');
                                const zonaText = zonaSelect ? zonaSelect.options[zonaSelect.selectedIndex].text : '';
                                const cocheraSelect = document.getElementById('cochera');
                                const cocheraValue = cocheraSelect ? cocheraSelect.value : 'NO';

                                // Actualizar los atributos de datos
                                criteriaItem.setAttribute('data-tipo-inmueble', tipoInmuebleText);
                                criteriaItem.setAttribute('data-cant-dormitorios', updatedCriterio.cant_dormitorios || '0');
                                criteriaItem.setAttribute('data-zona', zonaText);
                                criteriaItem.setAttribute('data-cochera', cocheraValue);

                                // Llamar a mostrarEstado para actualizar el título de la conversación
                                if (typeof mostrarEstado === 'function') {
                                    mostrarEstado(updatedCriterio.id_criterio_venta);
                                }
                            }

                            // Update the client list item
                            const clienteId = criteriaItem.closest('.criterio-chat')?.id.replace('chat', '');
                            if (clienteId) {
                                const clienteItem = document.querySelector(
                                `.barra_contactos .contacto[data-cliente-id="${clienteId}"]`);
                                if (clienteItem) {
                                    // Update the potability icon in the client list
                                    const iconContainer = clienteItem.querySelector('.icono_potabilidad');
                                    if (iconContainer) {
                                        let iconClass = 'fa-regular ';
                                        if (updatedCriterio.estado_criterio_venta !== 'Activo') {
                                            iconClass += 'fa-folder-closed naranja';
                                        } else {
                                            if (updatedCriterio.id_categoria === 'Potable') {
                                                iconClass += 'fa-face-grin-beam text-success';
                                            } else if (updatedCriterio.id_categoria === 'Medio') {
                                                iconClass += 'fa-face-grimace text-warning';
                                            } else if (updatedCriterio.id_categoria === 'No Potable') {
                                                iconClass += 'fa-face-angry text-danger';
                                            } else {
                                                iconClass += 'fa-pen-to-square text-dark';
                                            }
                                        }
                                        iconContainer.className = iconClass + ' icono_potabilidad';
                                    }
                                }
                            }

                            // Update the potability icon in the criteria list
                            const iconContainer = criteriaItem.querySelector('.icono_potabilidad');
                            if (iconContainer) {
                                let iconClass = 'fa-regular ';
                                if (updatedCriterio.estado_criterio_venta !== 'Activo') {
                                    iconClass += 'fa-folder-closed naranja';
                                } else {
                                    if (updatedCriterio.id_categoria === 'Potable') {
                                        iconClass += 'fa-face-grin-beam text-success';
                                    } else if (updatedCriterio.id_categoria === 'Medio') {
                                        iconClass += 'fa-face-grimace text-warning';
                                    } else if (updatedCriterio.id_categoria === 'No Potable') {
                                        iconClass += 'fa-face-angry text-danger';
                                    } else {
                                        iconClass += 'fa-pen-to-square text-dark';
                                    }
                                }
                                iconContainer.className = iconClass + ' icono_potabilidad';
                            }
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('editarCriterioForm');
                        const btnGuardar = document.getElementById('guardarCriterioBtn');
                        const spinner = document.getElementById('criterioSpinner');
                        const btnText = document.getElementById('criterioBtnText');

                        form.addEventListener('submit', function(e) {
                            e.preventDefault();

                            // Show loading state
                            btnGuardar.disabled = true;
                            spinner.classList.remove('d-none');
                            btnText.textContent = 'Guardando...';

                            // Get the form data
                            const formData = new FormData(this);
                            const url = this.action;

                            // Get the _method value from the form
                            const method = document.querySelector('input[name="_method"]').value;

                            // Create headers
                            const headers = new Headers();
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                            headers.append('X-CSRF-TOKEN', csrfToken);
                            headers.append('X-Requested-With', 'XMLHttpRequest');
                            headers.append('Accept', 'application/json');

                            // Send the request
                            fetch(url, {
                                    method: 'POST', // Always use POST for form submissions
                                    headers: headers,
                                    body: formData,
                                    credentials: 'same-origin'
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(err => {
                                            throw new Error(err.message ||
                                                'Error en la respuesta del servidor');
                                        });
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        // Close the modal
                                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                                            'modalEditarCriterio'));
                                        modal.hide();

                                        // Update the specific criteria in the list
                                        updateCriteriaInList(data.criterio);
                                    } else {
                                        throw new Error(data.message || 'Error al actualizar el criterio');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert(error.message || 'Error al procesar la solicitud');
                                })
                                .finally(() => {
                                    // Reset button state
                                    btnGuardar.disabled = false;
                                    spinner.classList.add('d-none');
                                    btnText.textContent = 'Guardar';
                                });
                        });
                    });
                </script>

            </div>
        </div>
    </div>
</div>
