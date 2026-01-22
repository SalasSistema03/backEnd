<div class="modal fade" id="AlquilerPropiedadCarga" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Alquiler
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="formularioComodidades" id="formulario" value="">
                <div class="row g-3">

                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Codigo</label>
                        <input type="number"
                            class="form-control text-center small @error('cod_alquiler') is-invalid @enderror"
                            value="{{ old('cod_alquiler', request('cod_alquiler')) }}" id=""
                            name="cod_alquiler" min="0">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">F. Central</label>
                        <input type="number" class="form-control text-center @error('FCentral') is-invalid @enderror"
                            value="{{ old('FCentral', request('FCentral')) }}" id="" name="FCentral">
                    </div>

                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">F. Candioti</label>
                        <input type="number" class="form-control text-center @error('FCandioti') is-invalid @enderror"
                            value="{{ old('FCandioti', request('FCandioti')) }}" id="" name="FCandioti">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">F. Tribunales</label>
                        <input type="number" class="form-control text-center @error('FTribunales') is-invalid @enderror"
                            value="{{ old('FTribunales', request('FTribunales')) }}" id="" name="FTribunales">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="text-center form-label" for="">Estado de
                            Alquiler</label>
                        <select class="form-select @error('estado_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="estado_alquiler">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_alquileres as $estado_alquileres)
                                <option value="{{ $estado_alquileres->id }}"
                                    {{ old('estado_alquiler') == $estado_alquileres->id ? 'selected' : '' }}>
                                    {{ $estado_alquileres->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 pt-2">
                        <label class="text-center form-label" id="basic-addon1"></label>
                        <select class="form-select" aria-label="Default select example" name="moneda_alquiler">
                            <option value="1">$</option>
                            <option value="2">u$s</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Precio
                        </label>
                        <input type="number"
                            class="form-control text-center @error('monto_alquiler') is-invalid @enderror"
                            value="{{ old('monto_alquiler', request('monto_alquiler')) }}" id=""
                            name="monto_alquiler" min="0">
                    </div>


                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Autorizacion</label>
                        <select class="form-select @error('autorizacion_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="autorizacion_alquiler">
                            <option value="">-</option>
                            <option value="SI"
                                {{ old('autorizacion_alquiler', request('autorizacion_alquiler')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ old('autorizacion_alquiler', request('autorizacion_alquiler')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1"> Fecha
                            Aut.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_autorizacion_alquiler') is-invalid @enderror"
                            value="{{ old('fecha_autorizacion_alquiler', request('fecha_autorizacion_alquiler')) }}"
                            id="" name ="fecha_autorizacion_alquiler">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Exclusividad</label>
                        <select class="form-select @error('exclusividad_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="exclusividad_alquiler">
                            <option value="">-</option>
                            <option value="SI"
                                {{ old('exclusividad_alquiler', request('exclusividad_alquiler')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ old('exclusividad_alquiler', request('exclusividad_alquiler')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">C.
                            Venta</label>
                        <select class="form-select @error('clausula_de_venta') is-invalid @enderror"
                            aria-label="Default select example" name="clausula_de_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ old('clausula_de_venta', request('clausula_de_venta')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ old('clausula_de_venta', request('clausula_de_venta')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-4 ">
                        <label class="text-center form-label" id="basic-addon1">T.
                            Clausula</label>
                        <input type="text"
                            class="form-control text-center @error('tiempo_clausula') is-invalid @enderror"
                            value="{{ old('tiempo_clausula', request('tiempo_clausula')) }}" id=""
                            name="tiempo_clausula">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Fecha
                            Alta</label>
                        <input type="date"
                            class="form-control text-center @error('alquiler_fecha_alta') is-invalid @enderror"
                            name="alquiler_fecha_alta"
                            value="{{ old('alquiler_fecha_alta', request('alquiler_fecha_alta')) }}">
                        @error('alquiler_fecha_alta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center form-label" id="basic-addon1">Fecha
                            Pub.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_publicacion_ig') is-invalid @enderror"
                            name="fecha_publicacion_ig"
                            value="{{ old('fecha_publicacion_ig', request('fecha_publicacion_ig')) }}">
                    </div>

                    {{-- Boton Condicion --}}
                    <div class="col-md-6 pt-4">
                        <button type="button" class="btn btn-light w-100" data-bs-toggle="modal"
                            data-bs-target="#condicionAlquilesPropiedad">Condicion de
                            Alquiler</button>
                    </div>

                </div>

                <div class="modal-footer mt-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>
</div>
