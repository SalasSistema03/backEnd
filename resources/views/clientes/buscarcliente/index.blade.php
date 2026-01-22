@extends('layout.nav')
@section('title', 'Buscar Cliente')
@section('content')

<div class="container-fluid">
    <div class="row" id="contenedor_busqueda">
        <div class="col-6">
            <form>
                <div class="row">
                    <div class="col-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="number" id="telefono" class="form-control" value="{{ $cliente->telefono ?? '' }}">
                    </div>
                    <div class="col-3">
                        <label for="nombre" class="form-label">Nombre cliente</label>
                        @if(isset($cliente))
                        <input type="text" id="nombre" class="form-control" disabled value="{{ $cliente->nombre ?? '' }}">
                        @endif
                    </div>
                    <div class="col-auto">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" class="btn btnSalasAzul rounded-som " id="btnBuscarCliente">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.442 1.398a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z" />
                            </svg>
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-6 d-flex justify-content-between align-items-end gap-3">
            <div>
                <label class="form-label d-block ">Asesor venta:</label>
                @if(isset($cliente))
                <div class="bg-light border border-0  px-2 py-1 text-secondary shadow-sm small">
                    {{ $cliente->asesor->usuario->username ?? '-' }}
                </div>
                @endif
            </div>
<!-- 
            <div>
                <label class="form-label d-block">Asesor alquiler:</label>
                @if(isset($cliente))
                <div class="bg-light border border-0  px-2 py-1 text-secondary shadow-sm small">
                    {{ $cliente->asesor_Alquiler->usuario->username ?? '-' }}
                </div>
                @endif
            </div> -->

           @if(isset($cliente) && $cliente)
    {{-- La variable $cliente existe y no es null --}}

    @if($cliente->pertenece_a_inmobiliaria === 'S' && $cliente->nombre_de_inmobiliaria)
        <div>
            <label class="form-label d-block">Pertenece a inmobiliaria:</label>
            <div class="bg-light border border-0 px-2 py-1 text-secondary shadow-sm small">
                {{ $cliente->nombre_de_inmobiliaria }}
            </div>
        </div>
    @endif

@endif


            <div>
                @if(isset($cliente))
                @include('clientes.buscarcliente.modales.modificar_datos_personales')
                @endif
            </div>
        </div>



    </div>


    @if(isset($cliente))
    <div id="contenedor_listados" class="row">


        <!-- Contenedor de tabla de propiedades asignadas------------------------------------ -->

        <div class="col-md-6 ">
            <div class="contenedor_tabla" id="contendor_tabla_prop_asignadas">
                @include('clientes.buscarcliente.lista_proiedades_asignadas')
            </div>
        </div>









        <!-- Contenedor de tabla criterio de búsqueda-------------------------------------- -->
        <div class="col-md-6">
            <div id="contenedor_tabla_criterio_busqueda" class="contenedor_tabla">
                @include('clientes.buscarcliente.lista_criterio_busqueda')
            </div>
        </div>



    </div>
    @endif

    @if(isset($cliente))
    <div class=" d-flex justify-content-center align-items-center gap-3" style="margin-top: 3%;">
        <button type="button" id="guardar" class="btn btn-success h-50 btnSalas" style="width: 10%;">
            Guardar
        </button>
    </div>
    @endif

</div>

@endsection


@section('scripts')
<script type="module" src="{{ asset('js/cliente/buscar_cliente_telefono/index.js') }}"></script>
<script type="module" src="{{ asset('js/cliente/buscar_cliente_telefono/modal_lista_propiedades.js') }}"></script>
<script type="module" src="{{ asset('js/cliente/buscar_cliente_telefono/modal_criterio_busqueda.js') }}"></script>
<script type="module" src="{{ asset('js/cliente/buscar_cliente_telefono/main.js') }}"></script>

@endsection