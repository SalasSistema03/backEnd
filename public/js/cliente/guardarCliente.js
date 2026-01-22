/**
 * guardarCliente.js – clientes
 * ----------------------------------------
 *
 * Este archivo se encarga de:
 *  - Obtener datos de un cliente (Del formulario para cargar un nuevo cliente).
 *  - Guardar un nuevo cliente - consumiendo la ruta POST /clientes/guardar.
 *
 * Este archivo debe ser incluido solo en index.blade.php de clientes.
 */


import { getBaseUrl } from '../core/ajax.js';
import { getData } from '../core/ajax.js';

window.addEventListener('pageshow', function () {
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('change', async function () {
            const telefono = this.value.trim();
            if (!telefono) return;

            const baseUrl = getBaseUrl();
            const url = `${baseUrl}/cliente/${telefono}`;

            const response = await getData(url);

            if (response.ok) {
                window.location.href = url;
            } else if (response.status === 404) {
                console.log('Cliente no encontrado, no se redirige');
            } else {
                console.error('Error inesperado:', response);
            }
        });
    }
});



/* Se habilita o no el campo nombre de la inmobiliaria */
document.getElementById('pertenece_a_inmobiliaria').addEventListener('change', function () {
    const valorSeleccionado = this.value;
    const campoNombre = document.getElementById('nombre_pertenece_a_inmobiliaria');

    if (valorSeleccionado === 'S') {
        campoNombre.style.display = 'block';
    } else {
        campoNombre.style.display = 'none';
    }
});





export function obtenerDatosCliente() {
    const form = document.getElementById('form_cliente');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.id_asesor = parseInt(data.id_asesor, 10);
    data.usuario_id = parseInt(data.usuario_id, 10);
    data.sector_asesor = "venta"; //data.sector_asesor; --- IGNORE ---
    return data;
}

 
