import { mostrarAlertaError } from '../../core/utils.js';
import { getData, getBaseUrl } from '../../core/ajax.js';


const listaCriterioVenta = [];
const listaCriterioAlquiler = [];

//Se obtiene el ID del cliente desde el div con el atributo data-id
const el = document.getElementById('datos-cliente');
const idCliente = el.dataset.idCliente;
const idAsesor = el.dataset.idAsesor;
const idAsesorAlquiler = el.dataset.idAsesorAlquiler;
const idUsuario = el.dataset.usuarioId;
const telefonoCliente = el.dataset.telefono;

const url_obtener_criterios = `${getBaseUrl()}/cliente/${telefonoCliente}`;


document.getElementById("btn_guardar_cliente").addEventListener("click", function () {

    const tipoInmuebleSelect = document.getElementById("id_tipo_inmueble");
    const cocheraSelect = document.getElementById("cochera");
    const zonaSelect = document.getElementById("zona");

    // Validar que se haya seleccionado un tipo de inmueble
    if (tipoInmuebleSelect.value === "") {
        mostrarAlertaError("Debe seleccionar un tipo de inmueble", "Error al guardar");
        return;
    }
    // Validar que se haya seleccionado una cochera
    if (cocheraSelect.value === "") {
        mostrarAlertaError("Debe seleccionar una cochera", "Error al guardar");
        return;
    }
    // Validar que se haya seleccionado una zona
    if (zonaSelect.value === "") {
        mostrarAlertaError("Debe seleccionar una zona", "Error al guardar");
        return;
    }

    const criterios = {
        id_sector_asesor: "venta",
        sector_asesor_texto: "Venta",

        id_tipo_inmueble: tipoInmuebleSelect.value,
        tipo_inmueble_texto: tipoInmuebleSelect.options[tipoInmuebleSelect.selectedIndex].text,

        cant_dormitorios: document.getElementById("cant_dormitorios").value || 0,

        cochera: cocheraSelect.value,
        cochera_texto: cocheraSelect.options[cocheraSelect.selectedIndex].text,

        id_zona: zonaSelect.value,
        zona_texto: zonaSelect.options[zonaSelect.selectedIndex].text,

        fecha_criterio: new Date().toISOString().slice(0, 10)
    };
    guardarCriterios(criterios);
});



export async function guardarCriterios(criterios) {
    const resultado = await obetnerCriteriosAsignados(
        criterios.id_sector_asesor,
        Number(criterios.id_tipo_inmueble),
        criterios.cant_dormitorios
    );

    // Verificamos si ya existe un criterio similar
    if (resultado?.existe) {
        console.warn(`Criterio ya existente. ID del criterio duplicado: ${resultado.id_criterio_venta}`);
        //return; // Cancelamos la ejecución si ya existe
    }

    // Asignar ID único para poder eliminar luego
    criterios.id = Date.now();

    criterios.estado_criterio_venta = "activo";
    listaCriterioVenta.push(criterios);
    agregarFilaTabla("venta", criterios);
 


    // Cerrar el modal
    const modalElement = document.getElementById('modal_criterio');
    const modalBootstrap = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalBootstrap.hide();
}


function agregarFilaTabla(tipo, datos) {
    const tbody = document.querySelector(`#tabla_criterios_${tipo} tbody`);
    const nuevaFila = document.createElement("tr");

    nuevaFila.innerHTML = `
        <td>${datos.tipo_inmueble_texto}</td>
        <td>${datos.cant_dormitorios}</td>
        <td>${datos.cochera_texto}</td>
        <td>${datos.zona_texto}</td>
        <td>Activo</td>
        <td>${datos.fecha_criterio}</td>
        <td><button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="${datos.id}" data-tipo="${tipo}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg>
        </button></td>
    `;

    tbody.appendChild(nuevaFila);
}


function obtenerCriteriosSoloIDs(lista) {
    return lista.map(c => {
        let obj = {
            id_cliente: idCliente,
            //id_sector_asesor: c.id_sector_asesor,
            usuario_id: idUsuario,
            id_categoria: c.id_categoria,
            id_tipo_inmueble: c.id_tipo_inmueble,
            cant_dormitorios: c.cant_dormitorios,
            cochera: c.cochera,
            id_zona: c.id_zona,
            id_propiedad: c.id_propiedad,
            fecha_criterio: c.fecha_criterio,
            estado_criterio_venta:"Activo"
        };
        return obj;
    });
}




// Lógica para eliminar criterio tanto del array como de la tabla
document.addEventListener('click', function (e) {
    // Permitir click en botón o en cualquier elemento interno (SVG, path)
    const btn = e.target.closest('.btn-eliminar');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const tipo = btn.dataset.tipo;
    const lista = tipo === 'venta' ? listaCriterioVenta : listaCriterioAlquiler;
    const index = lista.findIndex(c => c.id === id);
    if (index > -1) lista.splice(index, 1);
    // Remover fila de la tabla
    btn.closest('tr').remove();
});



// Esta funcion OBTIENE los criterios que ya estan asigandas al ciente
// luego VERIFICA si la propiedas seleccionada ya exite
// TRUE si existe, FALSE si no existe
async function obetnerCriteriosAsignados(sector, tipo_inmueble, dormitorio) {
    const resultado = await getData(url_obtener_criterios);
    const criterios_venta_asignados = [];

    if (!resultado.ok) {
        console.error('Error al obtener propiedades. Status:', resultado.status);
        mostrarAlertaError('Error al obtener propiedades asignadas.');
        return;
    }

    if (!resultado.data || resultado.data.length === 0) {
        mostrarAlertaError('No se encontraron propiedades asignadas al cliente.');
        return;
    }

    criterios_venta_asignados.push(...resultado.data.cliente.criterio_busqueda_venta);

    if (sector === 'venta') {
        for (let i = 0; i < criterios_venta_asignados.length; i++) {
            const criterio = criterios_venta_asignados[i];

            if (
                criterio.id_tipo_inmueble === tipo_inmueble &&
                criterio.estado_criterio_venta === 'Activo' &&
                criterio.cant_dormitorios === dormitorio
            ) {
                mostrarAlertaError('Este criterio ya está asignado y activo, no se puede repetir');
                return {
                    existe: true,
                    id_criterio_venta: criterio.id_criterio_venta
                };
            }

        }

        // Si no se encontró ninguno parecido
        return {
            existe: false,
            id_criterio_venta: "hola"
        };
    }



}




export { listaCriterioVenta, listaCriterioAlquiler, obtenerCriteriosSoloIDs, obetnerCriteriosAsignados };