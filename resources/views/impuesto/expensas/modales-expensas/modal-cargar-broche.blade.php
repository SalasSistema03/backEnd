<div class="modal fade" id="expensasModal" tabindex="-1" aria-labelledby="expensasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expensasModalLabel">Carga de Expensas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros fijos -->
                <div class="row mb-2">
                    <!-- Input para folio -->
                    <div class="col-md-1">
                        <label for="folio">Folio</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Folio"
                            oninput="document.getElementById('folio').value = this.value" value=""
                            style="background-color: white;">
                        <input type="hidden" name="folio" id="folio" value="">
                    </div>
                    <!-- Selects y botón -->
                    <div class="col-md-2">
                        <label for="empresa">Empresa</label>
                        <select name="empresa" id="empresa" class="form-select form-select-sm">
                            <option value="-">Seleccionar Empresa</option>
                            <option value="1">Atilio Salas SRL</option>
                            <option value="2">Dolly j. Pianesi</option>
                            <option value="3">Giusiano Maria Florencia</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="edificio">Edificio</label>
                        <select name="edificio" id="edificio" class="form-select form-select-sm">
                            <option value="-">Seleccionar Edificio</option>
                            @foreach ($edificios as $edificio)
                            <option value="{{ $edificio->id }}">{{ $edificio->nombre_consorcio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="administrador">Administrador</label>
                        <select name="administrador" id="administrador" class="form-select form-select-sm">
                            <option value="-">Seleccionar Administrador</option>
                            @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" onclick="buscarfolio()" class="btn btn-primary btn-sm" id="btnBuscar"
                            name="btnBuscar">Buscar</button>
                    </div>
                </div>

                <!-- Contenedor con scroll SOLO para la tabla -->
                <div class="items_expensas_general" style="max-height: 50vh; overflow-y: auto;">
                    <div id="titulos_broches">
                        <!-- Aquí tus encabezados -->
                    </div>
                    <div id="broches">
                        <!-- Aquí tu tabla o contenido -->
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>