function agregarNuevoCriterio(clienteId) {
    const boton = document.getElementById(`agregar-nuevo-criterio-${clienteId}`);
    console.log('Botón encontrado:', boton);
    if (!boton) {
        console.error(`No se encontró el botón con id agregar-nuevo-criterio-${clienteId}`);
        return;
    }

    const telefono = boton.getAttribute('data-telefono');
    console.log('Teléfono obtenido del botón:', telefono);

    if (telefono) {
        const url = "{{ url('/cliente') }}/" + telefono;
        console.log('Redirigiendo a:', url);
        window.location.href = url;
    } else {
        console.error('No se encontró el teléfono del cliente en el botón');
        // Mostrar un mensaje al usuario
        alert('No se pudo obtener el teléfono del cliente. Por favor, seleccione un cliente primero.');
    }
}



 // Delegación para seleccionar potabilidad desde el menú
 document.addEventListener('DOMContentLoaded', function() {
    const menu = document.querySelector('.letras-potabilidad');
    if (!menu) return;
    menu.addEventListener('click', function(e) {
        const item = e.target.closest('.dropdown-item');
        if (!item) return;
        const selectedValue = item.getAttribute('data-potabilidad') || '';
        const icon = item.querySelector('i')?.outerHTML || '';
        // Actualizar botón y hidden
        document.getElementById('btnPotDropdownLabel').innerHTML = icon || 'Todos';
        document.getElementById('filtroPotDropdownValue').value = selectedValue;
        // Ejecutar filtro
        if (typeof window.filtrarContactosPotabilidad === 'function') {
            window.filtrarContactosPotabilidad();
        } else if (typeof window.filtrarContactos === 'function') {
            window.filtrarContactos();
        }
    });
});

// Definir función global de filtrado por potabilidad
window.filtrarContactosPotabilidad = function() {
    const filtro = document.getElementById('filtroPotDropdownValue')?.value || '';
    const contactos = document.querySelectorAll('.contacto');
    const noResults = document.getElementById('noResults');
    let hasResults = false;

    contactos.forEach(contacto => {
        // Detectar ícono de potabilidad en cada contacto
        const icon = contacto.querySelector('.col-1 i');
        let pot = '';
        if (icon) {
            const cls = icon.classList;
            if (cls.contains('fa-face-grin-beam')) pot = 'Potable';
            else if (cls.contains('fa-face-grimace')) pot = 'Medio';
            else if (cls.contains('fa-face-angry')) pot = 'No Potable';
        }

        const coincide = (filtro === '' || filtro === pot);
        //si selecciona todos, se habilita el input de buscar y se deshabilita el filtro
        const inputbuscar = document.getElementById('buscarInput');
        const devolucionescheck = document.getElementById('devolucionescheck');
        if (filtro === '') {
            inputbuscar.disabled = false;
            devolucionescheck.disabled = false;
        } else {
            inputbuscar.disabled = true;
            devolucionescheck.disabled = true;
        }

        if (coincide) {
            contacto.style.display = 'block';
            hasResults = true;
        } else {
            contacto.style.display = 'none';
        }
    });

    if (noResults) noResults.style.display = hasResults ? 'none' : 'block';
};


// Script independiente para filtrar contactos por devolución

document.addEventListener('DOMContentLoaded', function() {
    const devolucionesCheckbox = document.getElementById('devolucionescheck');

    if (devolucionesCheckbox) {
        devolucionesCheckbox.addEventListener('change', function() {
            filtrarContactosPorDevolucion();
        });
    }
});

function filtrarContactosPorDevolucion() {
    const devolucionesCheckbox = document.getElementById('devolucionescheck');
    const isChecked = devolucionesCheckbox ? devolucionesCheckbox.checked : false;
    const contactos = document.querySelectorAll('.contacto');
    const noResults = document.getElementById('noResults');
    const inputbuscar = document.getElementById('buscarInput');
    const btn = document.getElementById("btnPotDropdown");
    let hasResults = false;

    contactos.forEach(contacto => {
        const faltaDevolucion = contacto.getAttribute('data-falta-devolucion');
        const tieneDevolucionPendiente = faltaDevolucion === 'true' || faltaDevolucion === '1';

        let mostrarContacto = true;

        if (isChecked) {
            mostrarContacto = tieneDevolucionPendiente;
            inputbuscar.disabled = true;

            // 🔒 Deshabilitar el dropdown (quitar comportamiento Bootstrap)
            btn.removeAttribute("data-bs-toggle");
        } else {
            mostrarContacto = true;
            inputbuscar.disabled = false;


            // ✅ Rehabilitar el dropdown
            btn.setAttribute("data-bs-toggle", "dropdown");
        }

        if (mostrarContacto) {
            contacto.style.display = 'block';
            hasResults = true;
        } else {
            contacto.style.display = 'none';
        }
    });

    if (noResults) {
        noResults.style.display = hasResults ? 'none' : 'block';
    }
}