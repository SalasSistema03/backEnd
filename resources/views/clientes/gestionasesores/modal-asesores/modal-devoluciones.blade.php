<div class="modal fade" id="modal-devolucion" tabindex="-1" aria-labelledby="modal-devolucionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-devolucionLabel">Devolución</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-devolucion"
                    action="{{ route('clientes.devolver-mensaje', $criterio->id_criterio_venta) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id_mensaje" id="id_mensaje">
                    <input type="hidden" name="tipo" id="tipo">
                    <div class="mb-3">
                        <label for="" class="form-label">Devolución</label>
                        <textarea name="devolucion" id="" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Devolver</button> --}}
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-devolucion');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const url = form.action;
                const idCriterio = document.getElementById('input-id-criterio')
                .value; // ya existe en tu HTML

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text)
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        $('#modal-devolucion').modal('hide');
                        form.querySelector('textarea[name="devolucion"]').value = '';
                        mostrarEstado(idCriterio);
                    })
                    .catch(error => {
                        // Mostrar el error real para depuración
                        alert('Hubo un error al guardar la devolución:\n' + error.message);
                    });
            });
        }
    });
</script>
