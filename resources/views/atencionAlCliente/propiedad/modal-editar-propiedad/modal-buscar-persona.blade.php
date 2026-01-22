<div class="modal fade" id="modalBuscarPersona" tabindex="-1" aria-labelledby="modalBuscarPersonaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalBuscarPersonaLabel">Buscar Persona</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label for="search-persona">Buscar Persona</label>
                        <input type="text" id="search-persona" class="form-control"
                            placeholder="Buscar persona por nombre o apellido...">
                        <input type="hidden" id="persona_id" name="persona_id">
                        <div id="search-results-persona" class="list-group mt-2"
                            style="position: absolute; z-index: 1000;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregarPersona" disabled>Agregar</button>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#listaPropietario">Cerrar</button>
            </div>
        </div>
    </div>
</div>
