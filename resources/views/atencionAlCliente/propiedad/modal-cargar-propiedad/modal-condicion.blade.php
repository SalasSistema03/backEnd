<div class="modal fade" id="condicionAlquilesPropiedad" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">
                                Condicion de Alquiler
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="formulario" id="formulario" value="">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="mb-6">

                                        <textarea name="condicion" id="condicion" class="form-control form-control-atcl" rows="8">{{ old('condicion', request('condicion')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                {{-- eL CERRAR TIENE QUE APUNTAR AL MODAL ALQUILER --}}
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                    data-bs-target="#AlquilerPropiedadCarga">Cerrar</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>