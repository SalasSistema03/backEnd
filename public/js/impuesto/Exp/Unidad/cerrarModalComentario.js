function cerrarModalComentario() {
            const modalComentario = bootstrap.Modal.getInstance(document.getElementById('modalComentario'));
            if (modalComentario) {
                modalComentario.hide();
            }
            // abrir el modal de editar unidad
            setTimeout(function() {
                const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarUnidad'));
                modalEditar.show();
            }, 300);
        }