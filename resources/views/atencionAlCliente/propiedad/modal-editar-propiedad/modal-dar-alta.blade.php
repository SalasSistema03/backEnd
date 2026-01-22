<div class="modal fade" id="modalDarAlta" tabindex="-1" aria-labelledby="modalDarAltaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalDarAltaLabel">Propietarios Dados de Baja</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <table class="table table-striped table-hover text-center tabla">
                            <thead>
                                <tr>
                                    <th>Nombre Apellido</th>
                                    <th>Observación</th>
                                    <th>Alta</th>
                                    <th>Fecha Alta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($padrones as $propietario)
                                    @if ($propietario->baja === 'si')
                                        <tr id="propietario-alta-{{ $propietario->padron_id }}">
                                            <td>{{ $propietario->nombre }} {{ $propietario->apellido }}</td>
                                            <td>{{ $propietario->observaciones ?? 'Sin observaciones' }}</td>
                                            <td>No</td>
                                            <td>{{ $propietario->fecha_baja ?? '-' }}</td>
                                            <td>
                                                <button class="btn btn-success btn-sm dar-alta"
                                                    data-propiedad-id="{{ $propiedad->id }}"
                                                    data-padron-id="{{ $propietario->padron_id }}">
                                                    Dar de Alta
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#listaPropietario">Cerrar</button>
                </div>
            </div>
        </div>
    </div>