import { pregunaConfirmacion, mostrarError, mostrarExito } from '../../core/utils.js';

import { getBaseUrl } from '../../core/ajax.js';
import { getData } from '../../core/ajax.js';

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('codigo_barras');
    if (input) input.focus();

    calcularBroches();

    totalMontoTgiCargados();

    buscarTGIManual();

    guardarNumBroches();

    guardarNumBrocheSalas();
});

async function calcularBroches() {
    const baseUrl = getBaseUrl();

    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;
    const btnCalcular = document.getElementById('btn_calculaBroches');
    const contenedor = document.getElementById('contenedor_resultado_broche');

    btnCalcular.addEventListener('click', async function (e) {
        e.preventDefault();

        const cant_broches = document.getElementById('cant_broches').value;
        const url = `${baseUrl}/mostrar_broches/api/${anio}/${mes}/${cant_broches}`;

        try {
            const response = await getData(url);

            // Limpiar contenido anterior
            contenedor.innerHTML = "";

            const broches = response.data.broches;

            if (!broches || broches.length === 0) {
                contenedor.innerHTML = "<p>No se generaron broches.</p>";
                return;
            }

            // Crear lista con los resultados
            let html = "<ul class='list-group'>";

            for (let i = 0; i < broches.length; i++) {
                const numero = i + 1;
                const importe = broches[i].importe.toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const cantidadItems = broches[i].items.length;

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>Broche ${numero}:</strong> $${importe}
                            <small class="text-muted">(${cantidadItems} registros)</small>
                        </div>
                    </li> 
                `;
            }

            html += "</ul>";

            contenedor.innerHTML = html;

        }
        catch (error) {
            console.error('Error al obtener el monto total:', error);
        }
    });
}

async function guardarNumBroches() {
    const baseUrl = getBaseUrl();
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;
    const btnCalcular = document.getElementById('btn_guardar_broches_tgi');

    btnCalcular.addEventListener('click', async function (e) {
        e.preventDefault();

        const cant_broches = document.getElementById('cant_broches').value;

        // 🔴 Validación: campo vacío o cero
        if (!cant_broches) {
            mostrarError('Debes ingresar una cantidad válida de broches.');
            return;
        }

        const url = `${baseUrl}/guardar_num_broches/api/${anio}/${mes}/${cant_broches}`;

         try {
            const response = await getData(url);
            console.log("response", response.data);

            if (response.data.status === 'success') {
                mostrarExito(response.data.message || 'Los broches se guardaron correctamente.');
            } else {
                mostrarError(response.data.message || 'No se pudo guardar los broches.');
            }

        } catch (error) {
            console.error("Error al guardar broches:", error);
            mostrarError('Error al guardar los broches. JS');
        }
    });
}


// Guardar num_broche de SALAS
async function guardarNumBrocheSalas() {
    const baseUrl = getBaseUrl();
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;
    const url = `${baseUrl}/guardar_num_broche_salas/api/${anio}/${mes}`;
    const btnGuardar = document.getElementById('btn_guardar_broches_salas');
    btnGuardar.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
            const response = await getData(url);
            console.log("response", response.data);
            if (response.status = 'success') {
                mostrarExito('Los broches se guardaron correctamente.');
            }
        } catch (error) {
            mostrarError('Error al guardar los broches de SALAS. JS');
        }
    });
}


async function totalMontoTgiCargados() {
    const baseUrl = getBaseUrl();
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;

    const url = `${baseUrl}/sumar_montos/api/${anio}/${mes}`;

    try {
        const response = await getData(url);
        console.log("  response", response);

        if (response.ok && response.data) {
            document.getElementById('monto_total').innerHTML = `Monto Total: ${response.data.total.total}`;
            document.getElementById('monto_total_salas').innerHTML = `Monto Total: ${response.data.totalSalas}`;
        }
    } catch (error) {
        console.error('Error al obtener el monto total:', error);
        //mostrarError('Error al obtener el monto total.');
    }
}

async function buscarTGIManual() {
    const btnBuscar = document.getElementById('btnBuscar');

    btnBuscar.addEventListener('click', async function (e) {
        e.preventDefault();

        const baseUrl = getBaseUrl();
        const folio = document.getElementById('folio').value;
        const empresa = document.getElementById('empresa').value;

        if (folio === '') {
            mostrarError('El campo folio es obligatorio.');
            return;
        }

        const url = `${baseUrl}/api_padron/obtener/${folio}/${empresa}`;

        try {
            const response = await getData(url);

            if (response.ok && response.data) {
                document.getElementById('partida').value = response.data.partida || '';
                //document.getElementById('clave').value = response.data.clave || '';
                document.getElementById('administra').value = response.data.administra || '';

                // Paso 2: Desactivar los inputs
                document.getElementById('partida').readOnly = true;
                //document.getElementById('clave').readOnly = true;
                document.getElementById('administra').readOnly = true;
            } else if (response.status === 404) {
                mostrarError('No se encontró ningún registro con ese folio y empresa.');
            } else {
                mostrarError('Error inesperado al obtener el registro.');
            }
        } catch (error) {
            console.error('Error al obtener el registro:', error);
            mostrarError('Error inesperado al obtener el registro.');
        }
    });
}
