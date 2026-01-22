<div class="modal fade" id="ModalBroche" tabindex="-1" aria-labelledby="ModalBrocheLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalBrocheLabel">Armar Broches</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('exp_broche_expensas.descargar') }}" method="GET" target="_blank"
                autocomplete="off">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-2">
                            <label for="cant_broches" class="form-label">Mes</label>
                            <input type="number" name="mes" id="mes" class="form-control form-control-sm" min="1" max="12">
                        </div>
                        <div class="col-md-3">
                            <label for="cant_broches" class="form-label">Anio</label>
                            <input type="number" name="anio" id="anio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-10">
                            <label for="cant_broches" class="form-label">Administradores</label>
                            <div class="input-group">
                                <select name="administrador" id="administrador" class="form-select form-select-sm">
                                    <option value="">Seleccionar Administrador</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>
