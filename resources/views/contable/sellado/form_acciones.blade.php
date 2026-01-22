<!-- Modal Acciones -->

<div class="modal fade" id="modalAcciones" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Encabezado del Modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Acciones</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Modal -->
            <div class="modal-body">
                <div class="row">
                    <!-- Botón Exportar -->
                    <div class="col-md-6 d-flex justify-content-start align-items-center">
                        <form action="{{ route('exportar.registroSellado') }}" method="GET" autocomplete="off">
                            <button type="submit" class="btn btn-primary w-100">Exportar</button>
                        </form>
                    </div>

                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <div class="d-flex gap-2">
                            <form id="form_eliminar" action="{{ route('registroSellado.destroy') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input class="form-check-input" type="checkbox" name="check_eliminar" value="1" id="check_valor_ga">
                                <button type="submit" class="btn btn-danger" id="btn_eliminar">Eliminar</button>
                            </form>
                        </div>
                    </div>


                </div>
            </div>

            <!-- Pie del Modal -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
