<div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e9ecef; background: #f8fafc;">
                <h5 class="modal-title fw-semibold" id="staticBackdropLabel" style="letter-spacing: .5px;">Búsqueda
                    de Propiedades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pb-2" style="padding-bottom: 0;">
                <div
                    style="position: sticky; top: 0; z-index: 10; background: #fff; border-radius: 12px 12px 0 0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding-bottom: 10px;">
                    <form autocomplete="off">
                        <input type="hidden" name="buscar-propiedad-asesores" value="buscar-propiedad-asesores">

                        <div class="row g-1 mx-5 mb-1 d-flex align-items-end justify-content-center">
                            <div class="col-md-3 mx-3">
                                <label class="form-label mb-1">Código</label>
                                <input type="number" id="inputCodigoPropiedad" class="form-control form-control-sm"
                                    placeholder="Código">
                            </div>
                            <div class="col-md-6 mx-3">
                                <label class="form-label mb-1">Calle / Número</label>
                                <input type="text" id="inputCallePropiedad" class="form-control form-control-sm"
                                    placeholder="Calle o número">
                            </div>
                            <div class="col-md-2 mx-3">
                                <label class="form-label mb-1">Dorm.</label>
                                <input type="number" id="inputDormPropiedad" class="form-control form-control-sm"
                                    placeholder="Cantidad">
                            </div>
                            
                            <div class="col-md-2 mx-3">
                                <label class="form-label mb-1">Baños</label>
                                <input type="number" id="inputBaniosPropiedad" class="form-control form-control-sm"
                                    placeholder="Cantidad">
                            </div>
                            <div class="col-md-2 mx-3">
                                <label class="form-label mb-1">Cochera</label>
                                <div>
                                    <select name="" id="inputCocheraPropiedad" class="form-select form-select-sm">
                                        <option value="">-</option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>


                            <div class=" col-md-2 mt-3">
                                <button type="button" id="btnBuscarPropiedades" class="btn btn-primary btn-sm w-100"
                                    style="border-radius: 5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                                        <path
                                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.442 1.398a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z" />
                                    </svg>
                                    Buscar
                                </button>
                            </div>



                        </div>


                    </form>
                </div>
                <div style="max-height: 350px; overflow-y: auto; background: #fff; border-radius: 0 0 12px 12px;">
                    <div class="table-responsive rounded shadow-sm mt-0">
                        <table id="tabla_filtraPropiedades" class="table table-sm table-borderless align-middle mb-0">
                            <thead style="background: #e9f0fa;">
                                <tr style="font-size: 0.97rem; color: #0d6efd;">
                                    <th scope="col" class="text-center fw-semibold">Cod venta</th>
                                    <th scope="col" class="fw-semibold">Dirección</th>
                                    <th scope="col" class="fw-semibold">Zona</th>
                                    <th scope="col" class="fw-semibold">Dorm.</th>
                                    <th scope="col" class="fw-semibold">Baños</th>
                                    <th scope="col" class="fw-semibold">Cochera</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyFiltraPropiedades" style="font-size: 0.96rem; color: #495057;">
                                <!-- Resultados dinámicos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
