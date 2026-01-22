<!-- Botón para abrir el modal -->
<button type="button" class="btn btnSalasAzul" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Modificar datos personales
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form action="{{ route('clientes.modificarDatosPersonales', $cliente->id_cliente) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $cliente->nombre ?? '' }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="number" class="form-control" id="telefono" name="telefono" value="{{ $cliente->telefono ?? '' }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ingreso_por" class="form-label">Ingreso por</label>
                            <select class="form-select" id="ingreso_por" name="ingreso_por" required>
                                <option selected>{{ $cliente->ingreso ?? '' }}</option>
                                <option value="Correo">Correo</option>
                                <option value="Difusion">Difusión</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Presencial Candioti">Presencial Candioti</option>
                                <option value="Presencial Tribunales">Presencial Tribunales</option>
                                <option value="Recomendación">Recomendación</option>
                                <option value="Sitio web">Sitio web</option>
                                <option value="Telefonicamente">Telefónicamente</option>
                                <option value="Telefonicamente Candioti">Telefónicamente Candioti</option>
                                <option value="Telefónicamente Tribunales">Telefónicamente Tribunales</option>
                                <option value="Zona Prop">Zona Prop</option>
                                <option value="whatsapp">Whatsapp</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>¿Pertenece a un inmobiliaria?</label>
                            <select id="pertenece_a_inmobiliaria" name="pertenece_a_inmobiliaria" class="form-control">
                                <option value="" {{ is_null($cliente->pertenece_a_inmobiliaria ?? null) ? 'selected' : '' }} disabled>Seleccionar</option>
                                <option value="S" {{ ($cliente->pertenece_a_inmobiliaria ?? '') === 'S' ? 'selected' : '' }}>Sí</option>
                                <option value="N" {{ ($cliente->pertenece_a_inmobiliaria ?? '') === 'N' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3" id="nombre_pertenece_a_inmobiliaria">
                            <label>Nombre de la inmobiliaria</label>
                            <input type="text" class="form-control" name="nombre_de_inmobiliaria" id="nombre_de_inmobiliaria" value="{{ $cliente->nombre_de_inmobiliaria ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="asesor_venta" class="form-label">Asesor de Venta</label>
                            <select class="form-select" id="asesor_venta" name="id_asesor_venta">
                                <!-- Opción vacía si no tiene asesor asignado -->
                                <option value="" {{ empty($cliente?->asesor) ? 'selected' : '' }}>Seleccione un asesor...</option>

                                <!-- Opción actual (si tiene asignado) -->
                                @if(!empty($cliente?->asesor))
                                <option value="{{ $cliente->asesor['id_usuario'] }}" selected>
                                    {{ $cliente->asesor->usuario->username}}
                                </option>
                                @endif

                                <!-- Lista de asesores con venta = 'S', evitando duplicado -->
                                @if($resultadoPermisoBoton)
                                @foreach ($usuarioSectors as $asesor)

                                @continue($asesor['venta'] !== 'S')

                                @continue(!empty($cliente?->asesor) && optional($cliente->asesor)['id_usuario_sector'] == $asesor['id_usuario_sector'])

                                <option value="{{ $asesor['id_usuario']}}">
                                    {{ $asesor->usuario->username }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="1">{{ $cliente->observaciones ?? '' }}</textarea>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>