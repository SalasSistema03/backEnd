import { guardarCriterios, obetnerCriteriosAsignados } from './modal_criterio_busqueda.js';
import { mostrarAlertaError } from '../../core/utils.js'; 
import { listaCriterioVenta } from './modal_criterio_busqueda.js';

const listaPropiedadesVenta = [];
const listaPropiedadesAlquiler = [];

//Se obtiene el ID del cliente desde el div con el atributo data-id
const el = document.getElementById('datos-cliente');

const idCliente = el.dataset.idCliente;
const idAsesor = el.dataset.idAsesor;
const idAsesorAlquiler = el.dataset.idAsesorAlquiler;
const idUsuario = el.dataset.usuarioId;


export function recibirPropiedad(propiedad) {
    //const sector_asesor = document.getElementById("sector_asesor").value;
    //const asesor = document.getElementById("id_asesor_cliente").value;
/*     if (!sector_asesor) {
        alert('Debe seleccionar una SECTOR de Venta o Alquiler');
        return;
    } */
/*     if (!asesor) {
        alert('Debe seleccionar un ASESOR');
        return;
    } */

    agregarACriterioBusqueda(propiedad);

    listaPropiedadesVenta.push(propiedad);
    agregarFilaTabla('venta', propiedad);

/*     if (sector_asesor === 'venta') {
        listaPropiedadesVenta.push(propiedad);
        agregarFilaTabla('venta', propiedad);
        mostrarPestania('venta-tab'); // Cambia a la pestaña de ventas
    } else if (sector_asesor === 'alquiler') {
        listaPropiedadesAlquiler.push(propiedad);
       agregarFilaTabla('alquiler', propiedad);
        mostrarPestania('alquiler-tab'); // Cambia a la pestaña de alquileres
    } */

    // Cerrar el modal
    const modalElement = document.getElementById('modal_propiedades');
    const modalBootstrap = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalBootstrap.hide();
}



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

        usuaro_id: idUsuario,

        fecha_criterio: new Date().toISOString().slice(0, 10)

    }
    guardarCriterios(criterios);
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
    let fecha_criterio = new Date().toISOString().slice(0, 10);


    nuevaFila.innerHTML = `
        <td>${codigo}</td>
        <td>${direccion}</td>
        <td>Activo</td>
        <td>${fecha_criterio}</td>
        <td><button type="button" class="btn btn-sm btn-danger btn-eliminar-propiedad" data-id="${datos.id}" data-tipo="${tipo}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg>
        </button></td>
    `;
    tbody.appendChild(nuevaFila);
}


function mostrarPestania(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
}



function obtenerPropiedadesSoloIDs(listaPropiedades) {
    return listaPropiedades.map(propiedad => {

        let objeto = {
            tipo_consulta: propiedad.tipo_consulta,
            id_propiedad: propiedad.id,
            id_cliente: idCliente,
            usuario_id: idUsuario
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
document.addEventListener('click', function(e) {
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
