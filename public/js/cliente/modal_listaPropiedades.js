import { recibirPropiedad } from './asignarPropiedades.js';
import { getBaseUrl } from '../core/ajax.js';

const url_obtenerPropiedades = `${getBaseUrl()}/propiedades/search`;

document.addEventListener('DOMContentLoaded', () => {
    const btnBuscar = document.getElementById('btnBuscarPropiedades');
    const inputCodigo = document.getElementById('inputCodigoPropiedad');
    const inputCalle = document.getElementById('inputCallePropiedad');
    const tbody = document.getElementById('tbodyFiltraPropiedades');


    // Permitir búsqueda con Enter
    [inputCodigo, inputCalle].forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnBuscar.click();
            }
        });
    });

    btnBuscar?.addEventListener('click', async () => {
        const codigo = inputCodigo.value.trim();
        const calle = inputCalle.value.trim();
        // const sector_asesor = document.getElementById("sector_asesor").value;
        const params = [];
        if (codigo) params.push(`codigo=${encodeURIComponent(codigo)}`);
        if (calle) params.push(`calle=${encodeURIComponent(calle)}`);
        // <-- AGREGADO

        const url = params.length
            ? `${url_obtenerPropiedades}?${params.join('&')}`
            : url_obtenerPropiedades;
        const res = await fetch(url);
        const props = await res.json();

        if (!props.length || !props.some(p => p.cod_venta !== null)) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron propiedades</td></tr>';
        } else {
            const propsFiltradas = props.filter(p => p.cod_venta !== null);

            tbody.innerHTML = propsFiltradas.map(p => `
        <tr>
            <td>${p.cod_venta ?? ''} - ${p.cod_alquiler ?? 'No alquila'}</td>
            <td>${p.calle || ''}</td>
            <td>${p.zona || ''}</td>
            <td>
                <button 
                    type="button" 
                    class="btn btn-outline-primary btn-agregar" 
                    data-prop='${JSON.stringify(p)}'>
                    Agregar
                </button>
            </td>
        </tr>
    `).join('');
        }

    });

    // Delegar evento click para botones Agregar
    tbody.addEventListener('click', e => {
        if (e.target.classList.contains('btn-agregar')) {
            const prop = JSON.parse(e.target.getAttribute('data-prop'));
            prop.tipo_consulta = "Venta";
            recibirPropiedad(prop); //Esta funcion se define en asignarPropiedades.js: y tiene como objetivo pasar el bojeto entero

            // 🔽 Blanquear inputs y tabla
            inputCodigo.value = '';
            inputCalle.value = '';
            tbody.innerHTML = '';
        }
    });
});
