import { listaCriterioVenta, listaCriterioAlquiler, obtenerCriteriosSoloIDs } from './modal_criterio_busqueda.js';
import { listaPropiedadesVenta, listaPropiedadesAlquiler, obtenerPropiedadesSoloIDs } from './asignar_propiedades.js';
import { postData, getBaseUrl } from '../../core/ajax.js';
import { mostrarExito, mostrarError, mostrarAlertaError } from '../../core/utils.js';

const el = document.getElementById('datos-cliente');
const telefonoCliente = el.dataset.telefono;
const idCliente = el.dataset.idCliente;

const url_guardarCliente = `${getBaseUrl()}/clientes/alquiler/guardarCriteriosYpropiedades`;

const btn_guardar = document.getElementById("guardar");
btn_guardar.addEventListener("click", async function () {
    const propiedadesVenta = listaPropiedadesVenta.length > 0 ? obtenerPropiedadesSoloIDs(listaPropiedadesVenta) : [];
    const propiedadesAlquiler = listaPropiedadesAlquiler.length > 0 ? obtenerPropiedadesSoloIDs(listaPropiedadesAlquiler) : [];
    const criteriosVenta = listaCriterioVenta.length > 0 ? obtenerCriteriosSoloIDs(listaCriterioVenta) : [];
    const criteriosAlquiler = listaCriterioAlquiler.length > 0 ? obtenerCriteriosSoloIDs(listaCriterioAlquiler) : [];


    const payload = {
        propiedades_venta: propiedadesVenta,
        propiedades_alquiler: propiedadesAlquiler,
        criterios_venta: criteriosVenta,
        criterios_alquiler: criteriosAlquiler,
        id_cliente: idCliente,
    };
    //console.log("payloadaaaa", payload);

    const response = await postData(url_guardarCliente, payload);

    if (response.ok && response.data.success) {
        window.location.href = `${getBaseUrl()}/cliente/${telefonoCliente}`;
        //mostrarExito(response.data.message, '¡Cliente guardado!');
    } else {

        if (response.status === 422 && response.data.message.includes('teléfono')) {
            window.location.href = `${getBaseUrl()}/cliente/${telefonoCliente}`;
            return;
        }

        mostrarAlertaError((response.data && response.data.message) || 'Error al guardar', 'Error');
    }
});