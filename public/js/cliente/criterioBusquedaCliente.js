
import { validarCriteriosBusqueda } from './validaciones.js';
import { mostrarAlertaError } from '../core/utils.js';
const listaCriterioVenta = [];
const listaCriterioAlquiler = [];

document.getElementById("btn_guardar_cliente").addEventListener("click", function () {
    if (!validarCriteriosBusqueda()) {
        return; // Si la validación falla, no sigue con el proceso
    }

    const tipoInmuebleSelect = document.getElementById("id_tipo_inmueble");
    const cocheraSelect = document.getElementById("cochera");
    const zonaSelect = document.getElementById("zona");
    const criterios = {
        //id_sector_asesor: document.getElementById("sector_asesor").value,
        sector_asesor_texto: "venta", // Asignar texto fijo para sector de asesor

        id_tipo_inmueble: tipoInmuebleSelect.value,
        tipo_inmueble_texto: tipoInmuebleSelect.options[tipoInmuebleSelect.selectedIndex].text,

        cant_dormitorios: document.getElementById("cant_dormitorios").value || "-",

        cochera: cocheraSelect.value,
        cochera_texto: cocheraSelect.options[cocheraSelect.selectedIndex].text,

        id_zona: zonaSelect.value,
        zona_texto: zonaSelect.options[zonaSelect.selectedIndex].text,

        fecha_criterio: new Date().toISOString().slice(0, 10)
    };

    guardarCriterios(criterios);
});

export function guardarCriterios(criterios) {
    // Asignar ID único para poder eliminar luego
    criterios.id = Date.now();
    for (let criterio of listaCriterioVenta) {
        if (
            String(criterio.id_tipo_inmueble) === String(criterios.id_tipo_inmueble) &&
            String(criterio.cant_dormitorios) === String(criterios.cant_dormitorios)
        ) {
            mostrarAlertaError("Ya existe una propiedad con el miso criterio, se guardara el mismo");
            return; // ⛔ Termina la función si ya existe
        }
    }


/* 
    if (criterios.id_sector_asesor === "venta") { */
        criterios.estado_criterio_venta = "activo";
        listaCriterioVenta.push(criterios);
        agregarFilaTabla("venta", criterios);
     //   mostrarPestania("ventaCriterio-tab");
/*     } else if (criterios.id_sector_asesor === "alquiler") {
        criterios.estado_criterio_alquiler = "activo";
        listaCriterioAlquiler.push(criterios);
        agregarFilaTabla("alquiler", criterios);
        mostrarPestania("alquilerCriterio-tab"); */
/*     } else {
        mostrarAlertaError("Debe seleccionar un sector de asesor", "Error al guardar");
    } */
}

function agregarFilaTabla(tipo, datos) {
    const tbody = document.querySelector(`#tabla_criterios_${tipo} tbody`);
    const nuevaFila = document.createElement("tr");

    nuevaFila.innerHTML = `
        <td>${datos.tipo_inmueble_texto}</td>
        <td>${datos.cant_dormitorios}</td>
        <td>${datos.cochera_texto}</td>
        <td>${datos.zona_texto}</td>
        <td>${datos.fecha_criterio}</td>
        <td>activo</td>
        <td><button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="${datos.id}" data-tipo="${tipo}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg>
        </button></td>
    `;

    tbody.appendChild(nuevaFila);
}

/* function mostrarPestania(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
} */

function obtenerCriteriosSoloIDs(lista) {

    return lista.map(c => {
        let obj = {
            id_sector_asesor: "Venta",
            id_categoria: c.id_categoria,
            id_tipo_inmueble: c.id_tipo_inmueble,
            cant_dormitorios: c.cant_dormitorios,
            cochera: c.cochera,
            id_zona: c.id_zona,
            fecha_criterio: c.fecha_criterio,
            id_propiedad: c.id_propiedad,
            estado_criterio_venta: "Activo",
        };
/*         if (c.id_sector_asesor === "venta") {
            obj.estado_criterio_venta = "Activo";
        } else {
            obj.estado_criterio_alquiler = "Activo";
        } */
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

export { listaCriterioVenta, listaCriterioAlquiler, obtenerCriteriosSoloIDs };
