import { postData, getBaseUrl, getData } from '../../core/ajax.js';
import { mostrarAlertaError } from '../../core/utils.js';

//Se obtiene el ID del cliente desde el div con el atributo data-id
const el = document.getElementById('datos-cliente');

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnBuscarCliente');
    const telefonoInput = document.getElementById('telefono');

    async function buscarCliente() {
        const telefono = telefonoInput.value.trim();
        
        if (telefono) {
            const url = `${getBaseUrl()}/cliente/${telefono}`;

            const response = await getData(url);

            if (response.status === 404) {
                mostrarAlertaError('No se encontró ningún cliente con ese número de teléfono.');
                return;
            }

            if (response.status === 200) {
                window.location.href = `${getBaseUrl()}/cliente/${telefono}`;
                return;
            }
        } else {
            mostrarAlertaError('Por favor, ingresa un número de teléfono válido.');
        }
    }

    btn.addEventListener('click', buscarCliente);

    telefonoInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscarCliente();
        }
    });
});


/* Se habilita o no el campo nombre de la inmobiliaria */
/* document.getElementById('pertenece_a_inmobiliaria').addEventListener('change', function () {
    const valorSeleccionado = this.value;
    const campoNombre = document.getElementById('nombre_pertenece_a_inmobiliaria_buscar');

    if (valorSeleccionado === 'S') {
        campoNombre.style.display = 'block';
    } else {
        campoNombre.style.display = 'none';
        
    }
}); */