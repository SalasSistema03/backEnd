<div class="contenedor_tabla mt-2" >
    <div class="d-flex justify-content-center contenedor_titulo">
        <div class="d-flex align-items-center justify-content-center itulos_contenedores mt-2">
            <span class="fw-semibold titulo_text" style="font-size: 1rem; letter-spacing: 1.5px;">Lista criterio de búsqueda</span>
        </div>
    </div>



<!-- 
    <div>
        <ul class="nav nav-tabs menu_tabs">
            <li class="nav-item" role="presentation">
                <a class="nav-link small active" id="ventaCriterio-tab" data-bs-toggle="tab" href="#ventaCriterio"
                    role="tab" aria-controls="ventaCriterio" aria-selected="true">Venta</a>
            </li> -->
<!--             <li class="nav-item" role="presentation">
                <a class="nav-link small" id="alquilerCriterio-tab" data-bs-toggle="tab" href="#alquilerCriterio"
                    role="tab" aria-controls="alquilerCriterio" aria-selected="false">Alquiler</a>
            </li> -->

<!--         </ul>
    </div> -->



    <div class="card-body p-0 mt-2" >
        <div class="tab-content" id="myTabContent">
            <!-- Venta -->
            <div class="tab-pane fade show active" id="ventaCriterio" role="tabpanel" aria-labelledby="ventaCriterio-tab">
                <div style="max-height: 12rem; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="tabla_criterios_venta" class="table table-sm mb-0">
                        <thead style="font-size: 90%;" class="table-light sticky-top">
                            <tr>
                                <th scope="col" class="fw-semibold">Tipo. Inmueble</th>
                                <th scope="col" class="fw-semibold">Cant. dormitorios</th>
                                <th scope="col" class="fw-semibold">Cochera</th>
                                <th scope="col" class="fw-semibold">Zona</th>
                                <th scope="col" class="fw-semibold">Fecha</th>
                                <th scope="col" class="fw-semibold">Estado</th>
                                <th scope="col" class="fw-semibold"></th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 80%;">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Alquiler -->
            <div class="tab-pane fade" id="alquilerCriterio" role="tabpanel" aria-labelledby="alquilerCriterio-tab">
                <div style="max-height: 12rem; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="tabla_criterios_alquiler" class="table table-sm mb-0">
                        <thead style="font-size: 90%;" class="table-light sticky-top">
                            <tr>
                                <th scope="col" class="fw-semibold">Tipo. Inmueble</th>
                                <th scope="col" class="fw-semibold">Cant. dormitorios</th>
                                <th scope="col" class="fw-semibold">Cochera</th>
                                <th scope="col" class="fw-semibold">Zona</th>
                                <th scope="col" class="fw-semibold">Fecha</th>
                                <th scope="col" class="fw-semibold">Estado</th>
                                <th scope="col" class="fw-semibold"></th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 80%;">
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>