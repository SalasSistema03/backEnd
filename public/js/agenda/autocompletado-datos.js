

// Autocompletado de teléfono cliente alquileres
// Input donde el usuario escribe el teléfono del cliente
const inputCliente = document.getElementById('cliente');

// Contenedor donde se muestran las sugerencias
const sugerenciasDiv = document.getElementById('sugerencias-clientes');

const tablaContainer = document.getElementById('input-alquiler-container');
const tablaBody = document.querySelector('.calendar-table-body');

// Validación defensiva: solo continúa si ambos elementos existen en el DOM
if (inputCliente && sugerenciasDiv) {

    // Escucha el evento "input" (se dispara en cada tecla)
    inputCliente.addEventListener('input', function () {

        // Texto ingresado por el usuario
        const query = this.value;

        // Si hay menos de 3 caracteres, no se consulta al backend
        if (query.length < 3) {
            sugerenciasDiv.innerHTML = '';
            if (tablaContainer) {
                tablaContainer.style.display = 'none';
            }
            return;
        }

        // Llamada al backend para buscar clientes por teléfono
        fetch(window.RUTA_CLIENTE + '?telefono=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(clientes => {

                // Limpia sugerencias anteriores
                sugerenciasDiv.innerHTML = '';

                // Recorre los clientes devueltos por la API
                clientes.forEach(cliente => {

                    // Crea un elemento tipo link para cada sugerencia
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action';

                    // Texto visible de la sugerencia
                    item.textContent =
                        `${cliente.telefono} - ${cliente.nombre.toUpperCase()} `;

                    // Acción al hacer click sobre una sugerencia
                    item.onclick = () => {

                        // Setea el teléfono en el input principal
                        inputCliente.value = cliente.telefono;

                        // Guarda el ID real del cliente en un input oculto
                        document.getElementById('cliente-telefono-real').value = cliente.id_cliente;

                        // Guarda el nombre del cliente en otro input oculto
                        document.querySelector('input[name="cliente_nombre"]').value = cliente.nombre;

                        // Oculta las sugerencias luego de seleccionar
                        sugerenciasDiv.innerHTML = '';

                        cargarHistorialCliente(cliente.id_cliente);
                    };

                    // Agrega la sugerencia al contenedor
                    sugerenciasDiv.appendChild(item);
                });
            });
    });

    // Cierra las sugerencias si el usuario hace click fuera del input o del listado
    document.addEventListener('click', function (e) {
        if (!inputCliente.contains(e.target) && !sugerenciasDiv.contains(e.target)) {
            sugerenciasDiv.innerHTML = '';
        }
    });
}


// Coloca esta función al inicio de tu archivo JS
function limpiarTablaHistorial() {
    const tablaContainer = document.getElementById('input-alquiler-container');
    const tablaBody = document.querySelector('.calendar-table-body');
    
    // Limpiar el tbody
    if (tablaBody) {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted">
                    Seleccione un cliente para ver su historial de muestras
                </td>
            </tr>
        `;
    }
    
    // Ocultar la tabla
    if (tablaContainer) {
        tablaContainer.style.display = 'none';
    }
}

function cargarHistorialCliente(clienteId) {
    // URL de la ruta que creaste (ajusta según tu configuración)
    const urlHistorial = window.RUTA_HISTORIAL + '?cliente_id=' + clienteId;

    fetch(urlHistorial)
        .then(response => response.json())
        .then(historial => {
            // Limpiar tbody
            tablaBody.innerHTML = '';

            if (historial.length === 0) {
                // Mostrar mensaje si no hay datos
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            No hay registros de muestras para este cliente
                        </td>
                    </tr>
                `;
            } else {
                // Llenar la tabla con los datos
                historial.forEach(nota => {
                   //console.log(nota);
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${nota.fecha} ${nota.hora_inicio}Hs</td>
                        <td>${nota.cod_alquiler} - ${nota.inmueble} ${nota.numero_calle}</td>
                        <td>${nota.asesor}</td>
                        <td>${nota.activo}</td>
                        
                    `;
                    tablaBody.appendChild(fila);
                });
            }

            // Mostrar el contenedor de la tabla
            if (tablaContainer) {
                tablaContainer.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error al cargar historial:', error);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-danger">
                        Error al cargar el historial
                    </td>
                </tr>
            `;
        });
}
/* ------------------------------------------------------------------------------------------ */

// Autocompletado de código de propiedad
// Input donde el usuario escribe el código o parte del código de la propiedad
const inputPropiedad = document.getElementById('propiedad');

// Contenedor donde se listan las sugerencias de propiedades
const sugerenciasPropDiv = document.getElementById('sugerencias-propiedades');

// Validación defensiva: solo ejecutar si ambos elementos existen
if (inputPropiedad && sugerenciasPropDiv) {

    // Escucha cada cambio en el input
    inputPropiedad.addEventListener('input', function () {

        // Texto ingresado por el usuario
        const query = this.value;

        // Si hay menos de 2 caracteres, no se consulta al backend
        if (query.length < 2) {
            sugerenciasPropDiv.innerHTML = '';
            return;
        }

        // Consulta al backend buscando propiedades por código
        fetch(window.RUTA_PROPIEDAD + '?codigo=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(propiedades => {

                // Limpia sugerencias anteriores
                sugerenciasPropDiv.innerHTML = '';

                // =========================
                // Determinar sector activo
                // =========================

                // Botón de sector activo (Ventas / Alquiler / etc.)
                // Fallback: si no hay activo, toma el primero disponible
                const btnActiva =
                    document.querySelector('.sector-btn.active') ||
                    document.querySelector('.sector-btn');

                // Tab activo (fallback alternativo)
                const paneActivo = document.querySelector('.tab-pane.active');

                // Determina el sector actual:
                // 1) dataset del botón
                // 2) id del tab-pane
                // 3) por defecto "Ventas"
                const sectorNow =
                    btnActiva?.dataset?.sector ??
                    paneActivo?.id ??
                    'Ventas';

                // Normaliza a minúsculas para comparación
                const sectorNowNorm = sectorNow.toLowerCase();

                console.log('Sector detectado:', sectorNowNorm);

                // =========================
                // Filtrado según sector
                // =========================

                // Si es ventas, solo se muestran propiedades con codigo_v en otro caso muestra solamente las de codigo_alquiler
                /* const esVentas = sectorNowNorm.startsWith('venta'); */

                if(sectorNowNorm === 'ventas') {
                    const propiedadesFiltradas = (propiedades || []).filter(prop => prop?.codigo_v != null);
                    console.log(propiedadesFiltradas);
                    propiedadesFiltradas.forEach(prop => {
                        
                       const texto =
                       `${prop.codigo_v} | ` +
                       `${prop.nombre_calle ?? ''} ${prop.numero_calle ?? ''} | ` +
                       `${prop.estado_v.name}`;

                       // Crea el item de la lista
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = texto;

                        item.onclick = () => {

                        // Setea el input visible con código + dirección
                        inputPropiedad.value =
                            prop.codigo_v + ' - ' +
                            prop.nombre_calle + ' ' +
                            prop.numero_calle;

                        // Guarda el ID real de la propiedad en input oculto
                        document.getElementById('propiedad-codigo-real').value = prop.id;

                        // Limpia las sugerencias
                        sugerenciasPropDiv.innerHTML = '';
                        };

                        // Agrega el item al contenedor
                        sugerenciasPropDiv.appendChild(item);
                    });
                }else if(sectorNowNorm === 'alquiler'){
                    /* console.log('entroaAlquiler'); */
                    const propiedadesFiltradas = (propiedades || []).filter(prop => prop?.codigo_a != null);
                    console.log(propiedadesFiltradas);
                    propiedadesFiltradas.forEach(prop => {
                       const texto =
                       `${prop.codigo_a} | ` +
                       `${prop.nombre_calle ?? ''} ${prop.numero_calle ?? ''} | ` +
                       `${prop.estado_a.name}`;

                       // Crea el item de la lista
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = texto;

                        item.onclick = () => {

                        // Setea el input visible con código + dirección
                        inputPropiedad.value =
                            prop.codigo_a + ' - ' +
                            prop.nombre_calle + ' ' +
                            prop.numero_calle;

                        // Guarda el ID real de la propiedad en input oculto
                        document.getElementById('propiedad-codigo-real').value = prop.id;

                        // Limpia las sugerencias
                        sugerenciasPropDiv.innerHTML = '';
                        };

                        // Agrega el item al contenedor
                        sugerenciasPropDiv.appendChild(item);
                    });

                }

            });
    });
}

/* ------------------------------------------------------------------------------------------ */
// Autocompletado y manejo del campo calle
const inputCalle = document.getElementById('calle-autocomplete');
//console.log('inputCalle',inputCalle);
const inputCalleHidden = document.getElementById('calle_id');
//console.log('inputCalleHidden',inputCalleHidden);
const sugerenciasCalleDiv = document.getElementById('calle-suggestions');
//console.log('sugerenciasCalleDiv',sugerenciasCalleDiv);
// Esta función inicializa el campo calle y asegura que su valor sea visible
function inicializarCampoCalle() {
    const activeSectorBtn = document.querySelector('.sector-btn.active') || document.querySelector('.sector-btn');
    const activeTabPane = document.querySelector('.tab-pane.active');
    const sectorActual = (activeSectorBtn?.dataset?.sector) || (activeTabPane ? activeTabPane.id : null);
    //console.log('Sector actual:', sectorActual);
    const sectorButtons = document.querySelectorAll('.sector-btn');
    if (sectorButtons && sectorButtons.length) {
        sectorButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const s = e.target?.dataset?.sector || null;
                //console.log('Sector actual:', s);
            });
            btn.addEventListener('click', function (e) {
                const s = e.currentTarget?.dataset?.sector || null;
                //console.log('Sector actual:', s);
            });
        });
    }
    if (inputCalle && inputCalleHidden) {
        //console.log('Inicializando campo calle');
        //onsole.log('Valor actual de calle_id:', inputCalleHidden.value);

        // Si hay un valor en el campo oculto, pero el campo visible está vacío, copiarlo
        if (inputCalleHidden.value && !inputCalle.value) {
            inputCalle.value = inputCalleHidden.value;
            //console.log('Valor copiado al campo visible:', inputCalle.value);
        }

        // Escuchar cambios en el campo visible para actualizar el campo oculto
        inputCalle.addEventListener('input', function () {
            inputCalleHidden.value = this.value;
            //console.log('Campo oculto actualizado:', inputCalleHidden.value);
        });

        // Agregar listener al abrir el modal para asegurar que se muestre el valor
        const modal = document.getElementById('calendarEventModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function () {
                //console.log('Modal mostrado - valor de calle_id:', inputCalleHidden.value);
                if (inputCalleHidden.value && !inputCalle.value) {
                    inputCalle.value = inputCalleHidden.value;
                    //console.log('Valor de calle restaurado en modal:', inputCalle.value);
                }
            });
        }
    } else {
        console.error('No se encontraron los elementos del campo calle');
    }

    if (inputCalle && sugerenciasCalleDiv) {
        inputCalle.addEventListener('input', function () {
            const query = this.value;
            if (query.length < 3) {  // Mínimo 3 caracteres para buscar
                sugerenciasCalleDiv.innerHTML = '';
                return;
            }

            /*  fetch(`/salas/salas/public/buscar-calle?calle=${encodeURIComponent(query)}`) */
            fetch(window.RUTA_CALLE + '?calle=' + encodeURIComponent(query))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la búsqueda');
                    }
                    return response.json();
                })
                .then(calles => {
                    sugerenciasCalleDiv.innerHTML = '';
                    const btnActiva = document.querySelector('.sector-btn.active') || document.querySelector('.sector-btn');
                    const paneActivo = document.querySelector('.tab-pane.active');
                    const sectorNow = (btnActiva?.dataset?.sector) || (paneActivo ? paneActivo.id : null);
                    //Si no hay sector porque estamos ingresando por otro lado que no sea agenda por defecto le ponemos ventas
                    const sectorNowNorm = (sectorNow || 'Ventas').toLowerCase();
                    //console.log('Sector actualaa:', sectorNowNorm);
                    let lista = (calles || []);
                    if (sectorNowNorm.startsWith('venta')) {
                        lista = lista.filter(c => c?.codigo_v != null);
                        //console.log('Lista de calles después de filtrar:', lista);
                        lista.forEach(calle => {
                        console.log('estas son las calles de ka lista',calle);
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${calle.codigo_v ?? 'Sin Datos'} | ${calle.nombre_calle} ${calle.numero_calle || ''} - ${calle.estado_venta ?? 'Sin Datos'}`.trim();
                        item.onclick = () => {
                            inputCalle.value = calle.nombre_calle + (calle.numero_calle ? ' ' + calle.numero_calle : '');
                            inputCalleHidden.value = calle.id;
                            sugerenciasCalleDiv.innerHTML = '';
                        };
                        sugerenciasCalleDiv.appendChild(item);
                    });
                    } else if (sectorNowNorm.startsWith('alquiler')) {
                        lista = lista.filter(c => c?.codigo_alquiler != null);
                        //console.log('Lista de calles después de filtrar:', lista);

                        lista.forEach(calle => {
                        console.log('estas son las calles de ka lista',calle);
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${calle.codigo_alquiler ?? 'Sin Datos'} | ${calle.nombre_calle} ${calle.numero_calle || ''} - ${calle.estado_alquiler ?? 'Sin Datos'}`.trim();
                        item.onclick = () => {
                            inputCalle.value = calle.nombre_calle + (calle.numero_calle ? ' ' + calle.numero_calle : '');
                            inputCalleHidden.value = calle.id;
                            sugerenciasCalleDiv.innerHTML = '';
                        };
                        sugerenciasCalleDiv.appendChild(item);
                    });

                    }
                    if (lista.length === 0) {
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action disabled';
                        item.textContent = 'No se encontraron calles';
                        sugerenciasCalleDiv.appendChild(item);
                        return;
                    }

                   
                })
                .catch(error => {
                    console.error('Error al buscar calles:', error);
                    sugerenciasCalleDiv.innerHTML = '';
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action disabled';
                    item.textContent = 'Error al buscar calles';
                    sugerenciasCalleDiv.appendChild(item);
                });
        });

        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!inputCalle.contains(e.target) && !sugerenciasCalleDiv.contains(e.target)) {
                sugerenciasCalleDiv.innerHTML = '';
            }
        });
    }
}

// Iniciar la función cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', inicializarCampoCalle);