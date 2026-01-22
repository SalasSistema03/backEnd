@php
    $resultado = session('resultado');
    $valores = session('valores');
@endphp
<!-- Se incluye el formulario REGISTRO SELLADO-->
@include('contable.sellado.form_datosDeCalculo')
@include('contable.sellado.form_acciones')

<form id="formulario_registroSellado" method="POST" action="{{ route('calculoSellado') }}">
    {{-- Token de seguridad obligatorio en Laravel --}}
    @csrf
    <div id="contenedor_registro_sellado">
        <div class="mx-1 mb-0">
            <!-- Primera fila -->
            <div class="row">

                <div class="col-md-2">
                    <label class="form-label">Folio</label>
                    <input type="number" class="form-control text-center @error('folio') is-invalid @enderror"
                        value="{{ $resultado['folio'] ?? '' }}" name="folio" id="folio" required min="1">

                </div>
                <div class="col-md-8">
                    <label for="nombre_inq_l" class="form-label">Nombre del Inquilino</label>
                    <input type="text" class="form-control" name="nombre" value="{{ $resultado['nombre'] ?? '' }}"
                        required>
                </div>

                <div class="col-md-2">
                    <label for="Meses_l" class="form-label">C/Meses</label>
                    <input type="number" class="form-control" name="cantidad_meses"
                        value="{{ $resultado['cantidad_meses'] ?? '' }}" required min="1">
                </div>

                <!-- Segunda fila -->

                <div class="col-md-4">
                    <label for="monto_alquiler" class="form-label">Monto del Alquiler</label>
                    <input type="number" step="0.01" class="form-control" name="monto_alquiler"
                        value="{{ $resultado['monto_alquiler'] ?? '' }}" required min="1">
                </div>
                <div class="col-md-4">
                    <label for="monto_d_l" class="form-label">Monto del Documento</label>
                    <input type="number" step="0.01" class="form-control" name="monto_documento"
                        value="{{ $resultado['monto_documento'] ?? '' }}">
                </div>

                <div class="col-md-4">
                    <label for="monto_c_l" class="form-label">Monto del Contrato</label>
                    <input type="number" step="0.01"class="form-control" name="monto_contrato"
                        value="{{ $resultado['monto_contrato'] ?? '' }}">

                </div>

                <!-- Tercera fila -->

                <div class="col-md-2">
                    <label for="hojas_l" class="form-label">C/Hojas</label>
                    <input type="number" class="form-control" name="hojas" value="{{ $resultado['hojas'] ?? '' }}"
                        required min="1">
                </div>

                <div class="col-md-1">
                    <label for="informe_l" class="form-label">Informe</label>
                    <select class="form-select" id="informe" name="informe" required>
                        <option value="SI"
                            {{ isset($resultado['informe']) && $resultado['informe'] == 'SI' ? 'selected' : '' }}>
                            SI</option>
                        <option value="NO"
                            {{ isset($resultado['informe']) && $resultado['informe'] == 'NO' ? 'selected' : '' }}>
                            NO</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cantidad_informes_l" class="form-label">C/ Informes</label>
                    <input type="number" class="form-control" name="cantidad_informes"
                        value="{{ $resultado['cantidad_informes'] ?? '' }}" min="0">
                </div>

                <div class="col-md-2">
                    <label for="tipo_c_l" class="form-label">Tipo de Contrato</label>
                    <select class="form-select" name="tipo_contrato" required>
                        <!-- Usamos el valor de tipo_contrato que proviene del array o un valor por defecto si no está presente -->
                        <option disabled selected value="">-</option>
                        <option value="Vivienda"
                            {{ isset($resultado['tipo_contrato']) && $resultado['tipo_contrato'] == 'Vivienda' ? 'selected' : '' }}>
                            Vivienda</option>
                        <option value="Comercio"
                            {{ isset($resultado['tipo_contrato']) && $resultado['tipo_contrato'] == 'Comercio' ? 'selected' : '' }}>
                            Comercio</option>
                        <option value="Vivienda Comercial"
                            {{ isset($resultado['tipo_contrato']) && $resultado['tipo_contrato'] == 'Vivienda Comercial' ? 'selected' : '' }}>
                            Vivienda Comercial</option>
                        <option value="Cochera"
                            {{ isset($resultado['tipo_contrato']) && $resultado['tipo_contrato'] == 'Cochera' ? 'selected' : '' }}>
                            Cochera</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="inq_prop_l" class="form-label">Contrato Inq - Prop</label>

                    <select class="form-select" id="inq_prop" name="inq_prop" required>
                        <option value="NO"
                            {{ isset($resultado['inq_prop']) && $resultado['inq_prop'] == 'NO' ? 'selected' : '' }}>
                            NO</option>
                        <option value="SI"
                            {{ isset($resultado['inq_prop']) && $resultado['inq_prop'] == 'SI' ? 'selected' : '' }}>
                            SI</option>
                    </select>


                </div>

                <div class="col-md-3">
                    <label for="fecha_i_l" class="form-label">Fecha Inicio Contrato</label>
                    <input type="date" class="form-control" name="fecha_inicio"
                        value="{{ $resultado['fecha_inicio'] ?? '' }}" required>
                </div>
            </div>

            <!-- Botones -->
            <div class="row">
                <div class="col-md-4 d-flex justify-content-end mt-4">
                    <a href="{{ url()->current() }}" class="btn btn-primary w-70">Limpiar</a>
                </div>
                <div class="col-md-4 d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Calcular
                    </button>
                </div>
                <div class="col-md-4 d-flex justify-content-end mt-4" id="contenedor_tabla">
                    <div class="dropdown">
                        <button class="btn engrtanaje" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                            @if ($tieneAccesoDatosDeCalculo)
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalDatosCalculo">Datos de Calculo</a></li>
                            @else
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#">Datos de Calculo</a></li>
                            @endif
                            @if ($tieneAccesoAcciones)
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalAcciones">Acciones</a></li>
                            @else
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#">Acciones</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>



<!-- Tabla donde se mostrarán los datos -->
<form id="formulario_registroSellado" method="POST" action="{{ route('registroSellado.guardar') }}">
    @csrf
    <div class="custom-table" id="contenedor_tabla">
        @if (session('openModal'))
            <table class="table table-bordered text-center" id="tabla_resultadoSellado">
                <thead>
                    <tr>
                        <th colspan="4">Total Contrato</th>
                        <th colspan="4">Prop Alquiler</th>
                        <th colspan="4">Prop Documento</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">{{ $resultado['total_contrato'] ?? 'N/A' }}</td>
                        @if ($resultado['tipo_contrato'] == 'Vivienda')
                            <td colspan="4">{{ $resultado['prop_alquiler'] ?? 'N/A' }}</td>
                        @else
                            @if ($resultado['inq_prop'] == 'NO')
                                <td colspan="4">{{ round($resultado['prop_alquiler'], 2) . ' + iva' ?? 'N/A' }}
                                </td>
                            @else
                                <td colspan="4">{{ round($resultado['prop_alquiler'], 2) ?? 'N/A' }}</td>
                            @endif
                        @endif
                        <td colspan="4">{{ round($resultado['prop_doc'], 2) ?? 'N/A' }}</td>
                    </tr>
                </tbody>

                <thead>
                    <tr>
                        <th colspan="3">Gasto Administrativo</th>
                        <th colspan="3">IVA Gasto Adm</th>
                        <th colspan="3">Sellado</th>
                        <th colspan="3">Valor Informe</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">{{ $resultado['gasto_administrativo'] ?? 'N/A' }}</td>
                        <td colspan="3">{{ $resultado['iva_gasto_adm'] ?? 'N/A' }}</td>
                        <td colspan="3">{{ $resultado['sellado'] ?? 'N/A' }}</td>
                        <td colspan="3">{{ $resultado['valor_informe'] ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Botón Guardar -->
            <div class="d-flex justify-content-center mt-2">
                <form>
                    @csrf

                    @if ($tieneAccesoGuardar)
                        <button type="submit" class="btn btn-success btn-sm px-3 py-1 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar
                        </button>
                    @else
                        <button class="btn btn-success btn-sm px-3 py-1 shadow-sm" disabled> Guardar
                        </button>
                    @endif



                </form>
            </div>
        @else
            <!-- Si no hay resultados, mostrar mensaje -->
            <div class="d-flex justify-content-center align-items-center" style="height: 45px">
                <div class="card text-center shadow-sm p-1" style="max-width: 250px; font-size: 0.70rem;">
                    <p class="mb-0">No hay resultados para mostrar.</p>
                </div>
            </div>
        @endif
    </div>
</form>
