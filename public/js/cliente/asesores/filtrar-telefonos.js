function filtrarContactos() {
    const buscarInput = document.getElementById('buscarInput');
    const busqueda = buscarInput ? buscarInput.value.toLowerCase().trim() : '';
    const contactos = document.querySelectorAll('.contacto');
    const noResults = document.getElementById('noResults');
    let hasResults = false;

    // Si no hay texto de búsqueda, mostrar todos los contactos
    if (busqueda === '') {
        contactos.forEach(contacto => {
            contacto.style.display = 'block';
        });
        noResults.style.display = 'none';
        return;
    }

    contactos.forEach(contacto => {
        // Obtener el texto del nombre y teléfono
        console.log('contacto', contacto);
        
        const nombreElement = contacto.querySelector('.col-11 strong');
        //console.log('esto esta riciviendo nombre', nombreElement);
        const telefonoElement = contacto.querySelector('small strong');
        //console.log('esto esta riciviendo telefono', telefonoElement);
        const potabilidadElement = contacto.querySelector('.col-1 i');
        //console.log('esto esta riciviendo potabilidad', potabilidadElement);
        const nombre = nombreElement ? nombreElement.textContent.toLowerCase().trim() : '';
        let telefono = telefonoElement ? telefonoElement.textContent.trim() : '';
        
        // Limpiar el teléfono: quitar espacios, guiones, paréntesis y el ícono de WhatsApp
        const telefonoLimpio = telefono
            .replace(/[^0-9+]/g, '') // Mantener solo números y el signo +
            .replace(/^\+?54/, '')   // Remover código de país si existe
            .replace(/^9?0?/, '');    // Remover 9 o 0 inicial si existe
        
        // Limpiar la búsqueda: mantener solo números si es un número
        const busquedaLimpia = busqueda.replace(/[^0-9]/g, '');
        
        // Verificar coincidencia en nombre o teléfono
        const nombreCoincide = nombre.includes(busqueda);
        const telefonoCoincide = busquedaLimpia !== '' && telefonoLimpio.includes(busquedaLimpia);
        
        if (nombreCoincide || telefonoCoincide) {
            contacto.style.display = 'block';
            hasResults = true;
        } else {
            contacto.style.display = 'none';
        }
    });

    // Mostrar/ocultar mensaje de "no hay resultados"
    noResults.style.display = hasResults ? 'none' : 'block';
}

// Agregar evento al campo de búsqueda cuando la página esté cargada
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscarInput');
    
    if (buscarInput) {
        buscarInput.addEventListener('input', filtrarContactos);
        
        // Opcional: limpiar filtro al hacer clic en el input
        buscarInput.addEventListener('focus', function() {
            if (this.value === '') {
                filtrarContactos(); // Resetear la vista
            }
        });
    }
});