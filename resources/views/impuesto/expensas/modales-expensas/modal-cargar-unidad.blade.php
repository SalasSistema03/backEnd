<div class="modal fade" id="modalEditarUnidad" tabindex="-1" aria-labelledby="modalEditarUnidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUnidadLabel">Editar Unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('exp_unidades_completar_carga') }}" method="POST" class="row p-3" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row g-3 d-flex justify-content-between">
                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-text text_modal" style="height: 32px;">Folio</span>
                                <input type="text" class="form-control text_modal_text" id="folioInput"
                                    name="folio" readonly>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text text_modal" style="height: 32px;">Ubicacion</span>
                                <input type="text" class="form-control text_modal_text" id="ubicacionInput"
                                    name="ubicacion" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text text_modal">Estado</span>
                                <select class="form-select" name="estado" id="estadoSelect">
                                    <option value="Activo">ACTIVO</option>
                                    <option value="Inactivo">INACTIVO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-text text_modal">Comision</span>
                                <input type="text" class="form-control" id="comisionInput" name="comision"
                                    style="height: 34px;" oninput="this.value = this.value.toUpperCase();" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-text text_modal">Adm</span>
                                <input type="text" class="form-control" id="administraInput" name="administra"
                                    style="height: 34px;" oninput="this.value = this.value.toUpperCase();" readonly>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text text_modal" style="height: 34px;">Edificio</span>
                                <select class="form-select text_modal_text" name="edificio" id="edificioSelect">
                                    <option value="">Seleccione un edificio</option>
                                    @foreach ($edificios as $edificio)
                                        <option value="{{ $edificio->id }}">
                                            {{ strtoupper($edificio->nombre_consorcio) }}
                                            - {{ $edificio->direccion }} {{ $edificio->altura }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <input type="hidden" id="idInput" name="id">

                    <!-- Contenedor dinámico para unidades -->
                    <div id="unidades" class="mi-caja">
                        <!-- Primera fila (índice 0) -->
                        <div class="unidad-row mb-3" data-index="0">
                            <div class="row d-flex justify-content-between pb-2">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text text_modal_m" style="height: 34px;">Tipo</span>
                                        <select class="form-select" name="repetir[0][tipo]">
                                            <option value="" selected>Seleccione un tipo...</option>
                                            <option value="DEPARTAMENTO">DEPARTAMENTO</option>
                                            <option value="COCHERA">COCHERA</option>
                                            <option value="BAULERA">BAULERA</option>
                                            <option value="LOCAL_COMERCIAL">LOCAL COMERCIAL</option>
                                            <option value="OFICINA">OFICINA</option>
                                            <option value="CASA">CASA</option>
                                            <option value="TERRENO">TERRENO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text text_modal_m"
                                            style="height: 34px; width: 87px;">Estado</span>
                                        <select class="form-select" name="repetir[0][estado]">
                                            <option value="Activo">ACTIVO</option>
                                            <option value="Inactivo">INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button"
                                        class="btn btn-outline-secondary btn-sm w-100 h-100 btn-comentario"
                                        data-index="0" onclick="abrirModalComentario(0)">
                                        <i class="fa-regular fa-comment"></i> Comentario
                                    </button>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between pb-2">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text text_modal_m"
                                            style="height: 34px;">Unidad</span>
                                        <input type="number" class="form-control" name="repetir[0][unidad]"
                                            style="height: 34px;" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text text_modal_m" style="height: 34px;">Piso</span>
                                        <input type="number" class="form-control" name="repetir[0][piso]"
                                            style="height: 34px;" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text text_modal_m"
                                            style="height: 34px; width: 87px;">Depto</span>
                                        <input type="text" class="form-control" name="repetir[0][depto]"
                                            style="height: 34px;" oninput="this.value = this.value.toUpperCase();">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        style="height: 34px;" onclick="eliminarUnidad(this)">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Campo oculto para el comentario de esta unidad -->
                            <input type="hidden" name="repetir[0][comentario]" class="comentario-hidden"
                                data-index="0">
                            <hr>
                        </div>
                    </div>
                    <br>
                    <button type="button" onclick="agregarUnidad()" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Agregar
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="guardarCambiosUnidad">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
