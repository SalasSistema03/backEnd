@extends('layout.nav')

@section('title', 'Propiedad')

@section('content')

    <form class="text-center mx-3" method="GET" action="{{ route('propiedad.edit', $propiedad->id) }}">

        @csrf
        <input type="hidden" name="formulario" value="vistaPropiedad">
        <div class="text-center mt-4 mb-2 ms-2 me-2">
            {{-- datos de la propiedad y fotos --}}
            <div class="row">
                {{-- datos de la propiedad --}}
                <div class="col-md-6">
                    <div class="row g-1 datosPropiedad">
                        <div class="col-md-5 px-1">
                            <label class="text-center" id="basic-addon1">Calle</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->calle->name ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Numero</label>
                            <input type="text " class="form-control text-center"
                                value="{{ $propiedad->numero_calle ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-3 px-1">
                            <label class="text-center" id="basic-addon1">PH</label>
                            <input type="text" class="form-control text-center" value="{{ $propiedad->ph ?? '-' }}"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Piso</label>
                            <input type="text " class="form-control text-center" value="{{ $propiedad->piso ?? '-' }}"
                                id="calle-input" disabled>
                        </div>

                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Depto</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->departamento ?? '- ' }}" id="calle-input" disabled>
                        </div>

                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Inmueble</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->tipoInmueble->inmueble ?? '-' }}" id="calle-input" disabled>
                        </div>


                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Zona</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->zona->name ?? '-' }}" id="calle-input" disabled>
                        </div>

                        <div class="col-md-4 px-1">
                            <label class="text-center" id="basic-addon1">Provincia</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->provincia->name ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-4 px-1">
                            <button type="button" class="btn btnSalas btn-mx mt-3 w-100" data-bs-toggle="popover"
                                data-bs-placement="right" data-bs-custom-class="custom-popover"
                                data-bs-title="Comentario Llave"
                                data-bs-content="{{ $propiedad->comentario_llave ?? '-' }}">
                                <i class="bi bi-info-circle"></i> Llave Nº {{ $propiedad->llave ?? '-' }}
                            </button>
                        </div>
                        <div class="col-md-4 px-1">
                            <button type="button" class="btn btnSalas btn-mx mt-3 w-100" data-bs-toggle="popover"
                                data-bs-placement="right" data-bs-custom-class="custom-popover"
                                data-bs-title="Comentario Cartel"
                                data-bs-content="{{ $propiedad->comentario_cartel ?? '-' }}">
                                <i class="bi bi-info-circle"></i> Cartel - {{ $propiedad->cartel ?? '-' }}

                            </button>
                        </div>

                        <div class="col-md-4 pt-3">
                            @if ($tieneAccesoPropietario)
                                <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                                    data-bs-target="#listaPropietario">
                                    Propietario
                                </button>
                            @else
                                <button type="button" class="btn btn-light w-100" disabled>
                                    Propietario
                                </button>
                            @endif
                        </div>
                        <div class="col-md-4 pt-3">

                            <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                                data-bs-target="#exampleModalD">
                                Descripcion
                            </button>
                        </div>
                        <div class="col-md-4 pt-3">
                            <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                                data-bs-target="#exampleModalS">
                                Comodidades
                            </button>
                        </div>

                        <div class="col-md-2 pt-3 d-flex justify-content-end align-items-center">
                            @if ($tieneAccesoModificar)
                                <button type="submmit" class="btn btnModificar w-20">Modificar</button>
                            @else
                                <button class="btn btnModificar w-20" disabled>Modificar</button>
                            @endif
                        </div>




                        <div class="col-md-12 px-1">
                            <div class="row ">

                                <div class="col-md-3 pt-3">
                                    <label class="text-center" id="basic-addon1">Codigo</label>
                                </div>
                                <div class="col-md-3 pt-3">
                                    <input type="number" class="form-control text-center"
                                        value="{{ $propiedad->cod_venta ?? '-' }}" id="calle-input" disabled>
                                </div>
                                <div class="col-md-6 pt-3 ">
                                    @if ($tieneAccesoInformacionVenta)
                                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                                            data-bs-target="#exampleModalV" name="informacion_venta">
                                            Informacion Venta
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-light w-100" disabled>
                                            Informacion Venta
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 px-1">
                            <div class="row ">
                                <div class="col-md-3 pt-3">
                                    <label class="text-center" id="basic-addon1">Codigo</label>
                                </div>
                                <div class="col-md-3 pt-3">
                                    <input type="number" class="form-control text-center"
                                        value="{{ $propiedad->cod_alquiler ?? '-' }}" id="calle-input" disabled>
                                </div>
                                <div class="col-md-6 pt-3">

                                    @if ($tieneAccesoInformacionAlquiler)
                                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                                            data-bs-target="#exampleModalA" name="informacionAlquiler">
                                            Informacion Alquiler
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-light w-100" disabled>
                                            Informacion Alquiler
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="fotos-tab" data-bs-toggle="tab" href="#fotos"
                                        role="tab" aria-controls="fotos" aria-selected="true">Fotos</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="documentos-tab" data-bs-toggle="tab" href="#documentos"
                                        role="tab" aria-controls="documentos" aria-selected="false">Documentación</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="videos-tab" data-bs-toggle="tab" href="#videos"
                                        role="tab" aria-controls="videos" aria-selected="false">Videos</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0"> <!-- Eliminamos el padding para mejor control -->
                            <div class="tab-content" id="myTabContent">
                                <!-- Sección de Fotos -->
                                <div class="tab-pane fade show active" id="fotos" role="tabpanel"
                                    aria-labelledby="fotos-tab">
                                    <div id="carouselFotos" class="carousel slide" data-bs-ride="carousel"
                                        style="height: 345px;"> <!-- Alto fijo para el carrusel -->
                                        <div class="carousel-inner h-100"> <!-- Ocupa el 100% del alto del carrusel -->
                                            @forelse ($fotos as $index => $foto)
                                                <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                                                    <!-- Cada item ocupa el 100% del alto -->
                                                    <div class="d-flex flex-column h-100">
                                                        <div class="flex-grow-1" style="overflow: hidden;">
                                                            <!-- Contenedor de imagen con crecimiento flexible -->
                                                            <img src="{{ $foto->url }}"
                                                                class="w-100 h-100 object-fit-cover"
                                                                alt="Imagen de propiedad"
                                                                style="object-fit: cover; height: auto; max-height: 400px;"
                                                                style="cursor: pointer;"
                                                                onclick="openModal({{ $loop->index }})">
                                                        </div>

                                                        <div class="p-2 bg-white">
                                                            <!-- Notas siempre en la parte inferior -->
                                                            <input type="text" class="form-control"
                                                                value="{{ $foto->notes }}" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div
                                                    class="text-center h-100 d-flex align-items-center justify-content-center">
                                                    Sin Fotos
                                                </div>
                                            @endforelse
                                        </div>
                                        <button class="carousel-control-prev" type="button"
                                            data-bs-target="#carouselFotos" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                            data-bs-target="#carouselFotos" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Sección de Documentos (con scroll y estructura similar a las fotos) -->
                                <div class="tab-pane fade" id="documentos" role="tabpanel"
                                    aria-labelledby="documentos-tab">
                                    <div class="list-group" style="overflow-y: auto; max-height: 400px;">
                                        @forelse ($documentos as $documento)
                                            <div class="list-group-item d-flex flex-column h-100">

                                                <div class="flex-grow-1" style="overflow: hidden;">
                                                    <!-- Contenedor del PDF -->
                                                    <embed <img
                                                        src="{{ str_replace('/salas/salas/public', '', asset($documento->url)) }}"
                                                        type="application/pdf" class="w-100" height="300px">
                                                    {{--  @dd($documento->url) --}}
                                                </div>
                                                <div class="p-2 bg-white">
                                                    <!-- Input de notas similar a las fotos -->
                                                    <input type="text" class="form-control"
                                                        value="{{ $documento->notes }}" disabled>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="text-center h-100 d-flex align-items-center justify-content-center">
                                                Sin documentos
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                <!-- Sección de Videos (uno debajo del otro con scroll) -->
                                <div class="tab-pane fade" id="videos" role="tabpanel" aria-labelledby="videos-tab">
                                    <div class="list-group" style="overflow-y: auto; max-height: 400px;">
                                        @forelse ($videos as $video)
                                            <div class="list-group-item d-flex flex-column h-100">
                                                <div class="flex-grow-1" style="overflow: hidden;">

                                                    <!-- Contenedor del Video -->
                                                    <video controls class="w-100" height="300px">

                                                        <source {{--     src="{{ str_replace('/salas/salas/public', '', asset($video->url)) }}"  --}}
                                                            src="{{ str_replace('/salas/salas/public', '', asset($video->url)) }}"
                                                            {{-- src="{{ $video->url }}" --}} type="video/mp4">
                                                        Tu navegador no soporta la etiqueta de video.
                                                    </video>
                                                </div>
                                                <div class="p-2 bg-white">
                                                    <!-- Input de notas similar a fotos/documentos -->
                                                    <input type="text" class="form-control"
                                                        value="{{ $video->notes }}" disabled>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="text-center h-100 d-flex align-items-center justify-content-center">
                                                Sin Videos
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="formDescargarFotos" action="{{ route('descargar-fotos', $propiedad->id) }}" method="POST">
        @csrf
        <div class="col-md-2 pt-3 d-flex justify-content-end align-items-center">
            <button type="submit" class="btn btn-light w-100" style="margin-left: 30px;">Descargar Fotos</button>
        </div>
    </form>
    
    <!-- Script para abrir modal en la imagen seleccionada -->
    <script>
        function openModal(index) {
            let modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();

            // Ir directamente a la imagen seleccionada en el modal
            let modalCarousel = document.querySelector('#modalCarousel');
            let carousel = new bootstrap.Carousel(modalCarousel);
            carousel.to(index);
        }
    </script>

    <!-- Modal Comodidades-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-comodidades')
    <!-- Modal Descripcion-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-descripcion')
    <!-- Modal Venta-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-venta')
    <!-- Modal Alquiler-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-alquiler')
    <!-- Modal Documentacion-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-documentacion-propiedad')
    <!-- Modal Novedades Venta-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-novedades-venta')
    <!-- Modal Novedades Alquiler-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-novedades-alquiler')
    <!-- Modal Propietario-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-propietario')
    <!-- Modal Ampliar Imagenes-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-ampliar-imagenes')
    <!-- Modal Condicion-->
    @include('atencionAlCliente.propiedad.modal-propiedad.modal-condicion')



@endsection

@section('scripts')
    <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
@endsection
