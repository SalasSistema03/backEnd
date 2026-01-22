<button type="button" class="btn  btn-sm btnSalas botones_asignar" data-bs-toggle="modal"
    data-bs-target="#modal_propiedades">Asignar propiedad</button>


<div id="datos-cliente" data-id-cliente="{{ $cliente['id_cliente'] }}"
    data-id-asesor="{{ $cliente['asesor']['id_usuario'] ?? '' }}"
    data-id-asesor-alquiler="{{ $cliente['asesor_alquiler']['id_usuario'] ?? '' }}"
    data-telefono="{{ $cliente['telefono'] ?? '' }}" data-usuario-id="{{ session('usuario_id') }}">
</div>


<!-- Modal filtrado propiedad -->
<div class="modal fade" id="modal_propiedades" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalPropiedadesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold text-dark" id="modalPropiedadesLabel">
                    Búsqueda de Propiedadesssss
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <!-- Filtros de búsqueda -->
                <form autocomplete="off">
                    <div class="row  align-items-end mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Código</label>
                            <input type="number" id="inputCodigoPropiedad" class="form-control form-control-sm"
                                placeholder="Código">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Calle</label>
                            <input type="text" id="inputCallePropiedad" class="form-control form-control-sm"
                                placeholder="Calle">
                        </div>

                        

                        <div class="col-md-2">
                            <button type="button" id="btnBuscarPropiedades"
                                class="btn btn-primary btn-sm w-100 rounded-3">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resultados -->
                <div class="table-responsive" id="contenedor_tabla_filtraPropiedades">
                    <table id="tabla_filtraPropiedades" class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-primary fw-semibold small">
                                <th class="text-center">Cod venta/alquiler</th>
                                <th>Dirección</th>
                                <th>Zona</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyFiltraPropiedades" class="small text-dark">
                            <!-- Resultados dinámicos -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
