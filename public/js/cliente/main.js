/**
 * main.js – clientes
 * ----------------------------------------
 * Archivo principal del sistema cliente. 
 * Inicializa eventos, carga datos y maneja toda la lógica de interacción específica
 * de este sistema, utilizando funciones auxiliares de utils.js y ajax.js.
 *
 * Este archivo se encarga de:
 *  - Consumir los endpoints que trae informacion al ingresar a la pagina, EJ: listado de clientes.
 *  - Actualizar dinámicamente partes de la interfaz.
 *
 * Este archivo debe ser incluido solo en index.blade.php de clientes.
 */

import { listaCriterioVenta, listaCriterioAlquiler, obtenerCriteriosSoloIDs } from './criterioBusquedaCliente.js';
import { listaPropiedadesVenta, listaPropiedadesAlquiler, obtenerPropiedadesSoloIDs } from './asignarPropiedades.js';
import { obtenerDatosCliente } from './guardarCliente.js';
import { validarDatosCliente } from './validaciones.js';
import { mostrarExito, mostrarError, mostrarAlertaError } from '../core/utils.js';
import { postData, getBaseUrl } from '../core/ajax.js';


const url_guardarCliente = `${getBaseUrl()}/clientes/guardar`

// Filtrar select de asesores según sector (venta/alquiler)
/* document.addEventListener('DOMContentLoaded', () => {
    const sectorSelect = document.getElementById('sector_asesor');
    const asesorSelect = document.getElementById('id_asesor_cliente');
    // Clonar opciones originales con atributos data-venta/data-alquiler
    const originalOptions = Array.from(asesorSelect.querySelectorAll('option[data-venta]')).map(opt => opt.cloneNode(true));
    sectorSelect.addEventListener('change', function () {
        // Habilitar select de asesor
        asesorSelect.disabled = false;
        // Resetear select
        asesorSelect.innerHTML = '<option value="" selected disabled>Seleccionar</option>';
        // Repoblar según coincidencia
        originalOptions.forEach(opt => {
            const venta = opt.getAttribute('data-venta');
            const alquiler = opt.getAttribute('data-alquiler');
            if ((this.value === 'venta' && venta === 'S') || (this.value === 'alquiler' && alquiler === 'S')) {
                asesorSelect.appendChild(opt.cloneNode(true));
            }
        });
    });
}); */




/* Boton guardar todo (guarda cliente, criterios y propiedades) */
const btnGuardarTodo = document.getElementById('btn_guardar_todo');
btnGuardarTodo.addEventListener('click', async function () {
    // Realiza la validación de los datos del cliente
    if (!validarDatosCliente()) {
        return; // Si la validación falla, no sigue con el envío de datos
    }
    const datosCliente = obtenerDatosCliente();
    const propiedadesVenta = listaPropiedadesVenta.length > 0 ? obtenerPropiedadesSoloIDs(listaPropiedadesVenta) : [];
    const propiedadesAlquiler = listaPropiedadesAlquiler.length > 0 ? obtenerPropiedadesSoloIDs(listaPropiedadesAlquiler) : [];
    const criteriosVenta = listaCriterioVenta.length > 0 ? obtenerCriteriosSoloIDs(listaCriterioVenta) : [];
    const criteriosAlquiler = listaCriterioAlquiler.length > 0 ? obtenerCriteriosSoloIDs(listaCriterioAlquiler) : [];

    if (criteriosVenta.length === 0) {
        mostrarAlertaError('Debe agregar al menos un criterio de venta antes de guardar.');
        return;
    }

    const payload = {
        cliente: datosCliente,
        propiedades_venta: propiedadesVenta,
        propiedades_alquiler: propiedadesAlquiler,
        criterios_venta: criteriosVenta,
        criterios_alquiler: criteriosAlquiler
    };

    const response = await postData(url_guardarCliente, payload);

   /*  if (response.ok && response.data.success) {
        mostrarExito(response.data.message, '¡Cliente guardado!');
    } else {

        if (response.status === 422 && response.data.message.includes('teléfono')) {
            window.location.href = `${getBaseUrl()}/cliente/${datosCliente.telefono}`;
            return;
        }

        mostrarAlertaError((response.data && response.data.message) || 'Error al guardar', 'Error');
    } */

        if (response.ok && response.data.success) {
    // Mostrar mensaje de éxito (opcional)
    mostrarExito(response.data.message, '¡Cliente guardado!');

    // Redirigir a la página del cliente usando su teléfono
    window.location.href = `${getBaseUrl()}/cliente/${datosCliente.telefono}`;
    return;
    } else {
        if (response.status === 422 && response.data.message.includes('teléfono')) {
            window.location.href = `${getBaseUrl()}/cliente/${datosCliente.telefono}`;
            return;
        }

        mostrarAlertaError((response.data && response.data.message) || 'Error al guardar', 'Error');
    }
});


function mostrarErroresEnFormulario(errors) {
    for (const campo in errors) {
        const mensajes = errors[campo];
        const elemento = document.querySelector(`[name="${campo}"]`);
        if (elemento) {
            elemento.classList.add('is-invalid');
            // Aquí podrías insertar un div con los errores debajo del campo
            let errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.innerText = mensajes.join(', ');
            elemento.parentNode.appendChild(errorDiv);
        }
    }
}
