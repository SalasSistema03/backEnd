<!-- ESTE ARCHIVO contiene 2 modales: un modal para BUSCAR(modal secundario) una tgi y otro para GURDARLO(modal principal) -->

<div class="d-flex gap-2 mb-1">
    <button type="submit" class="btn btn-primary btn-sm">Cargar</button>
    <button type="button" class="btn btn-secondary btn-sm"
        data-bs-toggle="modal" data-bs-target="#modalNuevo">
        Carga manual
    </button>
</div>


<!-- Modal -->
<!-- Modal para cargar a mano -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Carga TGI manual</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('cargarNuevoTgiControllerManual') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4 d-grid">
                            <button type="button" class="btn btn-primary btn-sm" id="abrirModal">
                                Seleccionar TGI
                            </button>
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-md-4">
                            <input type="text" name="partida" id="partida" class="form-control form-control-sm" placeholder="Partida">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="clave" id="clave" class="form-control form-control-sm" placeholder="Clave">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="administra" id="administra" class="form-control form-control-sm" placeholder="Administra">
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-md-6">
                            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="importe" id="importe" class="form-control form-control-sm" placeholder="Importe">
                        </div>
                    </div>
                </div>

                <!-- Botones bien alineados -->
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal secundario: Buscar TGI -->
<div class="modal fade" id="modalBuscarTGI" tabindex="-1" aria-labelledby="modalBuscarTGILabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- modal-lg para más espacio -->
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalBuscarTGILabel">Buscar TGI</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <!-- Filtros -->
                <form id="formBuscarTGI" autocomplete="off">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="folio_buscar" id="folio"
                                class="form-control form-control-sm" placeholder="Folio">
                        </div>
                        <div class="col-md-5">
                            <select name="empresa_buscar" id="empresa"
                                class="form-select form-select-sm">
                                <option value="1" selected>Atilio Salas SRL</option>
                                <option value="2">Dolly J. Pianesi</option>
                                <option value="3">Giusiano Maria Florencia</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="button" class="btn btn-primary btn-sm" id="btnBuscar">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>



                <!-- Resultados en tabla scrollable -->
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th scope="col">Folio</th>
                                <th scope="col">Partida</th>
                                <th scope="col">Clave</th>
                                <th scope="col">Administra</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaResultadosTGI">

                        </tbody>
                    </table>
                </div>


            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const abrirModal = document.getElementById('abrirModal');
        const modalBuscar = new bootstrap.Modal(document.getElementById('modalBuscarTGI'), {
            backdrop: false // evita que se cierre el modal de atrás
        });

        abrirModal.addEventListener('click', function() {
            modalBuscar.show();
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        // Delegación de eventos: escucha clicks en botones Seleccionar dentro de la tabla
        document.getElementById('tablaResultadosTGI').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('btnSeleccionar')) {
                // obtener los atributos data-* del botón
                const folio = e.target.getAttribute('data-folio');
                const partida = e.target.getAttribute('data-partida');
                const clave = e.target.getAttribute('data-clave');
                const administra = e.target.getAttribute('data-administra');

                // rellenar los campos del modal principal
                document.getElementById('partida').value = partida;
                document.getElementById('clave').value = clave;
                document.getElementById('administra').value = administra;

                // cerrar el modal secundario
                const modalBuscar = bootstrap.Modal.getInstance(document.getElementById('modalBuscarTGI'));
                modalBuscar.hide();
            }
        });

        // botón "Seleccionar TGI" abre el modal secundario
        const abrirModalBtn = document.getElementById('abrirModal');
        abrirModalBtn.addEventListener('click', function() {
            const modalBuscar = new bootstrap.Modal(document.getElementById('modalBuscarTGI'), {
                backdrop: false // para que no cierre el modal principal
            });
            modalBuscar.show();
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
    // --- Modal secundario: ejecutar búsqueda con ENTER ---
    const folioInput = document.getElementById('folio');
    const empresaSelect = document.getElementById('empresa');
    const btnBuscar = document.getElementById('btnBuscar');

    // función que dispara el click en Buscar
    function ejecutarBusqueda() {
        btnBuscar.click();
    }

    folioInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // evita submit del form
            ejecutarBusqueda();
        }
    });

    empresaSelect.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            ejecutarBusqueda();
        }
    });

    // --- Modal principal: ejecutar guardado con ENTER ---
    const fechaInput = document.getElementById('fecha_vencimiento');
    const importeInput = document.getElementById('importe');
    const formPrincipal = document.querySelector('#modalNuevo form');

    function ejecutarGuardado() {
        formPrincipal.submit();
    }

    fechaInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            ejecutarGuardado();
        }
    });

    importeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            ejecutarGuardado();
        }
    });
});

</script>