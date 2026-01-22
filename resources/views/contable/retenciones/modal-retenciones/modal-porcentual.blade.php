<div class="modal fade" id="modalporcentual" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Base y Porcentual</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Fix: Use a custom route or method for updating both values -->
                <form action="{{ route('retenciones.updateBasePorcentual') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="base_id" value="{{ $bases_porcentuales[0]->id_base_porcentual }}">
                    <input type="hidden" name="porcentual_id" value="{{ $bases_porcentuales[1]->id_base_porcentual }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_base_calculo" class="form-label">Base</label>
                            <!-- Fix: Add name attribute -->
                            <input type="number" class="form-control" id="modal_base_calculo" name="base_dato"
                                aria-describedby="baseHelp" value="{{ $bases_porcentuales[0]->dato ?? '' }}">
                            <div id="baseHelp" class="form-text">Es la base mínima para calcular la retención
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_porcentual_calculo" class="form-label">Porcentual</label>
                            <!-- Fix: Add name attribute -->
                            <input type="number" class="form-control" id="modal_porcentual_calculo"
                                name="porcentual_dato" value="{{ $bases_porcentuales[1]->dato ?? '' }}" step="0.01">
                            <div id="porcentualHelp" class="form-text">Es el porcentual que aplica para la
                                retención
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button id="modal_boton_b_p_calculo" type="submit" class="btn btn-primary">Modificar</button>
            </div>
            </form>
        </div>
    </div>
</div>
