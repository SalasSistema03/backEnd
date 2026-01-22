<div class="modal fade" id="modalEditarPersona" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modificar datos personales</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPersona" action="{{ route('clientes.modificar-datos-personales', '') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Id del cliente real -->
                    <input type="hidden" id="id_cliente" name="id_cliente" value="">
                    <div class="row">
                        <!-- Nombre del cliente -->
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                value="{{ $cliente->nombre ?? '' }}" required>
                        </div>
                        <!-- Telefono del cliente -->
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="number" class="form-control" id="telefono" name="telefono"
                                value="{{ $cliente->telefono ?? '' }}" min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Observaciones del cliente -->
                        <div class="col-md-6 mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ $cliente->observaciones ?? '' }}</textarea>
                        </div>
                        <!-- Nombre de la inmobiliaria -->
                        <div class="col-md-6 mb-3">
                            <label for="nombre_de_inmobiliaria" class="form-label">Nombre de la inmobiliaria</label>
                            <input type="text" class="form-control" id="nombre_de_inmobiliaria"
                                name="nombre_de_inmobiliaria" value="{{ $cliente->nombre_de_inmobiliaria ?? '' }}">
                        </div>
                    </div>

                    <div class="modal-footer py-0 pb-0">
                        <button type="submit" class="btn btnSalas btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// VERSIÓN SUPER SIMPLIFICADA
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEditarPersona');
    const form = document.getElementById('formEditarPersona');

    // Llenar formulario al abrir modal
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        form.action = '{{ route("clientes.modificar-datos-personales", "") }}/' + button.getAttribute('data-id-cliente');
        document.getElementById('id_cliente').value = button.getAttribute('data-id-cliente');
        document.getElementById('nombre').value = button.getAttribute('data-nombre');
        document.getElementById('telefono').value = button.getAttribute('data-telefono');
        document.getElementById('observaciones').value = button.getAttribute('data-observaciones') || '';
        document.getElementById('nombre_de_inmobiliaria').value = button.getAttribute('data-nombre-inmobiliaria') || '';
    });

    // Envío del formulario
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const formData = new FormData(form);
        const idCliente = document.getElementById('id_cliente').value;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Mostrar spinner
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            
            const data = await response.json();

            if (data.success) {
                // Cerrar modal
                bootstrap.Modal.getInstance(modal).hide();
                
                // Actualizar cliente en la lista
                const contactoElement = document.querySelector(`.contacto[data-cliente-id="${data.cliente.id}"]`);
                if (contactoElement) {
                    // Actualizar nombre
                    const nombreElement = contactoElement.querySelector('.col-11 strong');
                    if (nombreElement) nombreElement.textContent = data.cliente.nombre.toUpperCase();
                    
                    // Actualizar teléfono
                    const telefonoElement = contactoElement.querySelector('.text-muted strong');
                    if (telefonoElement) {
                        telefonoElement.innerHTML = `${data.cliente.telefono} <i class="fa-brands fa-whatsapp" onclick="event.stopPropagation(); window.open('https://wa.com/${data.cliente.telefono.replace(/\D/g, '')}', '_blank')"></i>`;
                    }
                    
                    // Actualizar botón editar
                    const botonEditar = contactoElement.querySelector('[data-bs-target="#modalEditarPersona"]');
                    if (botonEditar) {
                        botonEditar.setAttribute('data-nombre', data.cliente.nombre);
                        botonEditar.setAttribute('data-telefono', data.cliente.telefono);
                        botonEditar.setAttribute('data-observaciones', data.cliente.observaciones || '');
                        botonEditar.setAttribute('data-nombre-inmobiliaria', data.cliente.nombre_de_inmobiliaria || '');
                    }
                    
                    // Actualizar inmobiliaria
                    let inmobiliariaElement = contactoElement.querySelector('.pertenece_inmobiliaria');
                    const row = contactoElement.querySelector('.row');
                    
                    // Si hay un nombre de inmobiliaria
                    if (data.cliente.nombre_de_inmobiliaria) {
                        // Si ya existe el elemento, actualizarlo
                        if (inmobiliariaElement) {
                            const textNode = Array.from(inmobiliariaElement.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                            if (textNode) {
                                textNode.textContent = ` ${data.cliente.nombre_de_inmobiliaria}`;
                            }
                        } else {
                            // Si no existe, crear el elemento
                            if (row) {
                                const divInmobiliaria = document.createElement('div');
                                divInmobiliaria.className = 'd-flex justify-content-start align-items-center col-12';
                                divInmobiliaria.innerHTML = `
                                    <span class="badge pertenece_inmobiliaria">
                                        <i class="bi bi-house-fill icon_pertenece_inmobiliaria"></i>
                                        ${data.cliente.nombre_de_inmobiliaria}
                                    </span>
                                `;
                                // Insertar después del elemento de teléfono
                                const telefonoElement = row.querySelector('.text-muted');
                                if (telefonoElement) {
                                    const parent = telefonoElement.closest('.d-flex');
                                    if (parent) {
                                        parent.insertAdjacentElement('afterend', divInmobiliaria);
                                    } else {
                                        row.appendChild(divInmobiliaria);
                                    }
                                } else {
                                    row.appendChild(divInmobiliaria);
                                }
                            }
                        }
                    } else if (inmobiliariaElement) {
                        // Si no hay nombre de inmobiliaria, eliminar el elemento
                        const parent = inmobiliariaElement.closest('.d-flex');
                        if (parent) parent.remove();
                    }
                }
                
                // Mostrar notificación
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('success', data.message || 'Cliente actualizado correctamente');
                }
            } else {
                const errorMsg = data.message || 'Error al actualizar el cliente';
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('error', errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMsg = 'Error al procesar la solicitud: ' + error.message;
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('error', errorMsg);
            } else {
                alert(errorMsg);
            }
        } finally {
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});
</script>