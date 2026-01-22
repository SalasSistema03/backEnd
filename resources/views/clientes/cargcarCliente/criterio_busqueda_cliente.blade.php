

<div  id="criterio_busqueda">
    <div class="d-flex justify-content-center contenedor_titulo">
        <div class="d-flex align-items-center justify-content-center itulos_contenedores">
            <span class="fw-semibold">Criterio de busqueda del cliente</span>
        </div>
    </div>
    <div class="form-group">
        <div class="row">

            <div class="col-md-4">
                <label>Tipo de inmueble</label>
                <select class="form-control" name="id_tipo_inmueble" id="id_tipo_inmueble">
                    <option value="" selected disabled>Seleccionar</option>
                    @foreach($tipoInmuebles as $tipoInmueble)
                    <option value="{{ $tipoInmueble->id }}">{{ $tipoInmueble->inmueble }}</option>
                    @endforeach
                </select>
            </div>


            <div class="col-md-4">
                <label>Cant dormitorios</label>
                <input type="number" class="form-control" name="cant_dormitorios" id="cant_dormitorios">
            </div>


        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-md-4">
                <label>Cochera</label>
                <select id="cochera" name="cochera" class="form-control">
                    <option value="-" selected disabled>-</option>
                    <option value="NO" >No</option>
                    <option value="SI">Sí</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Zona</label>
                <select class="form-control custom-select-style" name="zona" id="zona">
                    <option value="" selected disabled>Seleccionar</option>
                    @foreach($zonas as $zona)
                    <option value="{{ $zona->id }}">{{ $zona->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex justify-content-center align-items-end">
                <button id="btn_guardar_cliente" type="button" class="btn btn-outline-secondary btn-sm px-4">Agregar</button>
            </div>
        </div>
    </div>
</div>