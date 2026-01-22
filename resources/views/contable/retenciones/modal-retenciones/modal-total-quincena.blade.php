<div class="modal fade" id="total_q" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Total por Quincena</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="fecha_suma_retencion" class="form-label">Fecha a procesar</label>
                            <input type="date" class="form-control" id="fecha_suma_retencion"
                                aria-describedby="emailHelp">
                            <div class="form-text">Solo toma mes y año</div>
                        </div>
                        <div class="col-md-5">
                            <label for="suma_primer_quincena" class="form-label">1er Quincena</label>
                            <input type="number" class="form-control" id="suma_primer_quincena" disabled>
                        </div>
                        <div class="col-md-5">
                            <label for="suma_segunda_quincena" class="form-label">2da Quincena</label>
                            <input type="number" class="form-control" id="suma_segunda_quincena" disabled>
                        </div>

                    </div>

                    <button id="boton_suma_quincena" type="button" class="btn btn-primary">Calcular</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
