// importa helpers y la función de guardar
import { getData, getBaseUrl } from '../../../core/ajax.js';
import { guardarDatosExpensas } from './guardarDatosExpensas.js';
import { mostrarAlertaError, mostrarError } from '../../../core/utils.js';

async function buscarfolio() {
    const folio = document.getElementById('folio').value || '-';
    const empresa = document.getElementById('empresa').value || '-';
    const edificio = document.getElementById('edificio').value || '-';
    const administrador = document.getElementById('administrador').value || '-';

    const titulos_broches = document.getElementById('titulos_broches');
    const contenedorUnidades = document.getElementById('broches');
    titulos_broches.innerHTML = '';
    contenedorUnidades.innerHTML = '';




    console.log(folio, empresa, edificio, administrador);

    const baseUrl = getBaseUrl();
    const url = `${baseUrl}/exp-broche-expensas/buscar/${folio}/${empresa}/${edificio}/${administrador}`;

    const { ok, data } = await getData(url);

    if (ok && data?.success) {
        if (Array.isArray(data.data) && data.data.length > 0) {
            const tabla = document.createElement('table');
            tabla.classList.add('table', 'table-striped', 'table-hover', 'table-sm', 'align-middle', 'text-center');

            tabla.innerHTML = `
                <thead> 
                    <tr class="table_expensas_title sticky-top pt-0">
                        <th>Folio</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Consorcio</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th>Ex.ord.</th>
                        <th>Ord.</th>
                        <th>Total</th>
                        <th>Periodo</th>
                        <th>Año</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;

            const tbody = tabla.querySelector('tbody'); // 👈 esta línea faltaba

            data.data.forEach((unidad, index) => {
                const fila = document.createElement('tr');
                fila.classList.add('p-0', 'm-0', 'table_expensas');

                // si está inactivo, pintamos la fila 
                if (unidad.estado == "Inactivo") {
                    fila.classList.add('fila-inactiva');
                }

                    fila.innerHTML = `
                    <td class="p-1"><p class="small">${unidad.folio}</p></td>
                    <td class="p-1"><p class="small" name="tipo_${index}">${unidad.tipo}</p></td>
                    <td class="p-1 text-truncate" style="max-width: 120px;" title="${unidad.direccion_edificio} - ${unidad.altura_edificio}">
                        <p class="small">${unidad.direccion_edificio} - ${unidad.altura_edificio}</p>
                    </td>
                    <td class="p-1 text-truncate" style="max-width: 120px;" title="${unidad.nombre_consorcio}">
                        <p class="small">${unidad.nombre_consorcio}</p>
                    </td>
                    <td class="p-1">
                        ${unidad.estado === 'Activo'
                            ? '<p class="badge bg-success btn-xs">Activo</p>'
                            : '<p class="badge bg-secondary btn-xs">Inactivo</p>'}
                    </td>
                    <td class="p-1">
                        <input type="date" name="fecha_vencimiento_${index}" class="form-control input-xs">
                    </td>
                    <td class="p-1">
                        <input type="number" name="importe_extraordinaria_${index}" class="form-control input-xs" placeholder="0.00" step="0.01" oninput="calcularTotal(${index})">
                    </td>
                    <td class="p-1">
                        <input type="number" name="importe_ordinaria_${index}" class="form-control input-xs" placeholder="0.00" step="0.01" oninput="calcularTotal(${index})">
                    </td>
                    <td class="p-1">
                        <input type="number" name="total_${index}" class="form-control input-xs" placeholder="0.00" step="0.01" readonly>
                    </td>
                    <td class="p-1">
                        <input type="number" name="periodo_${index}" class="form-control input-xs" style="width: 40px" >
                    </td>
                    <td class="p-1">
                        <input type="number" name="anio_${index}" class="form-control input-xs" style="width: 60px">
                    </td>
                    <td class="p-1 accion-cell"></td>
                    <input type="hidden" name="id_unidad_${index}" value="${unidad.id_unidad}">
                    <input type="hidden" name="id_administrador_${index}" value="${unidad.id_administrador}">
                `;

                    const accionCell = fila.querySelector('.accion-cell');
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.classList.add('btn', 'btn-primary', 'btn-xs');
                    boton.textContent = 'Guardar';
                    boton.addEventListener('click', () => guardarDatosExpensas(index));
                    accionCell.appendChild(boton); tbody.appendChild(fila);
                });

            contenedorUnidades.appendChild(tabla);
        } else {
            mostrarError('No se encontraron expensas.');
        }
    } else { console.error('Error al buscar el folio:', ok, data?.message); alert('Error al buscar el folio'); }
}

// Exponer solo buscarfolio si quieres llamarlo desde HTML
window.buscarfolio = buscarfolio;
