document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.calendar-slot').forEach(function (cell) {
        //Obtenemos el valor de cada atributo/id
        cell.addEventListener('click', function () {
            const id = this.id;
            const username = cell.getAttribute('data-username');
            const userId = cell.getAttribute('data-usuario');
            const sectorId = cell.getAttribute('data-sector');
            const bajaForm = document.getElementById('bajaForm');
            const evento = cell.getAttribute('data-evento');
            document.getElementById('modal-username').value = username;
            document.getElementById('modal-userid').value = userId;
            document.getElementById('sector_real').value = sectorId;
            document.getElementById('modal-hora').value = cell.getAttribute('data-hora');
            

            /* console.log(document.querySelectorAll('.calendar-slot').length); */

            //Obtenemos la hora final
            let [h, m] = cell.getAttribute('data-hora').split(':').map(Number);
            m += 15;
            if (m >= 60) {
                h += 1;
                m = m - 60;
            }
            //Asignamos la hora final
            document.getElementById('modal-hora-fin').value =
                `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;

            //Obtenemos el evento y si existe y no es vacio lo parseamos para obtener los datos de la nota 

            if (evento && evento !== '') {
                try {
                    //Parseamos el evento
                    const eventoData = JSON.parse(evento);

                    //Obtenemos los datos de la nota y los asignamos a los campos
                    document.getElementById('modal-descripcion').value = eventoData['nota']['descripcion'] || '';
                    document.getElementById('modal-hora-fin').value = eventoData['nota']['hora_fin'] || '';
                    document.getElementById('propiedad').value = eventoData['propiedad'] || '';
                    document.getElementById('propiedad-codigo-real').value = eventoData['propiedad_id'] || '';
                    document.getElementById('creadoPor').value = eventoData['nota']['creado_por_username'] || '';
                    

                    // Actualizar la información del cliente
                    const clienteInfo = document.getElementById('cliente_info');
                    const clienteId = document.getElementById('cliente_id');
                    const calleAutocomplete = document.getElementById('calle-autocomplete');
                    const calleId = document.getElementById('calle_id');

                    // Si hay información de cliente en el evento, mostrarla
                    if (eventoData['cliente'] && eventoData['cliente_id']) {
                        clienteInfo.value = eventoData['cliente'];
                        clienteId.value = eventoData['cliente_id'];
                    } else if (eventoData['nota'] && eventoData['nota']['cliente']) {
                        // Si el cliente viene en la relación
                        const cliente = eventoData['nota']['cliente'];
                        clienteInfo.value = `${cliente.telefono} - ${cliente.nombre}`;
                        clienteId.value = cliente.id_cliente;
                    } else {
                        clienteInfo.value = 'Sin cliente';
                        clienteId.value = '';
                    }


                    //Si existe la calle la asignamos y deshabilitamos el campo 
                    if (calleAutocomplete) {
                        calleAutocomplete.value = eventoData['nota']['calle'];
                        calleAutocomplete.disabled = true;
                    } else {
                        console.error('Elemento calle-autocomplete no encontrado en el DOM');
                    }

                    //Si existe la calle la asignamos y deshabilitamos el campo 
                    if (calleId) {
                        calleId.value = eventoData['nota']['calle'];
                    } else {
                        console.error('Elemento calle_id no encontrado en el DOM');
                    }

                    // Deshabilitar campos cuando hay nota
                    document.getElementById('modal-descripcion').disabled = true;
                    document.getElementById('cliente').disabled = true;
                    document.getElementById('propiedad').disabled = true;
                    document.getElementById('creadoPor').disabled = true;
                    document.getElementById('modal-hora-fin').disabled = true;
                    document.getElementById('clienteContainer').disabled = true;
                    document.getElementById('cliente_info').disabled = true;

                    //Si existe la nota la asignamos y deshabilitamos el campo 

                    //console.log('ACAAAAAAAAAA' + eventoData['nota'] && eventoData['nota']['id']);
                    if (eventoData['nota'] && eventoData['nota']['id']) {
                        //Mostramos el formulario de baja y ocultamos el de guardar
                        document.getElementById('nota-id').value = eventoData['nota']['id'];
                        document.getElementById('nota-id-baja').value = eventoData['nota']['id'];

                        const fechaActual = document.getElementById('fechaNota').value;
                        const baseDomain = window.location.origin;
                        const currentPath = window.location.pathname;
                        const pathSegments = currentPath.split('/');
                        pathSegments.pop();
                        const basePath = pathSegments.join('/') + '/';

                        bajaForm.action = baseDomain + basePath + 'agenda/' + eventoData['nota']['id'] + '?fecha=' + fechaActual;


                        // Mostrar baja, ocultar guardar
                        bajaForm.style.cssText = 'display: block !important;';
                        document.getElementById('btnGuardar').style.display = 'none';
                        document.getElementById('creadoPorContainer').style.display = 'block';
                        document.getElementById('clienteContainer').style.display = 'block';
                        document.getElementById('div-buscar-propiedad').style.display = 'none';
                        document.getElementById('cliente-nombre').style.display = 'none';
                        document.getElementById('clientelefono').style.display = 'none';
                      



                        setTimeout(() => {
                            console.log('Estado de visibilidad del botón de baja:',
                                bajaForm.style.display,
                                'Dimensiones:',
                                bajaForm.offsetWidth,
                                bajaForm.offsetHeight,
                                'Estilo completo:', bajaForm.style.cssText);
                        }, 100);
                        


                        
                        //console.log('ID del tab:', sectorId);
                        if (sectorId == '2') {
                            console.log('Ventas-tab o Alquiler-tab');
                            document.getElementById('clienteContainer').style.display = 'block';
                            document.getElementById('input-alquiler-container').style.display = 'none'; 
                        } else if (sectorId == '3') {
                            document.getElementById('clienteContainer').style.display = 'block';
                             document.getElementById('input-alquiler-container').style.display = 'block'; 
                        } else {
                            document.getElementById('clienteContainer').style.display = 'none';
                            document.getElementById('input-alquiler-container').style.display = 'none'; 
    
                        }




                    } else {
                        console.error('No se encontró ID de nota en los datos del evento');
                        bajaForm.style.display = 'none';
                        document.getElementById('btnGuardar').style.display = 'block';
                        document.getElementById('creadoPorContainer').style.display = 'none';
                        document.getElementById('clienteContainer').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error al procesar datos del evento:', error);
                    bajaForm.style.display = 'none';
                    document.getElementById('btnGuardar').style.display = 'block';
                    document.getElementById('creadoPorContainer').style.display = 'none';
                    document.getElementById('clienteContainer').style.display = 'none';
                }
            } else {
                // No hay evento, limpiar todo
                document.getElementById('modal-descripcion').value = '';
                document.getElementById('cliente').value = '';
                document.getElementById('cliente-telefono-real').value = ''; 
                document.getElementById('propiedad').value = '';
                document.getElementById('propiedad-codigo-real').value = '';
                document.getElementById('nota-id').value = '';
                document.getElementById('nota-id-baja').value = '';
                document.getElementById('creadoPor').value = '';
            

                bajaForm.action = '';
                bajaForm.style.display = 'none';

                document.getElementById('btnGuardar').style.display = 'block';
                /* document.getElementById('cliente-nombre').style.display = 'block'; */
                document.getElementById('div-buscar-propiedad').style.display = 'block';
                /* document.getElementById('clientelefono').style.display = 'block'; */
                document.getElementById('creadoPorContainer').style.display = 'none';

                document.getElementById('calle-autocomplete').value = '';
                document.getElementById('calle_id').value = '';
                const id = this.id;
                console.log('esto sale por id', sectorId);
                if (sectorId == 2) {
                    console.log('esto sale por id', id);
                    document.getElementById('cliente-nombre').style.display = 'none';
                    document.getElementById('clientelefono').style.display = 'none';
                    document.getElementById('div-buscar-propiedad').style.display = 'block';
                    document.getElementById('clienteContainer').style.display = 'none';
                    document.getElementById('input-alquiler-container').style.display = 'none';
                } else if (sectorId == 3) {
                    document.getElementById('cliente-nombre').style.display = 'block';
                    document.getElementById('clientelefono').style.display = 'block';
                    document.getElementById('div-buscar-propiedad').style.display = 'block';
                    document.getElementById('clienteContainer').style.display = 'none';
                    document.getElementById('input-alquiler-container').style.display = 'block';
/* inputAlquilerContainer.style.display = 'block'; */
                    
                    /* document.getElementById('div-calle-propiedad').style.display = 'block';  */
                    document.getElementById('div-codigo-propiedad').style.display = 'block'; 
                } else {
                    document.getElementById('div-buscar-propiedad').style.display = 'none';
                    document.getElementById('clienteContainer').style.display = 'none';
                    document.getElementById('input-alquiler-container').style.display = 'none';
                    /* inputAlquilerContainer.style.display = 'none';
 */
                }

                // Habilitar campos cuando no hay nota
                document.getElementById('modal-descripcion').disabled = false;
                document.getElementById('cliente').disabled = false;
                document.getElementById('propiedad').disabled = false;
                document.getElementById('calle-autocomplete').disabled = false;
                document.getElementById('modal-hora-fin').disabled = false;


            }

            var myModal = new bootstrap.Modal(document.getElementById('calendarEventModal'));
            myModal.show();
        });
    });
});
