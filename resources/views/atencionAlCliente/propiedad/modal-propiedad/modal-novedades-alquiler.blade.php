<div class="modal fade" id="novedadesAlquiler" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Novedades Alquiler</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('propiedad.update', $propiedad->id) }}">
                        @csrf
                        @method('PUT')
                        <!-- Identificador del formulario -->
                        <input type="hidden" name="formulario-alquiler" value="novedadesAlquiler">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input name="fecha_actual" type="date" class="form-control text-center"
                                        value="{{ now()->format('Y-m-d') }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Novedad</label>
                                    <textarea name="novedad" class="form-control text-center" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="inputPassword" class="form-label">Novedades</label>
                                <div class="scrollable-content-alquiler">
                                    <table class="table">
                                        <tbody>
                                            @forelse ($observaciones_propiedades_alquiler as $observacion_alquiler)
                                                <tr>
                                                    <td>{{ $observacion_alquiler->created_at?->format('d/m/Y') }}
                                                        {{-- - Usuario: {{ $observacion_alquiler->lastModifiedBy->name }} --}}<br>
                                                        {{ $observacion_alquiler->notes }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="1" class="text-center">Sin resultados</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>