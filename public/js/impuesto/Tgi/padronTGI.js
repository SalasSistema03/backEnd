import { pregunaConfirmacion } from '../../core/utils.js';

document.addEventListener('DOMContentLoaded', function () {
    // === Dropdown con checkboxes ===
    opcionesCheckboxes();

    // === Modal de edición ===
    modificarRegistro();

    // === Alerta al actualizar padrón ===
    alertaMensajeActualizar();
});


//Modal de modificación de registro
function modificarRegistro() {
    const modalEditar = document.getElementById('modalEditar');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        // Mapea los atributos del botón al formulario del modal
        const fields = [
            'id', 'folio', 'calle', 'partida',
            'clave', 'estado', 'administra'
        ];

        fields.forEach(field => {
            const input = document.getElementById(`edit-${field}`);
            const value = button.getAttribute(`data-${field}`);
            if (input) input.value = value;
        });
    });
}


//Función para seleccionar o deseleccionar todos los checkboxes
function opcionesCheckboxes() {
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    // Evita que el dropdown se cierre al hacer clic dentro del menú
    dropdownMenu.addEventListener('click', function (e) {
        e.stopImmediatePropagation(); // Previene el cierre automático de Bootstrap
    });

    // Cierra el dropdown si se hace clic fuera del menú y del botón
    document.addEventListener('click', function (e) {
        const clickedInsideMenu = dropdownMenu.contains(e.target);
        const clickedToggle = dropdownToggle.contains(e.target);

        if (!clickedInsideMenu && !clickedToggle) {
            const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownToggle);
            dropdownInstance?.hide(); // Usa el operador opcional para mayor seguridad
        }
    });
}


function alertaMensajeActualizar() {
    const btnActualizar = document.getElementById('btnActualizarPadron');

    btnActualizar.addEventListener('click', function (e) {
        e.preventDefault(); // Evita que el enlace se ejecute automáticamente

        pregunaConfirmacion('¿Estás seguro de que querés actualizar el padrón?', 'Confirmar acción')
            .then((result) => {
                if (result.isConfirmed) {
                    window.location.href = btnActualizar.href; // Redirige manualmente
                }
            });
    });
}