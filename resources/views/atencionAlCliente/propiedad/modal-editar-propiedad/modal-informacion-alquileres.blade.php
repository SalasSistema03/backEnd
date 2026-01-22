<div class="modal fade" id="exampleModalA" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Alquiler</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body datosPropiedad">
                <div class="row ">
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Codigo</label>
                        <input type="number"
                            class="form-control text-center @error('cod_alquiler') is-invalid @enderror"
                            value="{{ session('cod_alquiler', $propiedad->cod_alquiler) }}" id="cod_alquiler"
                            name="cod_alquiler" min="0">
                    </div>
                     @php
                        $central = $empresaPropiedad->firstWhere('empresa_id', 1);
                    @endphp 

                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Central</label>
                        <input type="text" class="form-control text-center" name="FCentral"
                            value="{{ $central ? $central->folio : '-' }}"  >
                    </div>

                    @php    
                        $candioti = $empresaPropiedad->firstWhere('empresa_id', 2);
                    @endphp
                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Candioti</label>
                        <input type="text" class="form-control text-center" name="FCandioti"
                            value="{{ $candioti ? $candioti->folio : '-' }}" >
                    </div>

                     @php
                        $tribunales = $empresaPropiedad->firstWhere('empresa_id', 3);
                    @endphp 
                    <div class="col-md-2 px-1">
                        <label class="text-center">F.Tribunales</label>
                        <input type="text" class="form-control text-center" name="FTribunales"
                            value="{{ $tribunales ? $tribunales->folio : '-' }}"  >
                    </div>
                    <div class="col-md-4">
                        <label for="">Estado</label>
                        <select class="form-select @error('estado_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="estado_alquiler" id="estado_alquiler">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_alquileres as $estado_alq)
                            <option value="{{ $estado_alq->id }}"
                                {{ session('estado_alquiler', $propiedad->id_estado_alquiler) == $estado_alq->id ? 'selected' : '' }}>
                                {{ $estado_alq->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1"></label>
                        <select class="form-select" aria-label="Default select example" name="moneda_alquiler">
                            <option value="1"
                                {{ session('moneda_alquiler', $precio && $precio->moneda_alquiler_pesos !== null ? 1 : ($precio && $precio->moneda_alquiler_dolar !== null ? 2 : '')) == 1 ? 'selected' : '' }}>
                                $</option>
                            <option value="2"
                                {{ session('moneda_alquiler', $precio && $precio->moneda_alquiler_dolar !== null ? 2 : ($precio && $precio->moneda_alquiler_pesos !== null ? 1 : '')) == 2 ? 'selected' : '' }}>
                                u$s</option>
                        </select>
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Precio </label>
                        <input type="number"
                            class="form-control text-center @error('monto_alquiler') is-invalid @enderror"
                            value="{{ session('monto_alquiler', $precio ? $precio->moneda_alquiler_dolar ?? ($precio->moneda_alquiler_pesos ?? '') : '') }}"
                            id="monto_alquiler" name="monto_alquiler" min="0">
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Exclusividad</label>
                        <select class="form-select @error('exclusividad_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="exclusividad_alquiler">
                            <option value="">-</option>
                            <option
                                value="SI" {{ session('exclusividad_alquiler', $propiedad->exclusividad_alquiler) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option
                                value="NO" {{ session('exclusividad_alquiler', $propiedad->exclusividad_alquiler) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Autorizacion</label>
                        <select class="form-select @error('autorizacion_alquiler') is-invalid @enderror"
                            aria-label="Default select example" name="autorizacion_alquiler"
                            id="autorizacion_alquiler">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('autorizacion_alquiler', $propiedad->autorizacion_alquiler) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ session('autorizacion_alquiler', $propiedad->autorizacion_alquiler) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-center" id="basic-addon1"> Fecha Aut.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_autorizacion_alquiler') is-invalid @enderror"
                            value="{{ session('fecha_autorizacion_alquiler', $propiedad->fecha_autorizacion_alquiler ?? '-') }}"
                            id="" name="fecha_autorizacion_alquiler">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Clausula Venta</label>
                        <select class="form-select @error('clausula_de_venta') is-invalid @enderror"
                            aria-label="Default select example" name="clausula_de_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('clausula_de_venta', $propiedad->clausula_de_venta) === 'SI' ? 'selected' : '' }}>
                                SI
                            </option>
                            <option value="NO"
                                {{ session('clausula_de_venta', $propiedad->clausula_de_venta) === 'NO' ? 'selected' : '' }}>
                                NO
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">T. Clausula</label>
                        <input type="text"
                            class="form-control text-center @error('tiempo_clausula') is-invalid @enderror"
                            value="{{ session('tiempo_clausula', $propiedad->tiempo_clausula) }}"
                            id="tiempo_clausula" name="tiempo_clausula">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Fecha Alta</label>
                        <input type="date"
                            class="form-control text-center @error('alquiler_fecha_alta') is-invalid @enderror "
                            value="{{ session('alquiler_fecha_alta', $propiedad->alquiler_fecha_alta) }}"
                            name="alquiler_fecha_alta" id="alquiler_fecha_alta">
                    </div>
                    <div class="col-md-2 ">
                        <label class="text-center" id="basic-addon1">Fecha Pub.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_publicacion_ig') is-invalid @enderror"
                            value="{{ session('fecha_publicacion_ig', $propiedad->fecha_publicacion_ig) }}"
                            name="fecha_publicacion_ig" id="fecha_publicacion_ig">
                    </div>

                       <div class="col-md-8 d-none" id="descripcion_container_alquiler">
                        <label for="descripcion_alquiler" class="text-center" id="basic-addon1">Descripción</label>
                        <input type="text"
                            class="form-control @error('descripcion_estado_alquiler') is-invalid @enderror"
                            value="{{ $historialEstadosAlquiler->comentario_alquiler ?? '' }}"
                            name="descripcion_estado_alquiler" id="descripcion_alquiler">
                    </div>

                    <!-- Label e inpit de baja temporal- SI ESTADO VENTA TIENE BAJA TEMPORAL SE MUESTRA SINO NO-->
                    <!-- Label e input de baja temporal -->
                    <div class="col-md-2" style="display: none" id="baja_temporal_alquiler">
                        <label class="text-center" id="basic-addon1">Baja Temporal</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_baja_temporal_alquiler') is-invalid @enderror"
                            value="{{ $historialEstadosAlquiler?->reactiva_fecha_alquiler ? \Carbon\Carbon::parse($historialEstadosAlquiler->reactiva_fecha_alquiler)->format('Y-m-d') : '' }}"
                            name="fecha_baja_temporal_alquiler" id="fecha_baja_temporal_alquiler">
                    </div>

                    {{-- Boton Condicion --}}
                    <div class="col-md-6 pt-3">
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#condicionAlquilesPropiedad">Condicion de Alquiler</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const estadoSelect = document.getElementById("estado_alquiler");
    const descripcionContainer = document.getElementById("descripcion_container_alquiler");
    const bajaTemporalContainer = document.getElementById("baja_temporal_alquiler");
    const descripcionInput = document.getElementById("descripcion_alquiler");
    const bajaTemporalInput = document.getElementById("fecha_baja_temporal_alquiler");
    function toggleDescripcion() {
        if (!estadoSelect) return; // seguridad: si no existe el select, no hace nada


        const selectedOption = estadoSelect.options[estadoSelect.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text.trim().toUpperCase() : "";

        const estadosValidos = ["BAJA", "RESET","BAJA TEMPORAL"];

        // Mostrar/ocultar descripción
        if (estadosValidos.includes(selectedText)) {
            if (descripcionContainer) descripcionContainer.classList.remove("d-none");
        } else {
            if (descripcionContainer) descripcionContainer.classList.add("d-none");
            if (descripcionInput) descripcionInput.value = ""; // limpiar input
        }

        // Mostrar/ocultar baja temporal
        if (selectedText === "BAJA TEMPORAL") {
            if (bajaTemporalContainer) bajaTemporalContainer.style.display = "block";
        } else {
            if (bajaTemporalContainer) bajaTemporalContainer.style.display = "none";
            if (bajaTemporalInput) bajaTemporalInput.value = ""; // limpiar input
        }
    }

    // Ejecutar al cargar la página
    toggleDescripcion();

    // Ejecutar cada vez que cambie el select
    if (estadoSelect) {
        
        estadoSelect.addEventListener("change", toggleDescripcion);
        
    }
});
</script>