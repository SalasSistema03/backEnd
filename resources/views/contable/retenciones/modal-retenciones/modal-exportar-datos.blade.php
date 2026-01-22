<div class="modal fade" id="exportacion_registros" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Exportacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form id="formulario_exportacion_retenciones" action="{{ route('retenciones.exportar') }}"
                    method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="boton_exportacion_datos">TXT</button>
                </form>
                <form id="formulario_exportacion_retenciones_excel" action="{{ route('retenciones.exportarExcel') }}"
                    method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="boton_exportacion_datos_excel">Excel</button>
                </form>

            </div>
        </div>
    </div>
</div>
