<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Ventas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body datosPropiedad">
                <div class="row g-1">
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Codigo</label>
                        <input type="number" class="form-control text-center @error('cod_venta') is-invalid @enderror"
                            value="{{ session('cod_venta', $propiedad->cod_venta) }}" name="cod_venta" id="cod_venta"
                            min="0">
                    </div>
                    <div class="col-md-3">
                        <label for="">Estado</label>
                        <select class="form-select @error('estado_venta') is-invalid @enderror"
                            aria-label="Default select example" name="estado_venta" id="estado_venta">
                            <option value="">Seleccione una estado</option>
                            @foreach ($estado_venta as $estado)
                                <option value="{{ $estado->id }}"
                                    {{ session('estado_venta', $propiedad->id_estado_venta) == $estado->id ? 'selected' : '' }}>
                                    {{ $estado->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1"></label>
                        <select class="form-select" aria-label="Default select example" name="moneda_venta"
                            id="moneda_venta">
                            <option value="2"
                                {{ session('moneda_venta', $precio && $precio->moneda_venta_dolar !== null ? 2 : ($precio && $precio->moneda_venta_pesos !== null ? 1 : '')) == 2 ? 'selected' : '' }}>
                                u$s
                            </option>
                            <option value="1"
                                {{ session('moneda_venta', $precio && $precio->moneda_venta_pesos !== null ? 1 : ($precio && $precio->moneda_venta_dolar !== null ? 2 : '')) == 1 ? 'selected' : '' }}>
                                $
                            </option>
                        </select>

                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Precio</label>
                        <input type="number"
                            class="form-control text-center @error('monto_venta') is-invalid @enderror"
                            value="{{ session('monto_venta', $precio ? $precio->moneda_venta_dolar ?? $precio->moneda_venta_pesos : '') }}"
                            name="monto_venta" id="monto_venta" min="0">
                    </div>
                    {{--  <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Publicado Redes</label>
                        <input type="date" name="publicado" id="publicado"
                            class="form-control text-center @error('publicado') is-invalid @enderror"
                            value="{{ session('publicado', $propiedad->publicado ?? '') }}">
                    </div> --}}

                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1"> Tasacion</label>
                        <input type="date" name="fecha_tasacion_venta" id="fecha_tasacion_venta"
                            class="form-control text-center @error('fecha_tasacion_venta') is-invalid @enderror"
                            value="{{ session('fecha_tasacion_venta', $tasacion?->fecha_tasacion ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1"></label>
                        <select class="form-select" aria-label="Default select example" name="moneda_venta_tasacion"
                            id="moneda_venta_tasacion">
                            <option value="2"
                                {{ session('moneda_venta_tasacion', $tasacion && $tasacion->tasacion_dolar_venta !== null ? 2 : ($tasacion && $tasacion->tasacion_pesos_venta !== null ? 1 : '')) == 2 ? 'selected' : '' }}>
                                u$s
                            </option>
                            <option value="1"
                                {{ session('moneda_venta_tasacion', $tasacion && $tasacion->tasacion_pesos_venta !== null ? 1 : ($tasacion && $tasacion->tasacion_dolar_venta !== null ? 2 : '')) == 1 ? 'selected' : '' }}>
                                $
                            </option>
                        </select>

                    </div>
                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1">Monto</label>
                        <input type="number"
                            class="form-control text-center @error('monto_tasacion') is-invalid @enderror"
                            value="{{ session('monto_tasacion', $tasacion ? $tasacion->tasacion_dolar_venta ?? $tasacion->tasacion_pesos_venta : '') }}"
                            name="monto_tasacion" id="monto_tasacion" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Comparte</label>
                        <select class="form-select @error('comparte_venta') is-invalid @enderror"
                            aria-label="Default select example" name="comparte_venta" id="comparte_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('comparte_venta', $propiedad->comparte_venta) == 'SI' ? 'selected' : '' }}>
                                SI</option>

                            <option value="NO"
                                {{ session('comparte_venta', $propiedad->comparte_venta) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Exclusividad</label>
                        <select class="form-select @error('exclusividad_venta') is-invalid @enderror"
                            aria-label="Default select example" name="exclusividad_venta" id="exclusividad_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('exclusividad_venta', $propiedad->exclusividad_venta) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ session('exclusividad_venta', $propiedad->exclusividad_venta) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Condicionado</label>
                        <select class="form-select @error('condicionado_venta') is-invalid @enderror"
                            aria-label="Default select example" name="condicionado_venta" id="condicionado_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('condicionado_venta', $propiedad->condicionado_venta) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ session('condicionado_venta', $propiedad->condicionado_venta) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Autorizacion</label>
                        <select class="form-select  @error('autorizacion_venta') is-invalid @enderror"
                            aria-label="Default select example" name="autorizacion_venta" id="autorizacion_venta">
                            <option value="">-</option>
                            <option value="SI"
                                {{ session('autorizacion_venta', $propiedad->autorizacion_venta) == 'SI' ? 'selected' : '' }}>
                                SI</option>
                            <option value="NO"
                                {{ session('autorizacion_venta', $propiedad->autorizacion_venta) == 'NO' ? 'selected' : '' }}>
                                NO</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="text-center" id="basic-addon1"> Fecha Aut.</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_autorizacion_venta') is-invalid @enderror "
                            value="{{ session('fecha_autorizacion_venta', $propiedad->fecha_autorizacion_venta) }}"
                            name="fecha_autorizacion_venta" id="fecha_autorizacion_venta">
                    </div>
                    <div class="col-md-5">
                        <label class="text-center" id="basic-addon1">Comentario Autorizacion</label>

                        <textarea name="comentario_autorizacion" id="comentario_autorizacion" cols="30" rows="10"
                            class="form-control">{{ session('comentario_autorizacion', $propiedad->comentario_autorizacion) }}</textarea>
                    </div>
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Fecha Alta</label>
                        <input type="date"
                            class="form-control text-center @error('venta_fecha_alta') is-invalid @enderror "
                            value="{{ session('venta_fecha_alta', $propiedad->venta_fecha_alta) }}"
                            name="venta_fecha_alta" id="venta_fecha_alta">
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Zona Prop</label>
                        <input type="date"
                            class="form-control text-center @error('zona_prop') is-invalid @enderror "
                            value="{{ session('zona_prop', $propiedad->zona_prop) }}" name="zona_prop"
                            id="zona_prop">
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Flyer IG</label>
                        <input type="date" class="form-control text-center @error('Flyer') is-invalid @enderror "
                            value="{{ session('flyer', $propiedad->flyer) }}" name="flyer" id="flyer">
                    </div>
                    {{-- @dd($propiedad->flyer) --}}
                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Reel IG</label>
                        <input type="date" class="form-control text-center @error('reel') is-invalid @enderror "
                            value="{{ session('reel', $propiedad->reel) }}" name="reel" id="reel">
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Web</label>
                        <select name="web" id="web"
                            class="form-select @error('web') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            <option value="SI"{{ session('web', $propiedad->web) == 'SI' ? 'selected' : '' }}>SI
                            </option>
                            <option value="NO"{{ session('web', $propiedad->web) == 'NO' ? 'selected' : '' }}>NO
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Captador Interno</label>

                        <select class="form-select @error('captador_int') is-invalid @enderror"
                            aria-label="Default select example" name="captador_int" id="captador_int">
                            <option value="">Seleccione</option>
                            @foreach ($usuariosTotales as $usuario)
                                <option
                                    value="{{ $usuario->id }}"{{ session('captador_int', $propiedad->captador_int) == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->username }}
                                </option>
                            @endforeach

                        </select>

                    </div>
                    {{--  @dd($usuarioAsesor) --}}

                    <div class="col-md-2">
                        <label class="text-center" id="basic-addon1">Asesor</label>

                        <select class="form-select @error('asesor') is-invalid @enderror"
                            aria-label="Default select example" name="asesor" id="asesor">

                            <option value="">Seleccione</option>

                            @foreach ($usuarioAsesor as $usuarioSector)
                                @php
                                    $usuario = $usuarioSector->username->first();
                                @endphp

                                @if ($usuario)
                                    <option value="{{ $usuarioSector->id_usuario }}"
                                        {{ session('asesor', $propiedad->asesor) == $usuarioSector->id_usuario ? 'selected' : '' }}>
                                        {{ $usuario->username }}
                                    </option>
                                @endif
                            @endforeach

                        </select>
                    </div>


                    <div class="col-md-8 d-none" id="descripcion_container_venta">
                        <label for="descripcion" class="text-center" id="basic-addon1">Descripción</label>
                        <input type="text"
                            class="form-control @error('descripcion_estado_venta') is-invalid @enderror"
                            value="{{ $historialEstados->comentario ?? '' }}" name="descripcion_estado_venta"
                            id="descripcion_venta">
                    </div>

                    <!-- Label e inpit de baja temporal- SI ESTADO VENTA TIENE BAJA TEMPORAL SE MUESTRA SINO NO-->
                    <!-- Label e input de baja temporal -->
                    <div class="col-md-2" style="display: none" id="baja_temporal_venta">
                        <label class="text-center" id="basic-addon1">Baja Temporal</label>
                        <input type="date"
                            class="form-control text-center @error('fecha_baja_temporal_venta') is-invalid @enderror"
                            value="{{ $historialEstados?->reactiva_fecha ? \Carbon\Carbon::parse($historialEstados->reactiva_fecha)->format('Y-m-d') : '' }}"
                            name="fecha_baja_temporal_venta" id="fecha_baja_temporal_venta">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        const estadoSelect = document.getElementById("estado_venta");
        const descripcionContainer = document.getElementById("descripcion_container_venta");
        const bajaTemporalContainer = document.getElementById("baja_temporal_venta");
        const descripcionInput = document.getElementById("descripcion_venta");
        const bajaTemporalInput = document.getElementById("fecha_baja_temporal_venta");

        function toggleDescripcion() {
            if (!estadoSelect) return; // seguridad: si no existe el select, no hace nada


            const selectedOption = estadoSelect.options[estadoSelect.selectedIndex];
            const selectedText = selectedOption ? selectedOption.text.trim().toUpperCase() : "";

            const estadosValidos = ["BAJA", "RESET", "RETIRADA", "FINALIZADA", "BAJA TEMPORAL"];

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
