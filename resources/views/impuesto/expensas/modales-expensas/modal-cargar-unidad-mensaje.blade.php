<div class="modal fade" id="modalComentario" tabindex="-1" aria-labelledby="modalComentarioLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComentarioLabel">Comentario</h5>
                <button type="button" class="btn-close" onclick="cerrarModalComentario()"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="comentarioTextarea" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cerrarModalComentario()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="guardarComentarioYVolver()">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>
