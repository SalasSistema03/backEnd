@extends('layout.nav')

@section('title', 'Asignar Permisos a Usuario')

@section('content')
    {{--   @dd($broches) --}}
    <div class="px-3">
        <div class="row">
            <div class="col-md-9">
                <form action="{{ route('exp_broche_expensas.filtro') }}" method="GET"
                    class="row gy-1 gx-2 align-items-center mb-2" autocomplete="off">
                    <!-- Mes -->
                    <div class="col-md-1">
                        <input type="number" name="mes" class="form-control form-control-sm" placeholder="Mes"
                            value="{{ request('mes') }}">
                    </div>
                    <!-- Año -->
                    <div class="col-md-2">
                        <input type="number" name="anio" class="form-control form-control-sm" placeholder="Año"
                            value="{{ request('anio') }}">
                    </div>

                    <!-- Folio / Partida / Clave -->
                    <div class="col-md-2">
                        <input type="text" name="busqueda" class="form-control form-control-sm"
                            placeholder="Folio / Tipo" value="{{ request('busqueda') }}">
                    </div>

                    <!-- Botón -->
                    <div class="col-md-3 d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#expensasModal">Abrir carga</button>
                    </div>
                </form>
            </div>

            <div class="col-md-3">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#ModalBroche">Broches</button>
            </div>
        </div>
        <!-- Modal -->
        @include('impuesto.expensas.modales-expensas.modal-cargar-broche')
        <!-- Modal Broche -->
        @include('impuesto.expensas.modales-expensas.modal-exportar-broche')

        <hr>
        <!-- Tabla de broches -->
        <table id="table_broches" class="table table-sm table-striped table-hover text-center">
            <thead>
                <tr>
                    <th class="table_expensas_title">Folio</th>
                    <th class="table_expensas_title">Adm</th>
                    <th class="table_expensas_title">Administrador</th>
                    <th class="table_expensas_title">Edificio</th>
                    <th class="table_expensas_title">Tipo</th>
                    <th class="table_expensas_title">Unidad</th>
                    <th class="table_expensas_title">Direccion</th>
                    <th class="table_expensas_title">Estado</th>
                    <th class="table_expensas_title">Vencimiento</th>
                    <th class="table_expensas_title">Extraordinaria</th>
                    <th class="table_expensas_title">Ordinaria</th>
                    <th class="table_expensas_title">Total</th>
                    <th class="table_expensas_title">Periodo</th>
                    <th class="table_expensas_title">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody_broches" class="table_expensas">
                <tr class="" id="data_broche_tabla">
                    @foreach ($broches as $broche)
                        <td>{{ $broche->folio ?? '' }}</td>
                        <td>{{ $broche->administra ?? '' }}</td>
                        <td>{{ $broche->nombre ?? '' }}</td>
                        <td>{{ $broche->nombre_consorcio ?? '' }}</td>
                        <td>{{ $broche->tipo ?? '' }}</td>
                        <td>{{ $broche->unidad ?? '' }}</td>
                        <td>{{ $broche->direccion ?? '' }} {{ $broche->altura ?? '' }}</td>
                        <td>{{ $broche->estado ?? '' }}</td>
                        <td>{{ $broche->vencimientobroche ?? '' }}</td>
                        <td>{{ $broche->extraordinaria ?? '' }}</td>
                        <td>{{ $broche->ordinaria ?? '' }}</td>
                        <td>{{ $broche->total ?? '' }}</td>
                        <td>{{ $broche->periodo ?? '' }}</td>
                        <td>
                            <button type="button" onclick="eliminarBroche({{ $broche->id_broche }})"
                                class="btn btn-primary btn-sm">Eliminar</button>
                        </td>
                </tr>
                @endforeach

            </tbody>
        </table>

    </div>



    <script>
        // Restaurar búsqueda al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const savedData = sessionStorage.getItem('buscarfolio_data');

            if (savedData) {
                const data = JSON.parse(savedData);

                // Restaurar los valores en los campos
                document.getElementById('folio').value = data.folio;
                document.getElementById('empresa').value = data.empresa;
                document.getElementById('edificio').value = data.edificio;
                document.getElementById('administrador').value = data.administrador;

                // Limpiar el sessionStorage
                sessionStorage.removeItem('buscarfolio_data');

                // Ejecutar la búsqueda automáticamente
                buscarfolio();
            }
        });
    </script>

@endsection

@section('scripts')
    <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
    <script src="{{ asset('js/impuesto/Exp/Broches/calcularTotal.js') }}"></script>


    <!-- Importo script para buscar el folio -->
     <script type="module" src="{{ asset('js/impuesto/Exp/Broches/buscarFolio.js') }}"></script>
    <script type="module" src="{{ asset('js/impuesto/Exp/Broches/guardarDatosExpensas.js') }}"></script>
    <script type="module" src="{{ asset('js/impuesto/Exp/Broches/eliminarBroche.js') }}"></script>
@endsection
