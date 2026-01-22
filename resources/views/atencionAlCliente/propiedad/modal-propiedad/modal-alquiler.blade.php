<div class="modal fade" id="exampleModalA" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Alquiler</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body datosPropiedad">
                <div class="row  g-3">
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Codigo</label>
                        <input type="number" class="form-control text-center"
                            value="{{ $propiedad->cod_alquiler ?? '-' }}" id="calle-input" disabled>
                    </div>
                    @php
                        $central = $empresaPropiedad->firstWhere('empresa_id', 1);
                    @endphp

                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Central</label>
                        <input type="text" class="form-control text-center"
                            value="{{ $central ? $central->folio : '-' }}" disabled>
                    </div>

                    @php    
                        $candioti = $empresaPropiedad->firstWhere('empresa_id', 2);
                    @endphp
                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Candioti</label>
                        <input type="text" class="form-control text-center"
                            value="{{ $candioti ? $candioti->folio : '-' }}" disabled>
                    </div>

                    @php
                        $tribunales = $empresaPropiedad->firstWhere('empresa_id', 3);
                    @endphp
                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Tribunales</label>
                        <input type="text" class="form-control text-center"
                            value="{{ $tribunales ? $tribunales->folio : '-' }}" disabled>
                    </div>
                    <div class="col-md-3 px-1">
                        <label class="text-center" id="basic-addon1">Estado</label>
                        <input type="text" class="form-control text-center"
                            value="{{ $propiedad->estadoAlquiler->name ?? '-' }}" id="calle-input" disabled>
                    </div>
                    <div class="col-md-1 px-1">
                        <label class="text-center" id="basic-addon1"></label>
                        <input type="text" class="form-control text-center"
                            value="@if ($precio && $precio->moneda_alquiler_pesos != null) {{ '$' }}
                                @elseif($precio && $precio->moneda_alquiler_dolar != null){{ 'u$s' }} @endif"
                            id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Precio</label>
                        <input type="number" class="form-control text-center "
                            value="{{ $precio ? $precio->moneda_alquiler_dolar ?? $precio->moneda_alquiler_pesos : '' }}"
                            disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Exclusividad</label>
                        <input type="text" class="form-control text-center"
                            value="@if ($propiedad->exclusividad_alquiler == 'SI') {{ 'SI' }}
                            @elseif($propiedad->exclusividad_alquiler == 'NO'){{ 'NO' }}
                            @else{{ '-' }} @endif"
                            id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Fecha Alta</label>
                        <input type="date" class="form-control text-center"
                            value="{{ $propiedad->alquiler_fecha_alta ?? '-' }}" id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1" id="fechaBaja">
                        <label class="text-center" id="basic-addon1">Fecha Pub.</label>
                        <input type="date" class="form-control text-center"
                            value="{{ $propiedad->fecha_publicacion_ig ?? '-' }}" id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Fecha Baja</label>
                        <input type="date" class="form-control text-center"
                            value="{{ $propiedad->precio->alquiler_fecha_baja ?? '-' }}" id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1">Autorizacion</label>
                        <input type="text" class="form-control text-center"
                            value="{{ $propiedad->autorizacion_alquiler === 'SI' ? 'SI' : ($propiedad->autorizacion_alquiler === 'NO' ? 'NO' : '-') }}"
                            id="calle-input" disabled>
                    </div>
                    <div class="col-md-2 px-1">
                        <label class="text-center" id="basic-addon1"> Fecha Aut.</label>
                        <input type="date" class="form-control text-center"
                            value="{{ $propiedad->fecha_autorizacion_alquiler ?? '-' }}" id="calle-input" disabled>
                    </div>
                </div>
                <br>
                <div class="row g-1 d-flex justify-content-center">
                    <div class="col-md-4">
                        <button type="button" class="btn btnSalasPropiedad w-100" data-bs-toggle="modal"
                            data-bs-target="#novedadesAlquiler">Novedades</button>
                    </div>
                    {{-- Boton Condicion --}}
                    <div class="col-md-4">
                        <button type="button" class="btn btnSalasPropiedad w-100" data-bs-toggle="modal"
                            data-bs-target="#condicionAlquilesPropiedad">Condicion de Alquiler</button>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btnSalasPropiedad w-100"
                                onclick="window.open('{{ route('propiedades.pdf.pdfPlantillaPropiedad', ['id' => $propiedad->id, 'tipoBTN' => 'alquiler']) }}', '_blank'); return false;">
                                Ficha de propiedad en PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
