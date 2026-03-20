
//funcion para mostrar Los mensajes

async function mostrarEstado(estado) {
    /* --------------------------------------Titulos----------------------------------------------------- */

    //console.log('estado', estado);
    // Obtener el elemento del criterio seleccionado
    const criterioElement = document.querySelector(`[onclick="mostrarEstado('${estado}')"]`);
//console.log('criterioElement', criterioElement);
    // Obtener el tipo de inmueble, dormitorios, zona y cochera
    const tipoInmueble = criterioElement ? criterioElement.getAttribute('data-tipo-inmueble') : '';
    //console.log('tipoInmueble', tipoInmueble);
    const cantDormitorios = criterioElement ? criterioElement.getAttribute('data-cant-dormitorios') : '';
    const zona = criterioElement ? criterioElement.getAttribute('data-zona') : '';
    const cochera = criterioElement ? criterioElement.getAttribute('data-cochera') : '';


    const criterioDefaultConversacion = document.getElementById('criterio-default-conversacion');

    const tipoInmuebleElementConversacion = document.getElementById('tipo-inmueble-seleccionado-conversacion');

    // Mostrar u ocultar elementos según corresponda
    if (tipoInmueble) {

        // Crear el texto para la conversación incluyendo todos los datos
        let textoConversacion = tipoInmueble;

        // Agregar zona si existe y que el nombre aparesca en mayusucla
        if (zona && zona !== 'No especificado' && zona !== 'null') {
            textoConversacion += ` | ZONA: ${zona.toUpperCase()}`;

        }

        // Agregar cochera si existe
        if (cochera && cochera !== 'No especificado' && cochera !== 'null') {
            textoConversacion += ` | COCHERA: ${cochera}`;
        }

        // Agregar dormitorios si existen
        if (cantDormitorios && cantDormitorios !== 'No especificado' && cantDormitorios !== 'null') {
            textoConversacion += ` | DORM: ${cantDormitorios}`;
        }

        tipoInmuebleElementConversacion.textContent = textoConversacion;
        tipoInmuebleElementConversacion.style.display = 'inline-block';
        

        // Ocultar textos por defecto
        if (criterioDefaultConversacion) criterioDefaultConversacion.style.display = 'none';
    } else {
        // Si no hay tipo de inmueble, mostrar los textos por defecto
        if (tipoInmuebleElement) tipoInmuebleElement.style.display = 'none';
        if (tipoInmuebleElementConversacion) tipoInmuebleElementConversacion.style.display = 'none';
        if (criterioDefault) criterioDefault.style.display = 'block';
        if (criterioDefaultConversacion) criterioDefaultConversacion.style.display = 'block';
    }
    /* ---------------------------------------------Mensajes---------------------------------------------- */


    //obtenemos el id criterio venta del onclick
    const id_criterio_venta = estado;
    //console.log('ID criterio venta:', id_criterio_venta);
    const conversacionContainer = document.getElementById('input-conversacion');
        if (conversacionContainer) {
            conversacionContainer.style.display = 'block';
        }

    //obtenemos el id de la conversacion
    const conversacion = document.querySelector('#conversacion-container ul');
    //console.log(conversacion);

    //guardamos el valor de id_criterio en el input hidden
    document.getElementById('input-id-criterio').value = id_criterio_venta;
    //console.log(document.getElementById('input-id-criterio').value);

    // Limpiar la lista de códigos al cambiar de criterio
    const codigosList = document.getElementById('codigo-list');
    if (codigosList) {
        codigosList.innerHTML = '';
    }


    // Construir la URL usando la configuración de Laravel
    const url = window.AsesoresConfig.urls.getConversacion.replace('__CRITERIO_ID__', id_criterio_venta);
    console.log(url);

    //Esperamos respuestas del back
    const response = await fetch(url);
    const mensajes = await response.json();
    //console.log(mensajes);
    if (conversacion) {
        conversacion.innerHTML = '';

        //Armamos el listado y mostramos los mensajes
        try {
            if (mensajes.length > 0) {
                mensajes.forEach(mensaje => {
                    const newItem = document.createElement('li');
                    newItem.classList.add('list-group-item', 'mensaje-conversacion');
                    newItem.setAttribute('id', `mensaje-${mensaje.id}`);
                    newItem.setAttribute('data-fecha-hora', mensaje.fecha_hora);
                    newItem.innerHTML = mensaje.fecha_hora + "&nbsp;&nbsp;&nbsp;" + mensaje.mensaje + "&nbsp;&nbsp;&nbsp;";
                    if (mensaje.devolucion == null && mensaje.tipo != 'conversacion') {
                        newButton = document.createElement('button');
                        newButton.classList.add( 'btn',  'btn-devolucion-chat');
                        newItem.appendChild(newButton);
                        newButton.innerText = 'Devolucion';
                        newButton.addEventListener('click', function () {
                            //abrir modal y enviar id del mensaje
                            $('#modal-devolucion').modal('show');
                            document.getElementById('id_mensaje').value = mensaje.id;
                            document.getElementById('tipo').value = mensaje.tipo;
                        });
                    }
                    if (mensaje.devolucion != null) {
                        const newDevolution = document.createElement('li');
                        newDevolution.classList.add( 'devolucion-chat', 'list-group-item', 'mensaje-conversacion');
                        newDevolution.setAttribute('id', `devolucion-${mensaje.id}`);
                        newDevolution.innerText = mensaje.fecha_devolucion + ' - - - ' + mensaje
                            .devolucion + ' - ';
                        newItem.appendChild(newDevolution);
                    }

                    console.log(mensaje);
                    conversacion.appendChild(newItem);
                });
            } else {
                const newItem = document.createElement('li');
                newItem.classList.add('list-group-item', 'text-center', 'text-muted', 'ocultar-sin-mensajes');
                newItem.innerText = 'No hay mensajes en esta conversación.';
                conversacion.appendChild(newItem);
            }
        } catch (error) {
            //console.error('Error al cargar la conversación:', error);
            const newItem = document.createElement('li');
            newItem.classList.add('list-group-item', 'text-center', 'text-danger');
            newItem.innerText = 'No se pudo cargar la conversación.';
            conversacion.appendChild(newItem);
        }
    }


    /* -------------------CODIGOS------------------- */


    // Sección corregida del código de códigos
    fetch('historialCodOfrecimiento/' + id_criterio_venta, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            const codigosList = document.getElementById('codigo-list');
            if (codigosList) {
                codigosList.innerHTML = '';
                data.forEach(codigo => {
                    //console.log('ESTOS SON TODOS LOS DATOS DE CODIGO',codigo);
                    const newItem = document.createElement('li');
                    newItem.classList.add('list-group-item', 'codigo-item');
                    newItem.setAttribute('data-codigo', codigo.codigo);
                    newItem.setAttribute('data-mensaje-id', codigo.fecha_hora); // CAMBIADO: usar codigo.fecha_hora en lugar de codigo.mensaje_id
                    

                    //console.log('CODIGOS', codigo);
                    if (codigo.codigo_ofrecimiento) {
                        let contenido = `<div class="codigo-link row codigo_ofrecimiento" data-mensaje-id="${codigo.fecha_hora}">
                        <div class="col-12">${codigo.codigo_ofrecimiento}  ${codigo.devolucion != null ? '<span class="ms-2 check-codigos-asesores">✓</span>' : ''}</div>
                        <div class="col-12 direccion-codigo">${codigo.direccion}</div>
                        </div>`;
                        newItem.innerHTML = contenido;
                    } else if (codigo.codigo_muestra) {
                        let contenido = `<div class="codigo-link row codigo_muestra" data-mensaje-id="${codigo.fecha_hora}">
                        <div class="col-12">${codigo.codigo_muestra} ${codigo.devolucion != null ? '<span class="ms-2 check-codigos-asesores">✓</span>' : ''}</div>
                        <div class="col-12 direccion-codigo">${codigo.direccion}</div>
                        </div>`;
                        newItem.innerHTML = contenido;
                    } else if (codigo.codigo_consulta) {
                        let contenido = `<div class="codigo-link row codigo_consulta" data-mensaje-id="${codigo.fecha_hora}">
                        <div class="col-12">${codigo.codigo_consulta} ${codigo.devolucion != null ? '<span class="ms-2 check-codigos-asesores">✓</span>' : ''}</div>
                        <div class="col-12 direccion-codigo">${codigo.direccion}</div>
                        </div>`;
                        
                        newItem.innerHTML = contenido;
                    }
                    codigosList.appendChild(newItem);

                    // Event listener para el scroll
                    const link = newItem.querySelector('.codigo-link[data-mensaje-id]'); // Solo seleccionar links que tengan data-mensaje-id
                    //console.log('LINK',link);
                    if (link) {
                        link.addEventListener('click', function (e) {
                            e.preventDefault();
                            const mensajeId = this.getAttribute('data-mensaje-id');
                            console.log('ID del mensaje a buscar:', mensajeId);

                            if (mensajeId && mensajeId !== 'undefined') {
                                // Buscar el elemento del mensaje por su atributo data-fecha-hora
                                const mensajeElement = document.querySelector(`[data-fecha-hora="${mensajeId}"]`);

                                if (mensajeElement) {
                                    console.log('Elemento del mensaje encontrado:', mensajeElement);

                                    // Hacer scroll suave hasta el mensaje
                                    mensajeElement.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });

                                    // Resaltar el mensaje momentáneamente
                                    mensajeElement.classList.add('mensaje-resaltado');
                                    setTimeout(() => {
                                        mensajeElement.classList.remove('mensaje-resaltado');
                                    }, 2000);
                                } else {
                                    console.log('No se encontró el elemento del mensaje con fecha:', mensajeId);
                                }
                            } else {
                                console.log('ID del mensaje es undefined o inválido');
                            }
                        });
                    }
                });
            }
        })
        .catch(error => console.error('Error al cargar códigos:', error));
}

 /* -------------------Evitar cargar  pagina cuand ose manda un mensaje normal------------------- */


document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("form-mensaje");

    form.addEventListener("submit", function(e) {
        e.preventDefault(); // 🔹 evita que se recargue la página

        // acá podrías enviar con fetch/AJAX
        const formData = new FormData(form);

        fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": form.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                //console.log("Mensaje enviado:", data);

                // Si todo está bien
                if (data.success) {
                    // 1️⃣ Agregar a la conversación
                    const conversacionUl = document.querySelector("#conversacion-container ul");
                    // Si encontramos el contenedor de la conversación
                    if (conversacionUl) {
                        // Creamos el elemento
                        const li = document.createElement("li");

                        // Le agregamos la clase
                        li.className = "list-group-item mensaje-conversacion";

                        // Formatear fecha
                        const fechaOriginal = data.message.fecha_hora;
                        //console.log(fechaOriginal);
                        // Agregamos el mensaje
                        li.innerHTML =
                            `${fechaOriginal}&nbsp;&nbsp;&nbsp;${data.message.mensaje}&nbsp;&nbsp;&nbsp;`;

                        // Agregamos el elemento al contenedor
                        conversacionUl.appendChild(li);

                        // Scroll automático
                        li.scrollIntoView({
                            behavior: "smooth",
                            block: "end"
                        });

                        form.reset(); // limpiar input después de enviar

                        //ocultar esta clase  ocultar-sin-mensajes
                        const sinMensajes = document.querySelector('.ocultar-sin-mensajes');
                        console.log('sinMensajes', sinMensajes);
                        if (sinMensajes) {
                            sinMensajes.style.display = 'none';
                        }
                    }

                }
            })
            .catch(error => console.error("Error:", error));
    });
});