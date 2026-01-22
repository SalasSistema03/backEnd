<div class="modal fade" id="recordatorioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="recordatorioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"
            style="border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e9ecef; background: #f8fafc;">
                <h5 class="modal-title fw-semibold" id="recordatorioModalLabel" style="letter-spacing: .5px;">
                    <i class="bi bi-bell me-2"></i>Agendar Recordatorio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formRecordatorio">
                    @csrf
                    <input type="hidden" id="recordatorio_cliente_id" name="cliente_id">
                    <input type="hidden" name="usuario_id" value="{{ session('usuario_id') }}">
                    <input type="hidden" name="usuario_finaliza" value="{{ session('usuario_id') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="fecha_recordatorio">Fecha</label>
                            <input type="date" id="fecha_recordatorio" class="form-control" name="fecha_inicio"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="hora_recordatorio">Hora</label>
                            <input type="time" id="hora_recordatorio" class="form-control" name="hora"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="intervalo_recordatorio">Frecuencia</label>
                            <select name="intervalo" id="intervalo_recordatorio" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="Diario">Diario</option>
                                <option value="Mensual">Mensual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="cantidad_recordatorio">Cantidad</label>
                            <input type="number" id="cantidad_recordatorio" class="form-control" name="cantidad"
                                value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="repetir_recordatorio">Repetir</label>
                            <input type="number" id="repetir_recordatorio" class="form-control" name="repetir"
                                value="1" min="1">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="proximo_recordatorio">Próximo</label>
                            <input type="date" id="proximo_recordatorio" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="finaliza_recordatorio">Finaliza</label>
                            <input type="date" id="finaliza_recordatorio" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="agenda_recordatorio">Agendar</label>
                            <select name="agenda_id" id="agenda_recordatorio" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="">Personal</option>
                                @if (isset($agenda))
                                    @foreach ($agenda as $item)
                                        <option value="{{ $item->id }}">{{ $item->sector->nombre }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="descripcion_recordatorio">Descripción</label>
                            <textarea id="descripcion_recordatorio" rows="3" class="form-control" name="descripcion"
                                placeholder="Detalles del recordatorio" required></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarRecordatorio">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
