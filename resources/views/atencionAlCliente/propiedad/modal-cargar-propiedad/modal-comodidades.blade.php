{{-- Modal de Comodidades --}}
<div class="modal fade" id="comodidadesPropiedadCarga" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Comodidades
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="formularioComodidades" id="formulario" value="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="">Estado general</label>
                        <select class="form-select @error('estado_general') is-invalid @enderror"
                            aria-label="Default select example" name="estado_general">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_general as $estado_general)
                                <option value="{{ $estado_general->id }}"
                                    {{ old('estado_general') == $estado_general->id ? 'selected' : '' }}>
                                    {{ $estado_general->estado_general }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">Dorm.</label>
                        <input name="dormitorios" type="number"
                            class="form-control @error('dormitorios') is-invalid @enderror"
                            value="{{ old('dormitorios', request('dormitorios')) }}" id="" min="0"
                            max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">Baños</label>
                        <input name="banios" type="number" class="form-control @error('banios') is-invalid @enderror"
                            value="{{ old('banios', request('banios')) }}" id="" min="0" max="100">
                    </div>

                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">m² Lote</label>
                        <input name="m_Lote"type="number" class="form-control @error('m_Lote') is-invalid @enderror"
                            value="{{ old('m_Lote', request('m_Lote')) }}" id="" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">m² Cub.</label>
                        <input name="m_Cubiertos"type="number"
                            class="form-control @error('m_Cubiertos') is-invalid @enderror"
                            value="{{ old('m_Cubiertos', request('m_Cubiertos')) }}" id="">
                    </div>
                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">Cochera</label>
                        <select class=" form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="cochera">
                            <option value="">-</option>
                            <option value="SI" {{ old('cochera', request('cochera')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO" {{ old('cochera', request('cochera')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">N° Cochera</label>
                        <input name="numero_cochera"type="number"
                            class="form-control  @error('numero_cochera') is-invalid @enderror"
                            value="{{ old('numero_cochera', request('numero_cochera')) }}" id="">
                    </div>

                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">Asfalto</label>
                        <select class="form-select @error('asfalto') is-invalid @enderror"
                            aria-label="Default select example" name="asfalto">
                            <option value="">-</option>
                            <option value="SI" {{ old('asfalto', request('asfalto')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO" {{ old('asfalto', request('asfalto')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">Gas</label>
                        <select class="form-select @error('gas') is-invalid @enderror"
                            aria-label="Default select example" name="gas">
                            <option value="">-</option>
                            <option value="SI" {{ old('gas', request('gas')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO" {{ old('gas', request('gas')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-3 ">
                        <label class="text-center" id="basic-addon1">Cloaca</label>
                        <select class="form-select @error('cloaca') is-invalid @enderror"
                            aria-label="Default select example" name="cloaca">
                            <option value="">-</option>
                            <option value="SI" {{ old('cloaca', request('cloaca')) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO" {{ old('cloaca', request('cloaca')) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">Agua</label>
                        <select class="form-select @error('agua') is-invalid @enderror"
                            aria-label="Default select example" name="agua">
                            <option value="">-</option>
                            <option value="SI" {{ old('agua', request('agua')) == 'SI' ? 'selected' : '' }}>
                                SI
                            </option>
                            <option value="NO" {{ old('agua', request('agua')) == 'NO' ? 'selected' : '' }}>
                                NO
                            </option>
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
