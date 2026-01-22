<div class="modal fade" id="exampleModalV" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Ventas</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body datosPropiedad">
                    <div class="row g-3">
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Codigo</label>
                            <input type="number" class="form-control text-center"
                                value="{{ $propiedad->cod_venta ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-3 px-1">
                            <label class="text-center" id="basic-addon1">Estado</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->estadoVenta->name ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-1 px-1">
                            <label class="text-center" id="basic-addon1"></label>
                            <input type="text" class="form-control text-center"
                                value="@if ($precio && $precio->moneda_venta_pesos != null) {{ '$' }}
                                        @elseif($precio && $precio->moneda_venta_dolar != null){{ 'u$s' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Precio</label>
                            <input type="number" class="form-control text-center"
                                value="{{ $precio ? $precio->moneda_venta_dolar ?? $precio->moneda_venta_pesos : '' }}"
                                disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Fecha Alta</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->venta_fecha_alta }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Fecha Baja</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $precio ? $precio->venta_fecha_baja : '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1"> Tasacion</label>
                            <input type="date" name="fecha_tasacion_venta" id="fecha_tasacion_venta"
                                class="form-control text-center @error('fecha_tasacion_venta') is-invalid @enderror"
                                value="{{ $tasacion->fecha_tasacion ?? '' }}"disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1"></label>
                            <input type="text" class="form-control text-center"
                                value="@if ($tasacion && $tasacion->tasacion_pesos_venta != null) {{ '$' }}
                                        @elseif($tasacion && $tasacion->tasacion_dolar_venta != null){{ 'u$s' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Valor Tasacion</label>
                            <input type="number"
                                class="form-control text-center @error('tasacion_venta') is-invalid @enderror"
                                value="{{ $tasacion ? $tasacion->tasacion_pesos_venta ?? ($tasacion->tasacion_dolar_venta ?? '') : '' }}"
                                name="tasacion_venta" id="" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Comparte</label>
                            <input type="text" class="form-control text-center"
                                value="@if ($propiedad->comparte_venta == 'SI') {{ 'SI' }}
                                        @elseif($propiedad->comparte_venta == 'NO'){{ 'NO' }}
                                        @else{{ '-' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Exclusividad</label>
                            <input type="text" class="form-control text-center"
                                value="@if ($propiedad->exclusividad_venta == 'SI') {{ 'SI' }}
                                        @elseif($propiedad->exclusividad_venta == 'NO'){{ 'NO' }}
                                        @else{{ '-' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Condicionado</label>
                            <input type="text" class="form-control text-center"
                                value="@if ($propiedad->condicionado_venta == 'SI') {{ 'SI' }}
                                        @elseif($propiedad->condicionado_venta == 'NO'){{ 'NO' }}
                                        @else{{ '-' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Clausula Venta</label>
                            <input date="text" class="form-control text-center"
                                value="{{ $propiedad->clausula_de_venta === 'SI' ? 'SI' : ($propiedad->clausula_de_venta === 'NO' ? 'NO' : '-') }}"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Tiempo Clausula</label>
                            <input type="text" class="form-control text-center text-center"
                                value="{{ $propiedad->tiempo_clausula }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Folio</label>
                            <input type="text" class="form-control text-center text-center"
                                value="{{ $folio }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Inicio Contrato</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $inicio_contrato ?? '0000-00-00' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Venc. Contrato</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $vencimiento_contratos ?? '' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Alquiler</label>
                            <input type="number" class="form-control text-center" value="{{ $alquiler ?? '' }}"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Autorizacion</label>
                            <input type="text" class="form-control text-center"
                                value="@if ($propiedad->autorizacion_venta == 'SI') {{ 'SI' }}
                                        @elseif($propiedad->autorizacion_venta == 'NO'){{ 'NO' }}
                                        @else{{ '-' }} @endif"
                                id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1"> Fecha Autorizacion</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->fecha_autorizacion_venta ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-8 px-1">
                            <label class="text-center" id="basic-addon1">Comentario Autorizacion</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->comentario_autorizacion ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Zona Prop</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->zona_prop ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Flyer IG</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->flyer ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Reel IG</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->reel ?? '-' }}" id="calle-input" disabled>
                        </div>
                      {{--   @dd($propiedad) --}}
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Web</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->web ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Captador Interno</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $propiedad->captador_interno ?? '-' }}" id="calle-input" disabled>
                        </div>
                        <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1">Asesor</label>
                            <input type="text" class="form-control text-center"
                                value="{{ $username_asesor ?? '-' }}" id="calle-input" disabled>
                        </div>
                        {{-- <div class="col-md-2 px-1">
                            <label class="text-center" id="basic-addon1"> Publicado Redes</label>
                            <input type="date" class="form-control text-center"
                                value="{{ $propiedad->publicado ?? '-' }}" id="calle-input" disabled>
                        </div> --}}
                        <div class="col-md-4 pt-3">
                            <button type="button" class="btn btnSalasPropiedad w-100" data-bs-toggle="modal"
                                data-bs-target="#novedadesVenta">Novedades</button>
                        </div>
                        <div class="col-md-4 pt-3">
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btnSalasPropiedad w-100"
                                    onclick="window.open('{{ route('propiedades.pdf.pdfPlantillaPropiedad', ['id' => $propiedad->id, 'tipoBTN' => 'venta']) }}', '_blank'); return false;">
                                    Ficha de propiedad en PDF
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>