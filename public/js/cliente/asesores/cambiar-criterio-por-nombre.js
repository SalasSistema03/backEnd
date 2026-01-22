
function mostrarNombreCliente(nombre, telefono = '', clienteId = '') {
    //console.log('mostrarNombreCliente - Nombre:', nombre, 'Teléfono:', telefono, 'Cliente ID:', clienteId);
    
    // Obtener elementos con IDs dinámicos basados en el ID del cliente
    const criterioDefault = document.getElementById(`criterio-default-${clienteId}`);
    //console.log('ESTTTTTTTTEEEEEE:', criterioDefault);
    //console.log('ESTTEEE NOMBRE:', nombre);
    const tipoInmuebleElement = document.getElementById(`tipo-inmueble-seleccionado-${clienteId}`);
    const agregarNuevoCriterio = document.getElementById(`agregar-nuevo-criterio-${clienteId}`);
    const criterioDefaultDiv = document.getElementById(`criterio-default-div`);
    
    //console.log('Elementos encontrados:', {criterioDefault, tipoInmuebleElement, agregarNuevoCriterio});
    
    // Actualizar el teléfono en el botón
    if (telefono && agregarNuevoCriterio) {
        //console.log('Actualizando teléfono del botón a:', telefono);
        agregarNuevoCriterio.setAttribute('data-telefono', telefono);
        // Verificar que se actualizó correctamente
        //console.log('Teléfono actualizado en el botón:', agregarNuevoCriterio.getAttribute('data-telefono'));
    }

    if (nombre) {
        criterioDefaultDiv.style.display = 'none';
        //OCULTA EL CRIERIO DEFAULT
        criterioDefault.style.display = 'none';
        //MUESTRA EL TIPO DE INMUEBLE SELECCIONADO
        tipoInmuebleElement.textContent = nombre.toUpperCase();
        tipoInmuebleElement.style.display = 'inline-block';

        agregarNuevoCriterio.style.display = 'inline-block';
        
        //OCULTA EL INPUT DE CONVERSAICON
        const conversacionContainer = document.getElementById('input-conversacion');
        if (conversacionContainer) {
            conversacionContainer.style.display = 'none';
        }   
    }

    if (nombre) {
        //MUESTRA EL CRIERIO DEFAULT
        const criterioDefaultConversacion = document.getElementById('criterio-default-conversacion');
        criterioDefaultConversacion.style.display = 'block';
        //OCULTA EL TIPO DE INMUEBLE SELECCIONADO
        const tipoInmuebleElementConversacion = document.getElementById('tipo-inmueble-seleccionado-conversacion');
        tipoInmuebleElementConversacion.style.display = 'none';
        //LIMPIA LA CONVERSAICON
        const messagesList = document.querySelector('#conversacion-container ul');
        const codigoList = document.querySelector('#codigos-container ul');
        if (messagesList) {
            messagesList.innerHTML = '';
        }
        if (codigoList) {
            codigoList.innerHTML = '';
        }
    }
}
