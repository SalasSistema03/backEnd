import { pregunaConfirmacion, mostrarError } from '../../core/utils.js';
import { getBaseUrl } from '../../core/ajax.js';
import { getData } from '../../core/ajax.js';


document.getElementById('btnBuscarCalle').addEventListener('click', async function(e) {
    e.preventDefault();
    await buscarCalle();
});

async function buscarCalle() {
    const baseUrl = getBaseUrl();
    const nombreBuscado = document.getElementById('nombreBuscadoCalle').value;
    console.log(nombreBuscado);

    if (nombreBuscado === '') {
        mostrarError('El campo calle es obligatorio.');
        return;
    }

    const url = `${baseUrl}/exp-calle/${nombreBuscado}`;
    const resultadosDiv = document.getElementById('resultadosCalle');
    
    // Limpiar resultados anteriores
    resultadosDiv.innerHTML = '';

    try {
        const response = await getData(url);
        
        if (response.ok && response.data && response.data.length > 0) {
            console.log('Resultados:', response.data);
            
            // Crear tabla una sola vez
            let tablaHTML = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            // Agregar filas
            response.data.forEach(element => {
             
                tablaHTML += `
                    <tr>
                        <td>${element.calle || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="seleccionarCalle(${JSON.stringify(element).replace(/"/g, '&quot;')})">
                                Seleccionar
                            </button>
                        </td>
                    </tr>`;
            });
            
            tablaHTML += `</tbody></table>`;
            resultadosDiv.innerHTML = tablaHTML;
            
        } else {
            resultadosDiv.innerHTML = '<div class="alert alert-info">No se encontraron resultados</div>';
        }
    } catch (error) {
        console.error('Error al buscar proveedores:', error);
        mostrarError('Error al realizar la búsqueda');
    }
}

// Al final del archivo Calles.js, reemplaza la función actual con:
window.seleccionarCalle = function(calle) {
    console.log('Calle seleccionada:', calle);
    document.getElementById('direccion').value = calle.calle || '';

    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('ModalCalle'));
    if (modal) {
        modal.hide();
    }
}

// Opcional: Permitir búsqueda al presionar Enter
document.getElementById('nombreBuscadoCalle').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('btnBuscarCalle').click();
    }
});