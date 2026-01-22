<div class="contenedor_titulo_listado d-flex justify-content-between align-items-center">
    
    <span class="fw-semibold">Propiedades asignadas</span>
    
    <div>
        @include('clientes.buscarcliente.modales.asignar_propiedad')
    </div>
</div>


<!-- <ul class="nav nav-tabs" id="myTab" role="tablist">
    
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="venta-tab" data-bs-toggle="tab" href="#venta" role="tab" aria-controls="venta" aria-selected="true">Venta</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="alquiler-tab" data-bs-toggle="tab" href="#alquiler" role="tab" aria-controls="alquiler" aria-selected="false">Alquiler</a>
    </li>
</ul> -->

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="venta" role="tabpanel" aria-labelledby="venta-tab">
        <!-- Contenido de venta -->
        <div class="contenedor_tablas">
            <div>
                <table id="tabla_propiedad_venta" class="table table-sm mb-0 w-100">
                   <thead class="table-light sticky-top small">
                        <tr class="text-center">
                            <th scope="col">Cod venta</th>
                            <th scope="col">Dirección</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha asignación</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 80%;">
                        @forelse($cliente['consulta_prop_venta'] ?? [] as $consulta)
                        @php
                        $propiedad = $consulta['propiedad'];
                        @endphp
                        <tr>
                            <td>{{ $propiedad['cod_venta'] ?? ''}}</td>
                            <td>{{ $propiedad['calle']['name'] ?? '' }} {{ $propiedad['numero_calle'] ?? 'sin número' }}</td>
                            <td>{{ $consulta['estado_consulta_venta'] ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($consulta['fecha_consulta_propiedad'])->format('d/m/Y') ?? '' }}</td>

                        </tr>
                        @empty

                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

<!-- 

    <div class="tab-pane fade" id="alquiler" role="tabpanel" aria-labelledby="alquiler-tab">
        <div class="contenedor_tablas">
            <div>
                <table id="tabla_propiedad_alquiler" class="table table-sm mb-0 w-100">
                     <thead class="table-light sticky-top small">
                        <tr class="text-center">
                            <th scope="col">Cod alquiler</th>
                            <th scope="col">Dirección</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha asignación</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center" style="font-size: 80%;">
                        @forelse($cliente['consulta_prop_alquiler'] ?? [] as $consulta)
                        @php
                        $propiedad = $consulta['propiedad'];
                        @endphp
                        <tr>
                            <td>{{ $propiedad['cod_alquiler'] ?? ''}}</td>
                            <td>{{ $propiedad['calle']['name'] ?? '' }} {{ $propiedad['numero_calle'] ?? 'sin número' }}</td>
                            <td>{{ $consulta['estado_consulta_alquiler'] ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($consulta['fecha_consulta_propiedad'])->format('d/m/Y') ?? '' }}</td>

                        </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
</div>
