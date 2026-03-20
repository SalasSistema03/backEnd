import { pregunaConfirmacion, mostrarError } from '../../core/utils.js';
import { getBaseUrl } from '../../core/ajax.js';
import { getData } from '../../core/ajax.js';

// Mueve el event listener fuera de la función
document.getElementById('btnBuscar').addEventListener('click', async function (e) {
    e.preventDefault();
    await buscarProveedor();
});


// Función separada para la búsqueda
async function buscarProveedor() {
    const baseUrl = getBaseUrl();
    const nombreBuscado = document.getElementById('nombreBuscado').value;

    if (nombreBuscado === '') {
        mostrarError('El campo nombre es obligatorio.');
        return;
    }

    const url = `${baseUrl}/exp-proveedores/${nombreBuscado}`;
    const resultadosDiv = document.getElementById('resultados');

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
                            <th>CUIT</th>
                            <th>Rubro</th>
                            <th>Contacto</th>
                            <th>Pagina Web</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;

            // Agregar filas
            response.data.forEach(element => {
                tablaHTML += `
                    <tr style="font-size: 13px;">
    <td>${element.nombre || ''}</td>
    <td>${element.cuit || ''}</td>
    <td>${element.rubro || ''}</td>
    <td>${element.contacto || ''}</td>
    <td>${element.pagina_web || ''}</td>
    <td>
        <button class="btn btn-sm btn-primary" 
                onclick="seleccionarProveedor(${JSON.stringify(element).replace(/"/g, '&quot;')})">
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

// Función para manejar la selección de un proveedor
window.seleccionarProveedor = function (proveedor) {
    console.log(proveedor);
    // Llenar los campos del formulario con los datos del proveedor
    document.querySelector('input[name="id_proveedor"]').value = proveedor.id || '';
    document.querySelector('input[name="nombre"]').value = proveedor.nombre || '';
    document.querySelector('input[name="rubro"]').value = proveedor.rubro || '';
    document.querySelector('input[name="contacto"]').value = proveedor.contacto || '';
    document.querySelector('input[name="pagina_web"]').value = proveedor.pagina_web || '';
    document.querySelector('input[name="direccion"]').value = proveedor.direccion || '';
    document.querySelector('input[name="altura"]').value = proveedor.altura || '';

    if (proveedor.cuit) {
        const [p, n, d] = proveedor.cuit.split('-');
        document.querySelector('input[name="cuit_prefijo"]').value = p || '';
        document.querySelector('input[name="cuit_numero"]').value = n || '';
        document.querySelector('input[name="cuit_dv"]').value = d || '';
    } else {
        document.querySelector('input[name="cuit_prefijo"]').value = '';
        document.querySelector('input[name="cuit_numero"]').value = '';
        document.querySelector('input[name="cuit_dv"]').value = '';
    }


    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModal'));
    modal.hide();
};

// Opcional: Permitir búsqueda al presionar Enter
document.getElementById('nombreBuscado').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('btnBuscar').click();
    }
});
