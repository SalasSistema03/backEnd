<div class="modal fade" data-bs-backdrop="static" id="calendarEventModal" tabindex="-1"
    aria-labelledby="calendarEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarEventModalLabel">Agenda</h5>
                <span id="sector-modal-id"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="calendarEventForm" class="row" action="{{ route('agenda.store') }}" method="POST"
                    autocomplete="off">
                    @csrf
                    <input type="hidden" id="id_criterio" name="id_criterio">
                    <input type="hidden" id="modal-sector-id" name="sector" value="2">
                    <input type="hidden" id="modal-userid" name="usuario_id" value="{{ session('usuario_id') }}">
                    
                    {{-- No me interesa mandarlo --}}
                    <div class="col-md-3">
                        <label for="modal-username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="modal-username" value="{{ $usuario_nombre }}">
                    </div>
                    <div class="col-md-3">
                        <label for="fechaNota" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fechaNota" name="fecha">
                    </div>
                    <div class="col-md-3">
                        <label for="modal-hora-inicio" class="form-label">Hora de inicio</label>
                        <input type="time" class="form-control" id="modal-hora-inicio" name="hora_inicio"
                            step="900">
                    </div>
                    <div class="col-md-3">
                        <label for="modal-hora-fin" class="form-label">Hora de finalización</label>
                        <input type="time" class="form-control" id="modal-hora-fin" name="hora_fin" step="900">
                    </div>
                    <div class="col-md-12">
                        <label for="modal-descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="modal-descripcion" name="descripcion" placeholder="Descripción del evento"
                            rows="4"></textarea>
                    </div>

                    <div class="col-md-6" style="display:none;" id="div-buscar-propiedad">
                        <label for="tipo_busqueda" class="form-label">Buscar propiedad por</label>
                        <select class="form-control" id="tipo_busqueda">
                            <option value="codigo">Código</option>
                            <option value="calle">Calle</option>
                        </select>
                    </div>
                    <div class="col-md-6" style="display:none;" id="div-codigo-propiedad">
                        <label for="propiedad" class="form-label">Codigo Propiedad</label>
                        <input type="text" class="form-control" id="propiedad" autocomplete="off">
                        <input type="hidden" id="propiedad-codigo-real" name="propiedad_id">
                        <div id="sugerencias-propiedades" class="list-group position-absolute" style="z-index: 1000;">
                        </div>
                    </div>
                    <div class="col-md-6" style="display:none;" id="div-calle-propiedad">
                        <label for="calle-autocomplete" class="form-label">Calle Propiedad</label>
                        <input type="text" id="calle-autocomplete" class="form-control"
                            placeholder="Buscar calle..."
                            value="{{ isset($eventoActivo) ? $eventoActivo['nota']->calle : '' }}">
                        <input type="hidden" id="calle_id" name="calle"
                            value="{{ isset($eventoActivo) ? $eventoActivo['nota']->calle : '' }}">
                        <div id="calle-suggestions" class="list-group mt-2"
                            style="position: absolute; z-index: 1000;"></div>
                    </div>
                    <div class="col-md-6" style="display:none;" id="clientelefono">
                        <label for="cliente" class="form-label">Numero Telefono Cliente</label>
                        <input type="text" class="form-control" id="cliente" autocomplete="off">
                        <input type="hidden" id="cliente-telefono-real" name="cliente_id">
                        <div id="sugerencias-clientes" class="list-group position-absolute" style="z-index: 1000;">
                        </div>
                    </div>
                    <input type="hidden" id="nota-id" name="nota_id">
                </form>
                <div class="d-flex justify-content-between mt-3">
                   {{--  <div>
                        <form id="bajaForm" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" id="nota-id-baja" name="id">
                            <input type="hidden" id="fecha-baja" name="usuario" value="{{ session('usuario_id') }}">
                            <button type="submit" class="btn btn-danger">Baja</button>
                        </form>
                    </div> --}}
                    <div class="ms-auto">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary me-2"
                                    data-bs-dismiss="modal">Cancelar</button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btnGuardar" class="btn btn-primary"
                                    onclick="document.getElementById('calendarEventForm').submit();">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

