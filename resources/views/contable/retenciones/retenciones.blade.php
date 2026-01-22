@extends('layout.nav')

@section('title', 'Retenciones')

@section('content')
    <div class="p-3">
        <div class="row g-3">
            <form action="{{ route('retenciones.guardarRetencion') }}" method="POST" id="form_retenciones"
                class="modal-body row g-3 needs-validation" novalidate autocomplete="off">
                @csrf
                <input type="hidden" name="calcula_base" id="calcula_base">
                <div class="col-md-2">
                    <label for="">CUIT</label>
                    <input type="number" class="form-control" name="cuit_retenciones" id="cuit_retenciones">
                </div>
                <div class="col-md-10">
                    <label for="">Razon Social</label>
                    <input type="text" class="form-control" name="razon_social_retenciones" id="razon_social_retenciones"
                        required readonly disabled placeholder="Razon Social">
                </div>
                <div class="col-md-2">
                    <label for="">Fecha de Quincena</label>
                    <input type="date" class="form-control" name="fecha_retenciones" id="fecha_retenciones">
                </div>
                <div class="col-md-8">
                    <label for="">Importe / Suma</label>
                    <input type="sum" class="form-control" name="suma_retenciones" id="suma_retenciones">
                </div>
                <div class="col-md-2">
                    <label for="">Nº Comprobante</label>
                    <input type="number" class="form-control" name="numero_comprobante_retenciones"
                        id="numero_comprobante_retenciones">
                </div>

                <div class="col-md-2 d-flex justify-content-center align-items-end">
                    <button type="button" class="btn btnSalasAzul w-100" id="boton_carga_personas" data-bs-toggle="modal"
                        data-bs-target="#carga_persona_modal"> Carga Persona</button>
                </div>
                <div class="col-md-1 d-flex justify-content-center align-items-end">
                    <button id="boton_base" type="button" class="btn btnSalasAzul w-100" data-bs-toggle="modal"
                        data-bs-target="#modalporcentual">Base y %</button>
                </div>
                <div class="col-md-3">
                    <label for="">Importe</label>
                    <input type="number" class="form-control" name="importe_retenciones" id="importe_retenciones" readonly
                        disabled placeholder="Importe">
                </div>
                <div class="col-md-3">
                    <label for="">Retencion</label>
                    <input type="number" class="form-control" id="importe_retencion" name="importe_retencion" readonly
                        disabled placeholder="Retencion">
                </div>

                <div class="col-md-1 d-flex justify-content-center align-items-end">
                    <button type="submit" class="btn btnSalasAzul w-100">Enviar</button>
                </div>
                <div class="col-md-1 d-flex justify-content-center align-items-end">
                    <div class="dropdown">
                        <button class="btn engrtanaje" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-gear"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#total_q" id="boton_quincena">
                                    Total quincenas
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#exportacion_registros" id="boton_exportacion">
                                    Exportación
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#ret_x_cuit">
                                    Ret. X CUIT
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

            </form>
        </div>
        <div class="row g-3" id="contenedor_tabla_retenciones">
            <table id="tablaDatos" class="table table-striped table-hover text-center tablas">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CUIT</th>
                        <th>Base</th>
                        <th>F. Compr.</th>
                        <th>N° Compr.</th>
                        <th>Importe Compr.</th>
                        <th>Importe Ret.</th>
                        <th>F. Ret</th>
                        <th>- - - -</th>
                    </tr>
                </thead>
                <tbody id="contenedor">

                </tbody>
            </table>
        </div>


        <!-- modal carga de personas -->
        @include('contable.retenciones.modal-retenciones.modal-carga-persona')

        <!-- modal base y porcentual -->
        @include('contable.retenciones.modal-retenciones.modal-porcentual')

        <!-- modal modificacion retencion -->
        @include('contable.retenciones.modal-retenciones.modal-modificacion-retencion')

        <!-- modal total quincena -->
        @include('contable.retenciones.modal-retenciones.modal-total-quincena')

        <!-- modal exportacion datos -->
        @include('contable.retenciones.modal-retenciones.modal-exportar-datos')

        <!-- modal retencion x cuit -->
        @include('contable.retenciones.modal-retenciones.modal-ret_x_cuit')



    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/retenciones/cuit-buscador.js') }}"></script>
    <script src="{{ asset('js/retenciones/suma-retenciones.js') }}"></script>
    <script src="{{ asset('js/retenciones/mostrar-tabla.js') }}"></script>
    <script src="{{ asset('js/retenciones/modificar-retenciones.js') }}"></script>
    <script src="{{ asset('js/retenciones/modal-quincena.js') }}"></script>
    <script src="{{ asset('js/retenciones/retenciones-x-cuit.js') }}"></script>
    <script>
        document.getElementById('boton_exportar_personas').addEventListener('click', function() {
            window.location.href = "{{ route('retenciones.exportarPersonas') }}";
        });
    </script>
    <script>
        // Ocultar el spinner tan pronto como sea posible
        (function() {
            var spinner = document.querySelector('.spinner-wrapper');
            if (spinner) {
                // Forzar la ocultación del spinner
                spinner.style.display = 'none';
                // Prevenir que otros scripts lo vuelvan a mostrar
                var originalDisplay = spinner.style.display;
                Object.defineProperty(spinner.style, 'display', {
                    get: function() {
                        return originalDisplay;
                    },
                    set: function() {
                        return 'none';
                    }
                });
            }

            // Por si acaso, volver a ocultar cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                if (spinner) spinner.style.display = 'none';
            });
        })();
    </script>
    <script>
        document.getElementById("fecha_retenciones").value = new Date().toISOString().slice(0, 10);
    </script>

@endsection
