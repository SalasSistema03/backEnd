function guardarComentarioYVolver() {
            const comentario = document.getElementById('comentarioTextarea').value;

            // Guardar en el campo oculto específico de esta unidad
            const campoOculto = document.querySelector(`.comentario-hidden[data-index="${indiceComentarioActual}"]`);
            if (campoOculto) {
                campoOculto.value = comentario;
            }

            // Actualizar el color del botón
            const botonComentario = document.querySelector(`.btn-comentario[data-index="${indiceComentarioActual}"]`);
            if (botonComentario) {
                if (comentario.trim() !== '') {
                    botonComentario.classList.add('btn-comentario-con-texto');
                } else {
                    botonComentario.classList.remove('btn-comentario-con-texto');
                }
            }
            // cerrar el modal de comentario
            cerrarModalComentario();
        }