<div class="modal fade" id="VentasPropiedadCarga" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Ventas
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- Modal Ventas --}}
            <div class="modal-body">
                <input type="hidden" name="formularioComodidades" id="formulario" value="">
                <div class="row g-3">
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Codigo</label>
                        <input type="number" class="form-control text-center @error('cod_venta') is-invalid @enderror"
                            value="{{ old('cod_venta', request('cod_venta')) }}" id="" name="cod_venta"
                            id="" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="text-center form-label" for="">Estado de
                            Venta</label>
                        <select class="form-select @error('estado_venta') is-invalid @enderror"
                            aria-label="Default select example" name="estado_venta">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_venta as $estado_venta)
                                <option value="{{ $estado_venta->id }}"
                                    {{ old('estado_venta') == $estado_venta->id ? 'selected' : '' }}>
                                    {{ $estado_venta->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 pt-2">
                        <label class="text-center form-label" id="basic-addon1">
                        </label>
                        <select class="form-select" aria-label="Default select example" name="moneda_venta">
                            <option value="2">u$s</option>
                            <option value="1">$</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Precio</label>
                        <input type="number"
                            class="form-control text-center @error('monto_venta') is-invalid @enderror"
                            value="{{ old('monto_venta', request('monto_venta')) }}" name="monto_venta" id=""
                            min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Tasacion</label>
                        <input type="date" name="fecha_tasacion_venta"
                            class="form-control text-center @error('fecha_tasacion_venta') is-invalid @enderror"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Valor
                            Tasacion</label>
                        <input type="number"
                            class="form-control text-center @error('tasacion_venta') is-invalid @enderror"
                            value="{{ old('tasacion_venta', request('tasacion_venta')) }}" name="tasacion_venta"
                            id="">
                    </div>
                    {{-- <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">
                            Publicado
                            Redes</label>
                        <input type="date" name="publicado"
                            class="form-control text-center @error('fecha_tasacion_venta') is-invalid @enderror"
                            value="{{ old('fecha_tasacion_venta', request('fecha_tasacion_venta')) }}">
                    </div> --}}
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Exclusividad</label>
                        <select class="form-select @error('exclusividad_venta') is-invalid @enderror"
                            aria-label="Default select example" name="exclusividad_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ old('exclusividad_venta', request('exclusividad_venta')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ old('exclusividad_venta', request('exclusividad_venta')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Comparte</label>
                        <select class="form-select @error('comparte_venta') is-invalid @enderror"
                            aria-label="Default select example" name="comparte_venta">
                            <option value="">-</option>
                            <option
                                value="SI"{{ old('comparte_venta', request('comparte_venta')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option
                                value="NO"{{ old('comparte_venta', request('comparte_venta')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Condicionado</label>
                        <select class="form-select @error('condicionado_venta') is-invalid @enderror"
                            aria-label="Default select example" name="condicionado_venta">
                            <option value="">-</option>
                            <option
                                value="SI"{{ old('condicionado_venta', request('condicionado_venta')) == '1' ? 'selected' : '' }}>
                                SI</option>
                            <option
                                value="NO"{{ old('condicionado_venta', request('condicionado_venta')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Fecha
                            Alta</label>
                        <input type="date"
                            class="form-control text-center @error('venta_fecha_alta') is-invalid @enderror"
                            name="venta_fecha_alta"
                            value="{{ old('venta_fecha_alta', request('venta_fecha_alta')) }}">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Fecha
                            Aut.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_autorizacion_venta') is-invalid @enderror"
                            name="fecha_autorizacion_venta"
                            value="{{ old('fecha_autorizacion_venta', request('fecha_autorizacion_venta')) }}">
                        @error('fecha_autorizacion_venta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 ">
                        <label class="text-center form-label" id="basic-addon1">Comentario
                            Autorizacion</label>
                        <textarea name="comentario_autorizacion" id="comentario_autorizacion" cols="30" rows="10"
                            class="form-control"></textarea>
                    </div>

                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Zona Prop</label>
                        <input type="date"
                            class="form-control text-center @error('zona_prop') is-invalid @enderror"
                            name="zona_prop">
                    </div>

                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Flyer IG</label>
                        <input type="date" class="form-control text-center @error('flyer') is-invalid @enderror"
                            name="flyer">
                    </div>


                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Reel IG</label>
                        <input type="date" class="form-control text-center @error('reel') is-invalid @enderror"
                            name="reel">
                    </div>


                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Web</label>
                        <select class="form-select @error('Web') is-invalid @enderror"
                            aria-label="Default select example" name ="web">
                            <option value="">-</option>
                            <option value="SI"{{ old('Web', request('web')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"{{ old('Web', request('web')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Captador Interno</label>
                        <select class="form-select @error('captador_int') is-invalid @enderror"
                            aria-label="Default select example" name ="captador_int">
                            <option value="">-</option>
                            @foreach ($usuariosTotales as $usuarioTot)
                                <option
                                    value="{{ $usuarioTot->id }}"{{ old('captador_int', request('captador_int')) == $usuarioTot->id ? 'selected' : '' }}>
                                    {{ $usuarioTot->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                 
                    <div class="col-md-2">
                        <label class="text-center form-label" id="basic-addon1">Asesor</label>
                        <select class="form-select @error('asesor') is-invalid @enderror"
                            aria-label="Default select example" name ="asesor">
                            <option value="">-</option>
                            @foreach ($usuarioAsesor as $usuarioTot)
                                <option value="{{ $usuarioTot->id_usuario }}"
                                    {{ old('asesor', request('asesor')) == $usuarioTot->id_usuario ? 'selected' : '' }}>
                                    {{ optional($usuarioTot->username->first())->username }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="modal-footer mt-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
