<div class="contenedor_titulo_listado d-flex justify-content-between align-items-center">

    <span class="fw-semibold">Criterio de búsqueda asignado</span>

    <div>
        @include('clientes.buscarcliente.modales.modal_criterio_busqueda')
    </div>
</div>


<!-- <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="venta_criterio-tab" data-bs-toggle="tab" href="#venta_criterio_busqueda" role="tab" aria-controls="venta_criterio_busqueda" aria-selected="true">Venta</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="alquiler_criterio-tab" data-bs-toggle="tab" href="#alquiler_criterio_busqueda" role="tab" aria-controls="alquiler_criterio_busqueda" aria-selected="false">Alquiler</a>
    </li>
</ul>
 -->

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="venta_criterio_busqueda" role="tabpanel" aria-labelledby="venta_criterio-tab">
        <!-- Contenido de venta -->
        <div class="contenedor_tablas">
            <div>
                <table id="tabla_criterios_venta" class="table table-sm  w-100" >
                    <thead style="font-size: 90%;" class="table-light ">
                        <tr class="text-center">
                            <th scope="col">Tipo Inmueble</th>
                            <th scope="col">Dormitorios</th>
                            <th scope="col">Cochera</th>
                            <th scope="col">Zona</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>

                    <tbody class="text-center" style="font-size: 80%;">
                        @forelse($cliente['criterio_busqueda_venta'] ?? [] as $consulta)


                        <tr>
                            <td>{{ $consulta['tipoInmueble']['inmueble'] ?? '' }}</td>


                            <td>{{ $consulta['cant_dormitorios'] ?? '' }}</td>
                            <td>{{ $consulta['cochera'] ?? '' }}</td>
                            <td>{{ $consulta['zona']['name'] ?? '' }}</td>
                            <td>{{ $consulta['estado_criterio_venta'] ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($consulta['fecha_criterio_venta'])->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

<!--     <div class="tab-pane fade" id="alquiler_criterio_busqueda" role="tabpanel" aria-labelledby="alquiler_criterio-tab">
     
        <div class="contenedor_tablas">
            <div>
                <table id="tabla_criterios_alquiler" class="table table-sm mb-0 w-100">
                    <thead class="table-light sticky-top small">
                        <tr class="text-center">
                            <th scope="col">Tipo Inmueble</th>
                            <th scope="col">Dormitorios</th>
                            <th scope="col">Cochera</th>
                            <th scope="col">Zona</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center small">
                        @forelse($cliente['criterio_busqueda_alquiler'] ?? [] as $consulta)

                        <tr>
                            <td>{{ $consulta['tipoInmueble']['inmueble'] ?? '' }}</td>
                            <td>{{ $consulta['cant_dormitorios'] ?? '' }}</td>
                            <td>{{ $consulta['cochera'] ?? '' }}</td>
                            <td>{{ $consulta['zona']['name'] ?? '' }}</td>
                            <td>{{ $consulta['estado_criterio_alquiler'] ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($consulta['fecha_criterio_alquiler'])->format('d/m/Y') }}</td>

                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div> -->
</div>