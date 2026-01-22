<div class="modal fade" id="modalEditarConsorcio" tabindex="-1" aria-labelledby="modalEditarConsorcioLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarConsorcioLabel">Editar Consorcio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('exp_consorcio.actualizar') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_nombre" class="form-label">Nombre Consorcio</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre">
                        </div>
                        <div class="col-md-6">
                            <label for="search-calle-edit" class="form-label">Calle</label>
                            <input type="text" id="search-calle-edit" class="form-control"
                                placeholder="Buscar calle...">
                            <input type="hidden" id="calle_id_edit" name="calle">
                            <div id="search-results-edit" class="list-group mt-2"
                                style="position: absolute; z-index: 1000;"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_altura" class="form-label">Altura</label>
                            <input type="number" class="form-control" id="edit_altura" name="altura">
                        </div>
                        <!--Ordenar por nombre asc-->
                        <div class="col-md-6">
                            <label for="administrador-edit" class="form-label">Administrador</label>
                            <select name="administra" id="administrador-edit" class="form-select">
                                @foreach ($administradores as $administrador)
                                    <option value="{{ $administrador->id }}">{{ $administrador->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
