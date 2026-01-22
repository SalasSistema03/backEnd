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
                                <th>Observacion</th>
                                <th>Baja</th>
                                <th>Fecha Baja</th>
                                <th>Mas Informacion </th>
                            </tr>
                        <tbody>
                            @foreach ($padrones as $padron)
                                <tr>
                                    <td>{{ $padron->nombre }} {{ $padron->apellido }}</td>
                                    <td>{{ $padron->observaciones_baja ?? 'Sin observaciones' }}</td>
                                    <td>{{ $padron->baja === 'si' ? 'Sí' : 'No' }}</td>
                                    <td>{{ $padron->baja === 'si' ? $padron->fecha_baja : '-' }}</td>
                                    <td>
                                        <a href="{{ route('padron.show', $padron->padron_id) }}"
                                            class="btn btn-primary btn-sm">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                {{-- <button type="button" class="btn btn-primary">Understood</button> --}}
            </div>
        </div>
    </div>
</div>
