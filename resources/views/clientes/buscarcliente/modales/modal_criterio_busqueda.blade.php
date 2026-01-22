<button type="button" id="btn_asignarCriterio" class="btn  btn-sm btnSalas botones_asignar" data-bs-toggle="modal" data-bs-target="#modal_criterio">Asignar criterio</button>

<!-- este DIV contiente el ID del cliente -->
<div id="datos-cliente"
    data-id-cliente="{{ $cliente['id_cliente'] }}"
    data-id-asesor="{{ $cliente['asesor']['id_usuario'] ?? '' }}"
    data-id-asesor-alquiler="{{ $cliente['asesorAlquiler']['id_usuario'] ?? '' }}">
</div>

<!-- Modal filtrado propiedad -->
<div class="modal fade" id="modal_criterio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content" id="contenedor_modal">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="staticBackdropLabel" style=" color: black;">Criterio de busqueda del cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pb-2" style="padding-bottom: 0;">
                <div class="row">
                    <div class="col-md-6">
                        <label>Tipo de inmueble</label>
                        <select class="form-control" name="id_tipo_inmueble" id="id_tipo_inmueble">
                            <option value="" selected disabled>Seleccionar</option>
                            @foreach($tipoInmuebles as $tipoInmueble)
                            <option value="{{ $tipoInmueble->id }}">{{ $tipoInmueble->inmueble }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Cant dormitorios</label>
                        <input type="number" class="form-control" name="cant_dormitorios" id="cant_dormitorios" min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>Cochera</label>
                        <select id="cochera" name="cochera" class="form-control">
                            <option value="NO" selected>No</option>
                            <option value="SI">Sí</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Zona</label>
                        <select class="form-control custom-select-style" name="zona" id="zona">
                            <option value="" selected disabled>Seleccionar</option>
                            @foreach($zonas as $zona)
                            <option value="{{ $zona->id }}">{{ $zona->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-succes btnSalas" id="btn_guardar_cliente">Agregar</button>
            </div>
        </div>
    </div>
</div>