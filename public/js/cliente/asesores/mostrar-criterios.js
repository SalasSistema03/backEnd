function showChat(chatNumber) {
    
    clienteSeleccionado = chatNumber;

    // Ocultar todos los criterios
    document.querySelectorAll('.criterio-chat').forEach(chat => {
        chat.classList.remove('active-chat');
        chat.classList.add('hidden-chat');
    });

    // Mostrar criterios del cliente seleccionado
    const chatElement = document.getElementById(`chat${chatNumber}`);
    if (chatElement) {
        chatElement.classList.remove('hidden-chat');
        chatElement.classList.add('active-chat');
        
    }

    // Marcar contacto activo
    document.querySelectorAll('.contacto').forEach(contact => {
        contact.classList.remove('active');
    });
    event.currentTarget.classList.add('active');

    /* document.getElementById('input-id-cliente').value = chatNumber; */
}