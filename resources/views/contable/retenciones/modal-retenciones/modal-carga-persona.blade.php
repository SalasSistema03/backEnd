<div class="modal fade" id="carga_persona_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Carga de Personas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('retenciones.store') }}" method="POST" class="modal-body row g-3 needs-validation"
                novalidate autocomplete="off">
                @csrf
                <div class="col-md-2">
                    <label for="cuit_carga_retenciones" class="form-label">CUIT</label>
                    <input type="number" class="form-control" id="cuit_carga_retenciones" name="cuit_carga_retenciones"
                        value="">
                </div>
                <div class="col-md-10">
                    <label for="razon_soscial_carga_retenciones" class="form-label">Razon Social</label>
                    <input type="text" class="form-control" id="razon_social_carga_retenciones"
                        name="razon_social_carga_retenciones" value=""
                        onkeyup="this.value = this.value.toUpperCase()">

                </div>
                <div class="col-md-8">
                    <label for="domicilio_carga_retenciones" class="form-label">Domicilio Fizcal</label>
                    <input type="text" class="form-control" name="domicilio_carga_retenciones" value=""
                        onkeyup="this.value = this.value.toUpperCase()">
                </div>
                <div class="col-md-4">
                    <label for="selector_provincia_modal" class="form-label">Provincia</label>
                    <select name="provincia_id" id="provincia_id" class="form-select" required>
                        <option value="">Seleccione una provincia</option>
                        @foreach ($provincias as $provincia)
                            <option value="{{ $provincia->numero_provincia_retencion }}">
                                {{ $provincia->nombre_provincia_retencion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9">
                    <label for="localidad_carga_retenciones" class="form-label">Localidad</label>
                    <input type="text" class="form-control" name="localidad_carga_retenciones" value=""
                        onkeyup="this.value = this.value.toUpperCase()">
                </div>

                <div class="col-md-3">
                    <label for="codigo_posta_carga_retenciones" class="form-label">Codigo Postal</label>
                    <input type="number" class="form-control" name="codigo_posta_carga_retenciones" value="">
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="boton_exportar_personas">Exportacion
                        Personas</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="boton_enviar_persona">Carga</button>
                </div>
            </form>


        </div>
    </div>
</div>
