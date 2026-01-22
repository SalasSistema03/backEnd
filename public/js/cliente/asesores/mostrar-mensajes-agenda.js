
document.addEventListener("DOMContentLoaded", function () {
    // Cargamos el formulario
    const form = document.getElementById("calendarEventForm");
    // Cargamos el botón
    const btnGuardar = document.getElementById("btnGuardar");

    // Quitar onclick tradicional
    btnGuardar.removeAttribute("onclick");

    // Interceptar el click en el botón Guardar
    btnGuardar.addEventListener("click", function (e) {
        e.preventDefault(); // evita submit normal
        enviarEvento();
    });

    // Interceptar submit del formulario por si alguien presiona Enter
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        enviarEvento();
    });

    // Función para enviar el evento
    async function enviarEvento() {
        // Deshabilitar el botón y cambiar su texto
        btnGuardar.disabled = true;
        btnGuardar.textContent = "Guardando...";

        try {
            // Cargamos el formulario
            let formData = new FormData(form);

            // Enviamos el formulario
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                }
            });

            // Recibimos la respuesta
            const data = await response.json();

            // Si todo está bien
            if (data.success) {
                const sinMensajes = document.querySelector('.ocultar-sin-mensajes');
                //console.log('sinMensajes', sinMensajes);
                if (sinMensajes) {
                    sinMensajes.style.display = 'none';
                }
                // 1️⃣ Agregar a la conversación
                const conversacionUl = document.querySelector("#conversacion-container ul");
                const tbody = document.getElementById("tbodyFiltraPropiedades");

                // Si encontramos el contenedor de la conversación
                if (conversacionUl) {
                    // Creamos el elemento
                    const li = document.createElement("li");
                    // Le agregamos la clase
                    li.className = "list-group-item mensaje-conversacion";

                    // Formatear fecha
                    const fechaOriginal = data.evento.fecha_hora;
                    // Formateamos la fecha
                    const fechaFormateada = fechaOriginal.split('.')[0].replace('T', ' ');

                    // Agregamos el mensaje
                    li.innerHTML =
                        `${fechaFormateada}&nbsp;&nbsp;&nbsp;${data.evento.mensaje}&nbsp;&nbsp;&nbsp;<button class="btn btn-devolucion-chat">Devolucion</button>`;

                    // Agregamos el elemento al contenedor
                    conversacionUl.appendChild(li);
                    // Scroll al último mensaje
                    conversacionUl.scrollTop = conversacionUl.scrollHeight;

                    // Parte de códigos
                    const codigosUL = document.querySelector("#codigos-container ul");

                    // Si encontramos el contenedor de los códigos
                    if (codigosUL) {
                        // Creamos el elemento
                        const li2 = document.createElement("li");
                        // Le agregamos la clase
                        li2.className = "list-group-item codigo-item";

                        // Creamos el div
                        const div = document.createElement("div");
                        // Le agregamos la clase
                        div.className = "codigo-link row codigo_muestra";

                        // Creamos el div2
                        const div2 = document.createElement("div");
                        // Le agregamos la clase
                        div2.className = "col-12";
                        // Agregamos el código
                        div2.innerHTML = data.evento.codigo_muestra;
                        // Agregamos el div2 al div
                        div.appendChild(div2);

                        // Creamos el div3
                        const div3 = document.createElement("div");
                        // Le agregamos la clase
                        div3.className = "col-12 direccion-codigo";
                        // Agregamos la dirección
                        div3.innerHTML = data.evento.direccion;
                        // Agregamos el div3 al div
                        div.appendChild(div3);
                        // Agregamos el div al li2
                        li2.appendChild(div);
                        // Agregamos el li2 al contenedor
                        codigosUL.appendChild(li2);
                    }
                }


                // 2️⃣ Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById(
                    "calendarEventModal"));
                modal.hide();

                // 3️⃣ Limpiar formulario
                form.reset();
            } else {
                alert("Error al guardar: " + (data.message || "Intenta de nuevo"));
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Ocurrió un error al guardar");
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar";
        }
    }

});
