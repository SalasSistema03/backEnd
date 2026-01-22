<div class="modal fade" id="listaPropietario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Propietarios</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <table class="table table-striped table-hover text-center tabla">
                        <thead>
                            <tr>
                                <th>Nombre Apellido</th>
                                <th>Observación</th>
                                <th>Baja</th>
                                <th>Fecha Baja</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($padrones as $propietario)
                                <tr {{-- id="propietario-{{ $propietario->padron_id }}" --}}>
                                    <td class="text-center pt-2 pb-1">{{ $propietario->nombre }}
                                        {{ $propietario->apellido }}</td>
                                    @if ($propietario->baja === 'si')
                                        <td class="text-center text-center pt-2 pb-1">
                                            {{ $propietario->observaciones_baja }}</td>
                                    @else
                                        <td class="text-center text-center pt-2 pb-1">
                                            <input type="text" name="observaciones"
                                                data-padron-id="{{ $propietario->padron_id }}"
                                                value="{{ $propietario->observaciones_baja }}">
                                            <input type="hidden" name="padron_id"
                                                value="{{ $propietario->padron_id }}">
                                            <input type="hidden" name="propiedad_id" value="{{ $propiedad->id }}">
                                        </td>
                                    @endif
                                    <td class="text-center pt-2 pb-1">
                                        {{ $propietario->baja === 'si' ? 'Sí' : 'No' }}
                                    </td>
                                    <td class="text-center pt-2 pb-1">{{ $propietario->fecha_baja ?? '-' }}</td>
                                    <td class="text-center pt-1 pb-1">
                                        @if ($propietario->baja === 'no')
                                            <button class="btn btn-primary btn-sm dar-baja"
                                                data-propiedad-id="{{ $propiedad->id }}"
                                                data-padron-id="{{ $propietario->padron_id }}">
                                                Dar de Baja
                                            </button>
                                        @else
                                            <span class="text-center pt-3 pb-1">Ya dado de baja</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#modalBuscarPersona">
                    Buscar Persona
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDarAlta">
                    Ver Propietarios Dados de Baja
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
