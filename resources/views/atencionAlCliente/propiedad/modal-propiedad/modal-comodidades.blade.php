<div class="modal fade" id="exampleModalS" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Servicios y Caracteristicas</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row  px-3 pb-3">
                        <div class="col-md-5 px-1">
                            <label class="text-center" id="basic-addon1">Estado</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->estadoGeneral->estado_general ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-3 px-1">
                            <label class="text-center" id="basic-addon1">Dormitorios</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->cantidad_dormitorios ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Baños</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->banios ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Cochera</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->cochera ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Nª Cochera</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->numero_cochera ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">m² Lote</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->mLote ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">m² Cubiertos</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->mCubiertos ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Asfalto</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->asfalto ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Gas</label>
                            <input type="text" class="form-control text-center" value="{{ $propiedad->gas ?? '-' }}"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Cloaca</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->cloaca ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Agua</label>
                            <input type="text" class="form-control text-center" value="{{ $propiedad->agua ?? '-' }}"
                                id="calle-input" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                </div>
            </div>
        </div>
    </div>