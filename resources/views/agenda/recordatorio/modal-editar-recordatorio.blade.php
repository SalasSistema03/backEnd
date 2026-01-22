 <!-- Modal de Edición -->
    <div class="modal fade" id="editarRecordatorioModal" tabindex="-1" aria-labelledby="editarRecordatorioModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarRecordatorioModalLabel">Editar Recordatorio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarRecordatorio" action="{{ route('recordatorio.update', 0) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordatorio_id">
                        <input type="hidden" name="finalizado" id="finalizado" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="fecha-modal">Fecha</label>
                            <input type="date" id="fecha-modal" class="form-control" name="fecha_inicio">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="hora-modal">Hora</label>
                            <input type="time" id="hora-modal" class="form-control" name="hora">
                        </div>
                        <div class="row">
                            <div class="col-md-4 ">
                                <label class="form-label fw-semibold" for="intervalo-modal">Frecuencia</label>
                                <select name="intervalo" id="intervalo-modal" class="form-control">
                                    <option value="">Seleccione</option>
                                    <option value="Diario">Diario</option>
                                    <option value="Mensual">Mensual</option>
                                </select>
                            </div>
                            <div class="col-md-4 ">
                                <label class="form-label fw-semibold" for="cantidad-modal">Cantidad</label>
                                <input type="number" id="cantidad-modal" class="form-control" name="cantidad"
                                    min="1">
                            </div>
                            <div class="col-md-4 ">
                                <label class="form-label fw-semibold" for="repetir-modal">Repetir</label>
                                <input type="number" id="repetir-modal" class="form-control" name="repetir"
                                    min="1">
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-md-2 ">
                                <label class="form-label fw-semibold" for="intervalo-modal">Proximo</label>
                            </div>
                            <div class="col-md-4 ">
                                <input type="date" id="proximo-modal" class="form-control" name="cantidad" disabled>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold" for="repetir-modal">Finaliza</label>
                            </div>
                            <div class="col-md-4 ">
                                <input type="date" id="finaliza-modal" class="form-control" name="repetir" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="agenda_id-modal">Agendar</label>
                            <select name="agenda_id" id="agenda_nombre-modal" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="">Personal</option>
                                @foreach ($agenda as $items)
                                    <option value="{{ $items->id }}">
                                        {{ $items->sector->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="descripcion-modal">Descripción</label>
                            <textarea id="descripcion-modal" rows="3" class="form-control" name="descripcion"
                                placeholder="Detalles del recordatorio"></textarea>
                        </div>
                        <input type="hidden" name="usuario_id" value="{{ session('usuario_id') }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>