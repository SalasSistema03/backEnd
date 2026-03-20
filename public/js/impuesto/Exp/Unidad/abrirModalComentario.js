function abrirModalComentario(index) {
    indiceComentarioActual = index;

    // Cargar el comentario guardado de esta unidad específica
    const campoOculto = document.querySelector(`.comentario-hidden[data-index="${index}"]`);
    const comentarioGuardado = campoOculto ? campoOculto.value : '';
    document.getElementById('comentarioTextarea').value = comentarioGuardado;

    // Cerrar modal principal
    const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarUnidad'));
    if (modalEditar) {
        modalEditar.hide();
    }

    // Abrir modal comentario
    setTimeout(function () {
        const modalComentario = new bootstrap.Modal(document.getElementById('modalComentario'));
        modalComentario.show();
    }, 300);
}