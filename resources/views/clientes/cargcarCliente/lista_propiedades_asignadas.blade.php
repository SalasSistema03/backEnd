<div class="contenedor_tabla">

    <div class="d-flex justify-content-center contenedor_titulo">
        <div class="d-flex align-items-center justify-content-center itulos_contenedores mt-2">
            <span class="fw-semibold">Propiedades asignadas</span>
        </div>
    </div>

    <div class="col-md-10">
        <div class="col-md-6 d-flex align-items-end">
            <button type="button" id="btn_asignarPropiedad" class="btn btn-outline-secondary " style="margin: 5px; height: 1.8rem; width: 8rem;" data-bs-toggle="modal" data-bs-target="#exampleModal">Asignar propiedad</button>
            @include('clientes.cargcarCliente.modal_listaPropiedades')
        </div>
    </div>

    <!--     <ul class="nav nav-tabs" id="myTab" role="tablist"> -->
    <!--         <li class="nav-item small" role="presentation">
            <a class="nav-link active" id="ventaProp-tab" data-bs-toggle="tab" href="#ventaProp"
            role="tab" aria-controls="ventaProp" aria-selected="true">Venta</a>
        </li> -->
    <!--         <li class="nav-item small" role="presentation">
            <a class="nav-link" id="alquilerProp-tab" data-bs-toggle="tab" href="#alquilerProp" type="button" role="tab" aria-controls="alquilerProp" aria-selected="false">Alquiler</a>
        </li> -->
    <!--     </ul> -->

    <div class="tab-content" id="myTabContent">
        <!-- Tab venta -->
        <div class="tab-pane fade show active" id="ventaProp" role="tabpanel" aria-labelledby="venta-tab">
            <div id="contenedor_tabla_propAsignadas" style="max-height: 10rem; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                <table id="tabla_propiedad_venta" class="table table-sm mb-0" style="width: 100%;">
                    <thead style="font-size: 90%;" class="table-light">
                        <tr class="text-center">
                            <th scope="col">Cod venta</th>
                            <th scope="col">Dirección</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 80%;">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab alquiler -->
        <div class="tab-pane fade" id="alquilerProp" role="tabpanel" aria-labelledby="venta-tab">
            <div style="max-height: 10rem; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                <div id="contenedor_tabla_propAsignadas">
                    <table id="tabla_propiedad_alquiler" class="table table-sm mb-0" style="width: 100%;">
                        <thead style="font-size: 90%;" class="table-light sticky-top">
                            <tr>
                                <th scope="col">Cod alquiler</th>
                                <th scope="col">Dirección</th>
                                <th scope="col"></th>
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

<!--     <div class="col-md-12" style="margin-top: 3px;">
        <ul class="nav nav-tabs">
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="ventaProp-tab" data-bs-toggle="tab" href="#ventaProp"
                    role="tab" aria-controls="ventaProp" aria-selected="true">Venta</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="alquilerProp-tab" data-bs-toggle="tab" href="#alquilerProp"
                    role="tab" aria-controls="alquilerProp" aria-selected="false">Alquiler</a>
            </li>

        </ul>
    </div> -->

<!--     <div  style="border: 1px solid black;">
        <div class="tab-content" id="myTabContent">
  
            <div class="tab-pane fade" id="ventaProp" role="tabpanel" aria-labelledby="ventaProp-tab">
                <div>
                    <div id="contenedor_tabla_propAsignadas">
                        <table id="tabla_propiedad_venta" class="table table-sm table-borderless align-middle mb-0" style=" width: 98%;" >
                            <thead class="bg-table-header">
                                <tr class="text-table-header">
                                    <th scope="col" class="fw-semibold ps-3">Cod venta</th>
                                    <th scope="col" class="fw-semibold">Dirección</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
            <div class="tab-pane fade" id="alquilerProp" role="tabpanel" aria-labelledby="alquilerProp-tab">
                <div class="px-2 py-3">
                    <div id="contenedor_tabla_propAsignadas">
                        <table id="tabla_propiedad_alquiler" class="table table-sm table-borderless align-middle mb-0">
                            <thead class="bg-table-header">
                                <tr class="text-table-header">
                                    <th scope="col" class="fw-semibold ps-3">Cod alquiler</th>
                                    <th scope="col" class="fw-semibold">Dirección</th>
                                </tr>
                            </thead>
                            <tbody class="text-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> -->