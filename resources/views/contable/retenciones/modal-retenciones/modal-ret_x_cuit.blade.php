<div class="modal fade" id="ret_x_cuit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Retencion por CUIT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row mb-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="input_cuit_retencion"
                            placeholder="Ingrese CUIT (sin guiones)">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" onclick="buscarRetencionesPorCuit()">Buscar</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>CUIT</th>
                                <th>Fecha</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody id="contenedor_retenciones_x_cuit">
                            <!-- Aquí se cargarán las retenciones -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
