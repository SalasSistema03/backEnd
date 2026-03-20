/**
 * Inicializa la funcionalidad de búsqueda de propiedades
 */
function inicializarBusquedaPropiedades() {
    const btnBuscar = document.getElementById("btnBuscarPropiedades");
    //BUSCA EL BOTON DE BUSCAR
    if (btnBuscar) {
        btnBuscar.addEventListener("click", buscarPropiedades);
    }

    // Campos de búsqueda (exclusivos entre sí)
    const codigoEl = document.getElementById("inputCodigoPropiedad");
    const calleEl = document.getElementById("inputCallePropiedad");
    const dormEl = document.getElementById("inputDormPropiedad");
    const baniosEl = document.getElementById("inputBaniosPropiedad");
    const cocheraEl = document.getElementById("inputCocheraPropiedad");

    const inputs = [codigoEl, calleEl, dormEl, baniosEl, cocheraEl].filter(Boolean);

    function hasValue(el) {
        if (!el) return false;
        if (el.tagName === 'SELECT') return el.value !== '' && el.value != null;
        return (el.value || '').trim() !== '';
    }

    function bloquearSegunCodigo() {
        const codigoTieneValor = hasValue(codigoEl);
        inputs.forEach((el) => {
            if (!el) return;
            // El campo código nunca se deshabilita
            if (el === codigoEl) {
                el.disabled = false;
            } else {
                // Solo bloquear otros si código tiene valor
                el.disabled = codigoTieneValor;
            }
        });
    }

    function onChange() {
        // Reevaluar bloqueo basado únicamente en el código
        bloquearSegunCodigo();
    }

    if (codigoEl) {
        codigoEl.addEventListener('input', onChange);
        codigoEl.addEventListener('change', onChange);
    }

    // Al abrir el modal: evaluar estado inicial solo según código
    bloquearSegunCodigo();

    // Event listener para seleccionar propiedades
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".seleccionar-propiedad");
        //BUSCA EL BOTON DE SELECCIONAR
        if (!btn) return;
        //SI NO ENCUENTRA EL BOTON DE SELECCIONAR, NO HACE NADA
        seleccionarPropiedad(btn);
    });
}

/**
 * Realiza la búsqueda de propiedades
 */
async function buscarPropiedades() {
  
    //BUSCA EL CODIGO
    const codigo = document.getElementById("inputCodigoPropiedad").value.trim();
    //BUSCA LA CALLE
    const calle = document.getElementById("inputCallePropiedad").value.trim();
    //BUSCA LA DORM
    const dorm = document.getElementById("inputDormPropiedad").value.trim();
    //BUSCA LA BAÑOS
    const banios = document.getElementById("inputBaniosPropiedad").value.trim();
    //BUSCA LA COCHERA
    const cochera = document.getElementById("inputCocheraPropiedad").value.trim();
    //BUSCA LA TABLA
    const tbody = document.getElementById("tbodyFiltraPropiedades");

    // Mostrar estado de carga
    tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted">Buscando...</td></tr>';

    try {
        // Construir URL usando la configuración de Laravel
        const url = `${window.AsesoresConfig.urls.propiedadesBuscar
            }?codigo=${encodeURIComponent(codigo)}&calle=${encodeURIComponent(
                calle
            )}`;

        //console.log('URL de búsquedas:', url); // Para debugging
        //HACE LA PETICION
        const response = await fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        //SI LA PETICION NO ES EXITOSA, Lanza un error
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        //SI LA PETICION ES EXITOSA, Llama a la funcion buscarPropiedades
        const data = await response.json();
        tbody.innerHTML = "";
        //SI NO HAY DATOS, MUESTRA UN MENSAJE
        if (data.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="4" class="text-center text-muted">No se encontraron resultados.</td></tr>';
            return;
        }
        //SI HAY DATOS, MUESTRA LOS DATOS
        data.forEach((prop) => {
            const sinMensajes = document.querySelector('.ocultar-sin-mensajes');
            //console.log('sinMensajes', sinMensajes);
            if (sinMensajes) {
                sinMensajes.style.display = 'none';
            }
             //console.log(prop); 
            
            //SI EL CODIGO ES NULL, NO HACE NADA
            if (prop.codigo_v == null) {
                return;
            }
            // Construir fila solo si cumple con los filtros
            const codigo_mostrar = prop.codigo_v || "-";
            const direccion = `${prop.nombre_calle || ""} ${prop.numero_calle || ""}`.trim();
            const zona = prop.zona || "-";
            const dormProp = prop.dorm || "-";
            const baniosProp = prop.banios || "-";
            const cocheraProp = prop.cochera || "NO";

            // Si hay código ingresado, filtrar exclusivamente por código exacto
            if ((codigo || "") !== "") {
                if (prop.codigo_v !== null && prop.codigo_v == codigo) {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                    <td class="text-center">${codigo_mostrar}</td>
                    <td>${direccion}</td>
                    <td>${zona}</td>
                    <td>${dormProp}</td>
                    <td>${baniosProp}</td>
                    <td>${cocheraProp}</td>
                    <td>
                        <button class="btn btn-sm btn-success seleccionar-propiedad" 
                                data-id="${prop.id}" 
                                data-codigo="${codigo_mostrar}">
                            Seleccionar
                        </button>
                    </td>`;
                    tbody.appendChild(tr);
                }
                return; // si hay código, no evaluamos otros filtros
            }

            // AND de los demás filtros: solo se exige el que el usuario completó
            let coincide = true;
            if ((calle || "") !== "") {
                const nombreCoincide = prop.nombre_calle && prop.nombre_calle.toLowerCase().includes(calle.toLowerCase());
                const numeroCoincide = (prop.numero_calle || "") == calle;
                coincide = coincide && (nombreCoincide || numeroCoincide);
            }
            if ((dorm || "") !== "") {
                coincide = coincide && (String(prop.dorm) == String(dorm));
            }
            if ((banios || "") !== "") {
                coincide = coincide && (String(prop.banios) == String(banios));
            }
            if ((cochera || "") !== "") {
                coincide = coincide && (String(prop.cochera) == String(cochera));
            }

            if (coincide) {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td class="text-center">${codigo_mostrar}</td>
                <td>${direccion}</td>
                <td>${zona}</td>
                <td>${dormProp}</td>
                <td>${baniosProp}</td>
                <td>${cocheraProp}</td>
                <td>
                    <button class="btn btn-sm btn-success seleccionar-propiedad" 
                            data-id="${prop.id}" 
                            data-codigo="${codigo_mostrar}">
                        Seleccionar
                    </button>
                </td>`;
                tbody.appendChild(tr);
            }

           
        });
    } catch (error) {
        //MUESTRA UN ERROR
        console.error("Error en búsqueda:", error);
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error al buscar propiedades: ${error.message}</td></tr>`;
    }
}

/**
 * Selecciona una propiedad  y la almacena en la base de datos
 * @param {HTMLElement} btn - Botón de selección clickeado
 */
function seleccionarPropiedad(btn) {
    //BUSCA EL ID DE LA PROPIEDAD
    const propiedadId = btn.dataset.id;
    //console.log("propiedadId", propiedadId);
    //BUSCA EL CODIGO DE LA PROPIEDAD
    const codigo = btn.dataset.codigo;
    //console.log("codigo", codigo);
    //BUSCA EL ID DEL CRITERIO DE VENTA
    const id_criterio_venta = document.getElementById("input-id-criterio").value;
    //console.log("id_criterio_venta", id_criterio_venta);

    //SI NO SE SELECCIONO NINGUN CRITERIO, MUESTRA UN ERROR
    if (id_criterio_venta == null || id_criterio_venta == "") {
        alert("No se selecciono ningun criterio");
        return;
    }

    //CONSTRUYE LA URL
    const urlpropiedad = `chatpropiedad/${propiedadId}`;

    //HACE LA PETICION
    fetch(urlpropiedad)
        .then((response) => response.json())
        .then((data) => {
            return fetch("historialCodOfrecimiento", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    propiedad: data,
                    id_usuario: id_criterio_venta,
                }),
            });
        })
        .then((response) => {
            if (!response.ok) {
                return response.text().then((text) => {
                    throw new Error(text);
                });
            }
            return response.json();
        })
        .then((responseData) => {
            // Cerrar el modal de búsqueda
            const modalElement = document.getElementById("exampleModal");
            //CIERRA EL MODAL
            const modal = bootstrap.Modal.getInstance(modalElement);
            //SI EL MODAL EXISTE, CIERRA EL MODAL
            if (modal) {
                modal.hide();
            }
            // Recargar la conversación (igual que devoluciones)
            if (typeof mostrarEstado === "function") {
                mostrarEstado(id_criterio_venta);
            }
        })
        .catch((error) => {
            //MUESTRA UN ERROR
            alert("Error al seleccionar la propiedad:\n" + error.message);
        });
}
// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", inicializarBusquedaPropiedades);
