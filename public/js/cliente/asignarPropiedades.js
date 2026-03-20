
import { guardarCriterios } from './criterioBusquedaCliente.js';
import { mostrarAlertaError } from '../core/utils.js';

const listaPropiedadesVenta = [];
const listaPropiedadesAlquiler = [];

export function recibirPropiedad(propiedad) {
    //const sector_asesor = document.getElementById("sector_asesor").value;
    const asesor = document.getElementById("id_asesor_cliente").value;
    //if (!sector_asesor) {
     //   alert('Debe seleccionar una SECTOR de Venta o Alquiler');
     //   return;
    //}
    if (!asesor) {
        alert('Debe seleccionar un ASESOR');
        return;
    }

    for (let prop of listaPropiedadesVenta) {
        if (prop.id === propiedad.id) {
            mostrarAlertaError("Ya ingresaste esta propiedad");
            return; // ⛔ Termina la función si ya existe
        }
    }


    agregarACriterioBusqueda(propiedad);
   // if (sector_asesor === 'venta') {
    propiedad.estado_consulta_venta = "activo";
    listaPropiedadesVenta.push(propiedad);
    agregarFilaTabla('venta', propiedad);
    //mostrarPestania('ventaProp-tab'); // Cambia a la pestaña de ventas
/*     } else if (sector_asesor === 'alquiler') {
        propiedad.estado_consulta_alquiler = "activo";
        listaPropiedadesAlquiler.push(propiedad);
        agregarFilaTabla('alquiler', propiedad);
        mostrarPestania('alquilerProp-tab'); // Cambia a la pestaña de alquileres
    } */

    // Cerrar el modal
    const modalElement = document.getElementById('exampleModal');
    const modalBootstrap = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalBootstrap.hide();
}

function agregarFilaTabla(tipo, datos) {
    const tbody = document.querySelector(`#tabla_propiedad_${tipo} tbody`);
    const nuevaFila = document.createElement('tr');
    let codigo = '';
    if (tipo === 'venta') {
        // Puede ser cod_venta, cod_venta_propiedad, etc. Ajusta si tu backend usa otro nombre.
        codigo = datos.cod_venta !== undefined ? datos.cod_venta : '';
    } else if (tipo === 'alquiler') {
        codigo = datos.cod_alquiler !== undefined ? datos.cod_alquiler : '';
    }
    let direccion = (datos.calle || '') + ' ' + (datos.numero_calle || '');

    nuevaFila.innerHTML = `
        <td>${codigo}</td>
        <td>${direccion}</td>
        <td><button type="button" class="btn btn-sm btn-danger btn-eliminar-propiedad" data-id="${datos.id}" data-tipo="${tipo}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg>
        </button></td>
    `;
    tbody.appendChild(nuevaFila);
}

/* 
function mostrarPestania(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
} */



//Esta funcion crea un objeto de tipo criterio busqueda par agregarlo a la lista que corresponda
function agregarACriterioBusqueda(propiedad) {
    const criterios = {
        id_sector_asesor: "Venta",
        sector_asesor_texto: "Venta",

        id_tipo_inmueble: propiedad.id_inmueble,
        tipo_inmueble_texto: propiedad.inmueble,

        cant_dormitorios: propiedad.cantidad_dormitorios,

        cochera: propiedad.cochera,
        cochera_texto: propiedad.cochera,

        id_zona: propiedad.id_zona,
        zona_texto: propiedad.zona,

        id_propiedad: propiedad.id,

        fecha_criterio: new Date().toISOString().slice(0, 10)

    }
    guardarCriterios(criterios);
}



function obtenerPropiedadesSoloIDs(listaPropiedades) {
    return listaPropiedades.map(propiedad => {

        let objeto = {
            tipo_consulta: propiedad.tipo_consulta,
            id_propiedad: propiedad.id,
            id_tipo_inmueble: propiedad.id_inmueble,
            cant_dormitorios: propiedad.cantidad_dormitorios,
        };

        if (propiedad.tipo_consulta === "venta") {
            objeto.estado_consulta_venta = "Activo";
        } else if (propiedad.tipo_consulta === "alquiler") {
            objeto.estado_consulta_alquiler = "Activo";
        }
        return objeto;
    });
}



// Lógica para eliminar criterio tanto del array como de la tabla
document.addEventListener('click', function (e) {
    // Permitir click en botón o en cualquier elemento interno (SVG, path)
    const btn = e.target.closest('.btn-eliminar-propiedad');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const tipo = btn.dataset.tipo;
    const lista = tipo === 'venta' ? listaPropiedadesVenta : listaPropiedadesAlquiler;
    const index = lista.findIndex(c => c.id === id);
    if (index > -1) lista.splice(index, 1);
    // Remover fila de la tabla
    btn.closest('tr').remove();
});


export { listaPropiedadesVenta, listaPropiedadesAlquiler, obtenerPropiedadesSoloIDs };
