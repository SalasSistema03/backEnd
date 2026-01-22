<div class="modal fade" id="modificaion_registros" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Modificacion Retencion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario_retenciones_carga_personas" action="{{ route('retenciones.modificarRetencion') }}"
                method="POST" class="modal-body row g-3 needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">

                    <input type="hidden" name="id_comprobante" id="id_comprobante">
                    <div class="col-md-3">
                        <label for="cuit_retenciones_m" class="form-label">CUIT</label>

                        <input type="text" class="form-control" id="cuit_retenciones_m" value="" disabled>
                        <input type="hidden" name="cuit_retenciones_mi" id="cuit_retenciones_mi">

                    </div>
                    <div class="col-md-3">
                        <label for="fecha_comprobante_m" class="form-label">Fecha Quincena</label>
                        <input type="date" class="form-control" id="fecha_comprobante_m" name="fecha_comprobante_m"
                            value="">
                    </div>
                    <div class="col-md-3">
                        <label for="calcula_base_m" class="form-label">Base</label>
                        <select class="form-select" aria-label="Default select example" id="calcula_base_m"
                            name="calcula_base_m">
                            <option selected>-</option>
                            <option value="S">Si</option>
                            <option value="N">No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="numero_comprobante_retenciones_m" class="form-label">N° Compr.</label>
                        <input type="number" class="form-control" id="numero_comprobante_retenciones_m"
                            name="numero_comprobante_retenciones_m" value="">
                    </div>
                    <div class="col-md-4">
                        <label for="importe_retenciones_m" class="form-label">Importe Compr.</label>
                        <input type="number" class="form-control" id="importe_retenciones_m"
                            name="importe_retenciones_m" value="">
                        {{-- <input type="hidden" name="importe_comprobante_m" id="importe_comprobante_m"> --}}
                    </div>
                    <div class="col-md-3">
                        <label for="importe_retencion_m" class="form-label">Importe Ret.</label>
                        <input type="number" class="form-control" id="importe_retencion_m" value="" disabled>
                        <input type="hidden" name="importe_retencion_mi" id="importe_retencion_mi">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="boton_modifica_retencion">Modificar</button>
                </div>
            </form>


        </div>
    </div>
</div>
