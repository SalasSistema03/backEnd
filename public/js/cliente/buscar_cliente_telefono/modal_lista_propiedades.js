import { recibirPropiedad } from './asignar_propiedades.js';
import { getBaseUrl, getData } from '../../core/ajax.js';
import { mostrarAlertaError } from '../../core/utils.js';

const el = document.getElementById('datos-cliente');
const telefonoCliente = el.dataset.telefono;

const url_obtenerPropiedades = `${getBaseUrl()}/propiedades/search`;
const url_obtenerPropiedadesasignadas = `${getBaseUrl()}/cliente/${telefonoCliente}`;

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
        //const sector_asesor = "Venta";

        /*     if (sector_asesor === '') {
                mostrarAlertaError('Debe seleccionar un SECTOR de Venta o Alquiler');
                return;
            } */

        const params = [];
        if (codigo) params.push(`codigo=${encodeURIComponent(codigo)}`);
        if (calle) params.push(`calle=${encodeURIComponent(calle)}`);
        // if (sector_asesor) params.push(`sector_asesor=${encodeURIComponent(sector_asesor)}`); // <-- AGREGADO

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
    tbody.addEventListener('click', async e => {
        if (e.target.classList.contains('btn-agregar')) {
            const prop = JSON.parse(e.target.getAttribute('data-prop'));
            prop.tipo_consulta = "Venta";

            const existe = await obetnerPropiedadesAsignadas(prop);
            if (existe) {
                return;
            }

            //ACA ES LA VALDACION PARA QUE NO SE REPITAN LAS PROPIEDADES

            //Esta funcion se define en asignarPropiedades.js: y tiene como objetivo pasar el bojeto entero
            recibirPropiedad(prop);
            // 🔽 Blanquear inputs y tabla
            inputCodigo.value = '';
            inputCalle.value = '';
            tbody.innerHTML = '';
        }
    });
});


// Esta funcion OBTIENE las propiedades que ya estan asigandas al ciente
// luego VERIFICA si la propiedas seleccionada ya exite
// TRUE si existe, FALSE si no existe
async function obetnerPropiedadesAsignadas(propiedad) {
    // Llamamos a getData con la URL
    const resultado = await getData(url_obtenerPropiedadesasignadas);
    const propiedades_venta_asignadas = [];

    // Si hubo un error en la respuesta
    if (!resultado.ok) {
        mostrarAlertaError('Error al obtener propiedades asignadas.');
        return;
    }

    // Si no hay datos
    if (!resultado.data || resultado.data.length === 0) {
        mostrarAlertaError('No se encontraron propiedades asignadas al cliente.');
        return;
    }

    propiedades_venta_asignadas.push(...resultado.data.cliente.consulta_prop_venta);



    const codigo = propiedad.cod_venta;


    let existe = false;

    for (let i = 0; i < propiedades_venta_asignadas.length; i++) {
        const propAsignada = propiedades_venta_asignadas[i];

        if (propAsignada.propiedad && propAsignada.propiedad.cod_venta === codigo && propAsignada.estado_consulta_venta === 'Activo') {
            existe = true;
            break;
        }


    }

    if (existe) {
        mostrarAlertaError('Esta propiedad ya esta asignada y activa, no se puede repetir - ' + "CODIGO VENTA:" + codigo);
        return true;
    }

    return false



}